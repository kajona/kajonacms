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
    protected $showLegend = "true";

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

    public function setBitRenderLegend($bitRenderLegend) {
        $this->showLegend = $bitRenderLegend;
        
    }

    public function setStrBackgroundColor($strColor) {
        
    }

    public function setStrFont($strFont) {
        
    }

    public function setStrFontColor($strFontColor) {
        
    }

    public function setStrGraphTitle($strTitle) {
        
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

