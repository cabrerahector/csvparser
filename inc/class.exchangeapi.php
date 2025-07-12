<?php
/**
 * Pulls data from the exchange Rate API
 *
 * @author Hector Cabrera <me@cabrerahector.com>
 */
class ExchangeAPI
{
    /**
     * Path to the uploads folder.
     *
     * @access private
     * @var    string
     */
    private $cache_dir;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->cache_dir = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'cache';

        if ( ! file_exists($this->cache_dir) ) {
            mkdir($this->cache_dir, 0755, true);
        }
    }

    /**
     * Gets latest exchange rate from USD to the specified currency symbol.
     *
     * @param  string      $to  Currency Symbol (eg. CAD)
     * @return float|null  Exchange rate, or null on failure
     */
    public function get_latest_rate(string $to)
    {
        $response = null;

        // API key has not been defined, bail.
        if ( ! defined('OER_APP_ID') ) {
            return $response;
        }

        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . 'response.json';

        // Create .json file if missing
        if ( ! file_exists($cache_file) ) {
            file_put_contents($cache_file, '{}');
        }

        if ( file_exists($cache_file) ) {
            $created_at = filectime($cache_file);
            $json = file_get_contents($cache_file);

            $now = time();
            $file_age = round(($now - $created_at) / 60); // Time in minutes

            if (
                $file_age > 120 // Cache file is too old or ...
                || $json === '{}' // ... it's empty
            ) {
                $api_response = $this->request($to);

                if ( is_array($api_response) && isset($api_response['rates'][$to]) ) {
                    file_put_contents($cache_file, json_encode($api_response));
                    $response = $api_response['rates'][$to];
                } else {
                    unlink($cache_file);
                    file_put_contents($cache_file, '{}');
                }
            }
            else {
                $arr = json_decode($json, true);

                if ( isset($arr['rates'][$to]) ) {
                    $response = $arr['rates'][$to];
                }
            }
        }

        return $response;
    }

    /**
     * Makes a request to the API.
     *
     * @param  string      $to           Currency symbol (eg. CAD)
     * @param  int         $max_retries  Number of times to attempt to get a response from the API
     * @return array|null  Null on failure, array on success
     */
    private function request(string $to, int $max_retries = 3)
    {
        $api_url = 'https://openexchangerates.org/api/latest.json?app_id='. OER_APP_ID . '&symbols=' . $to;

        $attempts = 0;

        while( $attempts < $max_retries ) {
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 20 seconds
            $response = curl_exec($ch);
            $errno = curl_errno($ch);

            // There was an issue while connecting to the API
            if ( $errno ) {
                $attempts++;
                sleep(2); // Wait 2 seconds before retrying...
            }
            // Got a response
            else {
                curl_close($ch);
                return json_decode($response, true);
            }
        }

        // Failed to connect, return null
        curl_close($ch);
        return null;
    }
}
