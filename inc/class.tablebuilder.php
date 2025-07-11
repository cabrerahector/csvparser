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
     * Initializes the class.
     *
     * @param array  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->avg_price = 0;
        $this->total_qty = 0;
        $this->avg_profit_margin = 0;
        $this->total_profit = 0;
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
            for($i = 1; $i < count($this->data); $i++) {
                $tbody .= '<tr>';

                for($j = 0; $j < count($this->data[$i]); $j++) {
                    $tbody .= '<td>' . htmlentities($this->data[$i][$j], ENT_QUOTES) . '</td>';
                }

                $tbody .= '</tr>';
            }
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
        $colspan = count($this->data[0]) - 1;

        $tfoot = <<<EOD
            <tfoot>
                <tr>
                    <td colspan="$colspan">Average Price</td>
                    <td>$this->avg_price</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total QTY</td>
                    <td>$this->total_qty</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Average Profit Margin</td>
                    <td>$this->avg_profit_margin</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total Profit (USD)</td>
                    <td>$this->total_profit</td>
                </tr>
                <tr>
                    <td colspan="$colspan">Total Profit (CAD)</td>
                    <td>$this->total_profit</td>
                </tr>
            </tfoot>
        EOD;

        return $tfoot;
    }
}
