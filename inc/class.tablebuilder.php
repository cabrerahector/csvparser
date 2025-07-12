<?php
/**
 * Builds an HTML table.
 *
 * @author Hector Cabrera <me@cabrerahector.com>
 */

class TableBuilder
{
    /**
     * Data array
     *
     * @access private
     * @var    array
     */
    private $data;

    /**
     * Average Price.
     *
     * @access private
     * @var    float
     */
    private $avg_price;

    /**
     * Total items.
     *
     * @access private
     * @var    int
     */
    private $total_qty;

    /**
     * Average Profit Margin.
     *
     * @access private
     * @var    float
     */
    private $avg_profit_margin;

    /**
     * Total Profit.
     *
     * @access private
     * @var    float
     */
    private $total_profit;

    /**
     * Array of data-related column indexes.
     *
     * @access private
     * @var    array
     */
    private $data_cols;

    /**
     * ExchangeAPI instance.
     *
     * @access private
     * @var    ExchangeAPI
     */
    private $exchange_api;

    /**
     * Exchange rate value.
     *
     * @access private
     * @var    float
     */
    private $exchange_rate;

    /**
     * Initializes the class.
     *
     * @param array        $data
     * @param ExchangeAPI  $exchange_api
     */
    public function __construct(array $data, ExchangeAPI $exchange_api)
    {
        $this->data = $data;
        $this->avg_price = 0;
        $this->total_qty = 0;
        $this->avg_profit_margin = 0;
        $this->total_profit = 0;
        $this->data_cols = [
            'cost' => 1,
            'price' => 2,
            'qty' => 3,
            'profit_margin' => 0,
            'profit' => 0
        ];
        $this->exchange_rate = null;
        $this->exchange_api = $exchange_api;
    }

    /**
     * Builds the table.
     *
     * @return string  $table
     */
    public function build()
    {
        $table = '<div class="table-wrapper">';

        if ( $this->data && $this->data[0] ) {
            $table .= '<table>';
            $table .= $this->add_thead();
            $table .= $this->add_tbody();
            $table .= $this->add_tfoot();
            $table .= '</table>';
        }

        $table .= '</div>';

        return $table;
    }

    /**
     * Builds and returns the <thead> section.
     *
     * @return string  $thead
     */
    private function add_thead()
    {
        $thead = '<thead>';

        $column_names = $this->data[0];

        if ( count($column_names) ) {
            $column_names = [...$column_names, 'Profit Margin', 'Total Profit (USD)', 'Total Profit (CAD)'];

            // Save Profit Margin and Profit column indexes for later use
            $this->data_cols['profit_margin'] = count($column_names) - 3;
            $this->data_cols['profit'] = $this->data_cols['profit_margin'] + 1;

            $thead .= '<tr>';

            foreach($column_names as $column_name) {
                $thead .= '<th>' . htmlentities($column_name, ENT_QUOTES) . '</th>';
            }

            $thead .= '</tr>';
        }

        $thead .= '</thead>';

        return $thead;
    }

    /**
     * Builds and returns the <tbody> section.
     *
     * @return string  $tbody
     */
    private function add_tbody()
    {
        $tbody = '<tbody>';

        if ( isset($this->data[1]) ) {
            $this->exchange_rate = $this->exchange_api->get_latest_rate('CAD');

            $data_col_index = array_flip($this->data_cols);

            for($i = 1; $i < count($this->data); $i++) {
                $tbody .= '<tr>';

                $item_cost = $this->data[$i][1];
                $item_price = $this->data[$i][2];
                $item_qty = $this->data[$i][3];

                $item_profit_margin = $item_price - $item_cost;
                $item_total_profit = $item_profit_margin * $item_qty;
                $item_total_profit_cad = ( is_float($this->exchange_rate) ) ? '$' . number_format(( $item_total_profit * $this->exchange_rate ), 2) : 'N/A';

                $this->data[$i] = [...$this->data[$i], $item_profit_margin, $item_total_profit, $item_total_profit_cad];

                for($j = 0; $j < count($this->data[$i]); $j++) {
                    if ( isset($data_col_index[$j]) ) {
                        $value = ( 'qty' !== $data_col_index[$j] ) 
                            ? '$' . number_format($this->data[$i][$j], 2)
                            : number_format($this->data[$i][$j], 0);
                    } else {
                        $value = htmlentities($this->data[$i][$j], ENT_QUOTES);
                    }

                    $tbody .= '<td>' . $value . '</td>';
                }

                $tbody .= '</tr>';
            }

            // Prepare data for <tfoot> section
            $data_arr = array_slice($this->data, 1);

            $price_column = array_column($data_arr, $this->data_cols['price']);
            $qty_column = array_column($data_arr, $this->data_cols['qty']);
            $profit_margin_column = array_column($data_arr, $this->data_cols['profit_margin']);
            $profit_column = array_column($data_arr, $this->data_cols['profit']);

            $this->avg_price = array_sum($price_column) / count($price_column);
            $this->total_qty = array_sum($qty_column);
            $this->total_profit = array_sum($profit_column);
            $this->avg_profit_margin = array_sum($profit_margin_column) / count($profit_margin_column);
        }

        $tbody .= '</tbody>';

        return $tbody;
    }

    /**
     * Builds and returns the <tfoot> section.
     *
     * @return string  $tfoot
     */
    private function add_tfoot()
    {
        $colspan = count($this->data[0]) + 2; // This is so our footer extends to the end of the table

        $avg_price = number_format($this->avg_price, 2);
        $total_qty = number_format($this->total_qty, 0);
        $avg_profit_margin = number_format($this->avg_profit_margin, 2);
        $total_profit = number_format($this->total_profit, 2);
        $total_profit_cad = ( is_float($this->exchange_rate) ) ? '$' . number_format(( $this->total_profit * $this->exchange_rate ), 2) : 'N/A';

        $tfoot = <<<EOD
            <tfoot>
                <tr>
                    <td colspan="$colspan">Average Price</td>
                    <td>$$avg_price</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total QTY</td>
                    <td>$total_qty</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Average Profit Margin</td>
                    <td>$$avg_profit_margin</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total Profit (USD)</td>
                    <td>$$total_profit</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total Profit (CAD)</td>
                    <td>$total_profit_cad</td>
                </tr>
            </tfoot>
        EOD;

        return $tfoot;
    }
}
