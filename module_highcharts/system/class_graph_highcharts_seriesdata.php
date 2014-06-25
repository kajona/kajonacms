<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * This class contains the data for a series and their specific options.
 *
 * @package module_highcharts
 * @since 4.6
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_highcharts_seriesdata {

    private $arrDataArray = null;
    private $intChartType = null;
    private $intSeriesDataOrder = null;



    //contains specific options for this series
    private $arrSeriesOptions = array(
        "type" => null,
        "data" => null,
        "name" => null,
        "dataLabels" => array(
            "enabled" => null
        ),

    );


    public function __construct($strChartType, $intSeriesDataOrder, &$arrGlobalOptions) {
        $this->intSeriesDataOrder = $intSeriesDataOrder;
        $this->intChartType = $strChartType;

        if($strChartType == class_graph_highcharts_charttype::LINE) {
            $this->arrSeriesOptions["type"] = "line";
        }
        else if($strChartType == class_graph_highcharts_charttype::BAR) {
            $this->arrSeriesOptions["type"] = "column";
        }
        else if($strChartType == class_graph_highcharts_charttype::STACKEDBAR) {
            $this->arrSeriesOptions["type"] = "column";
            $this->arrSeriesOptions["stacking"] ="normal";

        }
        else if($strChartType == class_graph_highcharts_charttype::STACKEDBAR_HORIZONTAL) {
            $this->arrSeriesOptions["type"] = "bar";
            $this->arrSeriesOptions["stacking"] ="normal";
        }
        else {
            throw new class_exception("Not a valid chart type", class_exception::$level_ERROR);
        }
    }


    /**
     * @param bool $bitWriteValues
     */
    public function setBitWriteValues($bitWriteValues = false) {
        $this->arrSeriesOptions["dataLabels"]["enabled"] = $bitWriteValues;
    }

    /**
     * @return int
     */
    public function getIntChartType() {
        return $this->intChartType;
    }


    /**
     * @return int
     */
    public function getIntSeriesDataOrder() {
        return $this->intSeriesDataOrder;
    }


    /**
     * @param array $arrDataArray
     */
    public function setArrDataArray($arrDataArray) {
        $this->arrSeriesOptions["data"] = $arrDataArray;
        $this->arrDataArray = $arrDataArray;
    }

    /**
     * @return array
     */
    public function getArrDataArray() {
        return $this->arrDataArray;
    }

    /**
     * @param string $strSeriesLabel
     */
    public function setStrSeriesLabel($strSeriesLabel) {
        $this->arrSeriesOptions["name"] = $strSeriesLabel;
    }

    /**
     * @return string
     */
    public function getStrSeriesLabel() {
        return $this->arrSeriesOptions["name"];
    }

    /**
     * Converts the php array to a JSON string for jqplot
     *
     * @return string
     */
    public function optionsToJSON() {
        return json_encode($this->arrSeriesOptions);
    }

    /**
     * @return array
     */
    public function getArrSeriesOptions() {
        return $this->arrSeriesOptions;
    }

    /**
     * @param array $arrSeriesOptions
     */
    public function setArrSeriesOptions($arrSeriesOptions) {
        $this->arrSeriesOptions = $arrSeriesOptions;
    }
}