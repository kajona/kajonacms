<?php

/* "******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 * -------------------------------------------------------------------------------------------------------*
 * 	$Id$                                             *
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

    protected $arrFlotSeriesData = array();
    protected $arrChartTypes = array();

    //line and barchart
    protected $strXAxisTitle = "";
    protected $strYAxisTitle = "";
    protected $intXAxisAngle = 0;
    protected $arrXAxisTickLabels = array();
    
    //line char, bar chart, pie chart
    protected $bShowLegend = "true";
    protected $strGraphTitle = "";
    protected $strBackgroundColor="";
    protected $strFont = "";
    protected $strFontColor ="";
    
    
    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->arrChartTypes["bars"] = "bars: {show:true, barWidth:0.5, align: 'center'}";
        $this->arrChartTypes["lines"] = "lines: {show:true}, points:{show:true} ";
        $this->arrChartTypes["pie"] = "pie: {show:true}";
    }
    
    public function setBitRenderLegend($bitRenderLegend) {
        if($bitRenderLegend) {
            $this->bShowLegend = "true";
        }
        else {
            $this->bShowLegend = "false";
        }
    }

    public function setIntXAxisAngle($intXAxisAngle) {
        $this->intXAxisAngle = $intXAxisAngle;
    }
    
    public function setStrBackgroundColor($strColor) {
        $this->strBackgroundColor = $strColor;
    }

    public function setStrFont($strFont) {
        $this->strFont = $strFont;
    }

    public function setStrFontColor($strFontColor) {
        $this->strFontColor = $strFontColor;
    }

    public function setStrGraphTitle($strTitle) {
        $this->strGraphTitle = $strTitle;
    }

    public function setStrXAxisTitle($strTitle) {
        $this->strXAxisTitle = $strTitle;
    }

    public function setStrYAxisTitle($strTitle) {
        $this->strYAxisTitle = $strTitle;
     }

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $this->arrXAxisTickLabels = $arrXAxisTickLabels;
    }

    public abstract function optionsToJSON();

    public abstract function showChartToolTips($strChartId);

    public function getArrChartTypes() {
        return $this->arrChartTypes;
    }

    public function saveGraph($strFilename) {
        //does nothing;
    }

    public function addSeriesData($seriesData) {
        $this->arrFlotSeriesData[] = $seriesData;
    }


    public function showGraph($strChartId) {
        $strData = $this->dataToJSON();
        $strOptions = $this->optionsToJSON();

        return " $(document).ready(function() {
            $.plot($(\"#" . $strChartId . "\"), [" . $strData . "], {" . $strOptions . "});
         });";
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

