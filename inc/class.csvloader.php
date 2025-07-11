<?php
/**
 * Handles loading the .csv file
 *
 * @author Hector Cabrera <me@cabrerahector.com>
 */

class CSVLoader
{
    /**
     * Path to the uploads folder.
     */
    private $uploads_dir;

    /**
     * File handle.
     */
    private $handle;

    /**
     * 
     */
    private $required_columns;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->uploads_dir = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'uploads';
        $this->handle = null;
    }

    /**
     * Opens the .csv file, if available.
     */
    public function open()
    {
        $file_path = $this->uploads_dir . DIRECTORY_SEPARATOR . 'inventory.csv';

        if (
            file_exists($file_path)
            && is_readable($file_path)
        ) {
            $this->handle = fopen($file_path, 'r');
        }
    }

    /**
     * Closes the .csv file.
     */
    public function close()
    {
        if ( is_resource($this->handle) ) {
            fclose($this->handle);
        }
    }

    /**
     * Returns a file handle.
     *
     * @return  resource|null
     */
    public function get_handle()
    {
        if ( ! is_resource($this->handle) ) {
            $this->open();
        }

        return $this->handle;
    }
}
