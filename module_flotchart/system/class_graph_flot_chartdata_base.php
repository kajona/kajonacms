<?php

/* "******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 * -------------------------------------------------------------------------------------------------------*
 * 	$Id: class_graph_flot_chartdata_base.php 4527 2012-03-07 10:38:46Z sidler $                                             *
 * ****************************************************************************************************** */

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_system
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
abstract class class_graph_flot_chartdata_base {

    protected $intWidth = 600;
    protected $intHeight = 350;
    protected $arrFlotSeriesData = array();
    protected $arrChartTypes = array();

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->arrChartTypes["bars"] = "bars: {show:true, barWidth:0.5, align: 'center'}";
        $this->arrChartTypes["lines"] = "lines: {show:true}, points:{show:true} ";
        $this->arrChartTypes["pie"] = "pie: {show:true}";
    }

    public abstract function setIntXAxisAngle($intXAxisAngle);

    public abstract function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12);

    public abstract function setStrXAxisTitle($strTitle);

    public abstract function setStrYAxisTitle($strTitle);

    public abstract function optionsToJSON();

    public function getArrChartTypes() {
        return $this->arrChartTypes;
    }

    public function saveGraph($strFilename) {
        //does nothing;
    }

    public function addSeriesData($seriesData) {
        $this->arrFlotSeriesData[] = $seriesData;
    }

    public function setBitRenderLegend($bitRenderLegend) {
        
    }

    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    public function setStrBackgroundColor($strColor) {
        
    }

    public function setStrFont($strFont) {
        
    }

    public function setStrFontColor($strFontColor) {
        
    }

    public function setStrGraphTitle($strTitle) {
        
    }

    public function showGraph() {
        $strData = $this->dataToJSON();
        $strOptions = $this->optionsToJSON();
        $strChartId = generateSystemid();

        //create div - in this div the chart will be generated
        $strDiv = "\t <div id=\"" . $strChartId . "\" class=\"graph\" style=\"width:".$this->intWidth."px; height:".$this->intHeight."px\"></div>";

        //function which will be called afetr the pages loading
        $strCall = "
        <script type=\"text/javascript\">
            $(document).ready(function(){
                $.plot($(\"#" . $strChartId . "\"),[" . $strData . "],{" . $strOptions . "})
            })
        </script>
        ";

        return $strDiv . "\n" . $strCall;
    }

    public function dataToJSON() {
        $data = "";
        foreach ($this->arrFlotSeriesData as $intKey => $objValue) {
            $data.= $objValue->toJSON() . ",";
        }
        $data = substr($data, 0, -1);
        return $data;
    }

}

