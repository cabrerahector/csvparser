<?php
/**
 * Parses the CSV file
 *
 * @author Hector Cabrera <me@cabrerahector.com>
 */

class CSVParser
{
    /**
     * CSVLoader instance.
     *
     * @access private
     * @var    CSVLoader
     */
    private $csv_loader;

    /**
     * Array of required columns and their labels.
     *
     * @access private
     * @var    array
     */
    private $required_columns;

    /**
     * Construct.
     *
     * @param CSVLoader
     */
    public function __construct(CSVLoader $csv_loader)
    {
        $this->csv_loader = $csv_loader;
        $this->required_columns = ['sku' => 'SKU', 'cost' => 'Cost', 'price' => 'Price', 'qty' => 'QTY'];
        $this->column_names = $this->get_column_names();
    }

    /**
     * Checks whether we have a valid .csv file.
     *
     * @return bool
     */
    public function is_valid()
    {
        $required_column_names = array_keys($this->required_columns);
        $matches = array_intersect($required_column_names, $this->column_names);
        return ( count($matches) === count($required_column_names) );
    }

    /**
     * Retrieves column names from .csv file.
     *
     * @return array
     */
    public function get_column_names()
    {
        $fh = $this->csv_loader->get_handle();

        if ( is_resource($fh) ) {
            $column_names = fgetcsv($fh);

            if ( ! empty($column_names[0]) ) {
                $column_names = array_map(function($name) {
                    return trim(strtolower(preg_replace("/[^A-Za-z]/", '', $name)));
                }, $column_names);
            }

            $this->csv_loader->close();

            return $column_names;
        }

        return [];
    }

    /**
     * Parses data from the .csv file so it matches our desired structure.
     *
     * Borrowed code from https://stackoverflow.com/a/68380989/9131961
     *
     * @return array  $output
     */
    public function parse()
    {
        $output = [];

        if ( $this->is_valid() ) {
            $required_column_names = array_keys($this->required_columns);
            $additional_column_names = array_diff($this->column_names, $required_column_names);

            $fh = $this->csv_loader->get_handle();

            /**
             * Create an array with header values in the order that they
             * should appear in the output.
             */
            $colSpec = array_merge($required_column_names, $additional_column_names);

            // Create a map for column name to actual index in the file
            $headerIndexMap = array_flip($colSpec);

            while ($currRow = fgetcsv($fh, 1000, ','))
            {
                // If this is our first row, set up the column mapping
                if ( empty($output) ) {
                    // Loop through the columns...
                    foreach($currRow as $index => $currHeaderLabel)
                    {
                        /*
                        * Trim the header value, in case there it leading/trailing whitespace in the data
                        */
                        $currHeaderLabel = trim($currHeaderLabel);

                        // If this column is in our column spec, set the index in $headerIndexMap
                        if(array_key_exists($currHeaderLabel, $headerIndexMap))
                        {
                            $headerIndexMap[$currHeaderLabel] = $index;
                        }
                    }
                }

                // Buffer for our output row
                $currOutput = [];

                // Loop through the column spec...
                foreach ($colSpec as $currColumn)
                {
                    // Get the actual index of the column from the index map
                    $currIndex = $headerIndexMap[$currColumn];

                    // Let's do a little cleanup
                    $data = $currRow[$currIndex];
                    $data = str_replace('+AC0', '', $data); // @TODO: Not entirely sure where +AC0 is coming from
                    $data = trim(preg_replace("/[^A-Za-z0-9-.]/", '', $data));

                    // Append the data in the appropriate column to the row output buffer
                    $currOutput[] = $data;
                }

                // Append the new reordered row to the output buffer
                $output[] = $currOutput;
            }

            $this->csv_loader->close();

            // Update column names
            if ( $output[0] ) {
                foreach($output[0] as $index => $name) {
                    if ( isset($this->required_columns[$name]) ) {
                        $output[0][$index] = $this->required_columns[$name];
                    } else {
                        $output[0][$index] = trim(preg_replace("/[^A-Za-z]/", '', $name));
                    }
                }
            }
        }

        return $output;
    }
}
