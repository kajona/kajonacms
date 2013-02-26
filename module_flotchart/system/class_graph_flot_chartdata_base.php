<?php
/* "******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 * -------------------------------------------------------------------------------------------------------*
 * 	$Id$                                             *
 * ****************************************************************************************************** */

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_flotchart
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
abstract class class_graph_flot_chartdata_base {

    /**
     * @var class_graph_flot_seriesdata[]
     */
    protected $arrFlotSeriesData = array();//contains the series data
    protected $arrChartTypes = array(); //contains the available chart types

    //chart id
    protected $strChartId = null;
    
    //width and heigth
    private $intWidth;
    private $intHeight;
    
    //line and barchart
    protected $strXAxisTitle = "";
    protected $strYAxisTitle = "";
    protected $intXAxisAngle = 0;
    protected $arrXAxisTickLabels = array();
    protected $intNrOfWrittenLabels = null;
    
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
        $this->arrChartTypes[class_graph_flot_seriesdatatypes::BAR] = "bars: {show:true, barWidth: %s, align: '%s', fill:true, lineWidth: 1, order: %d}";
        $this->arrChartTypes[class_graph_flot_seriesdatatypes::STACKEDBAR] = "stack:true, bars: {show:true, barWidth: %s, align: '%s', fill:true, lineWidth: 1}";
        $this->arrChartTypes[class_graph_flot_seriesdatatypes::LINE] = "lines: {show:true}, points:{show:true} ";
        $this->arrChartTypes[class_graph_flot_seriesdatatypes::PIE] = "pie: {show:true}";
    }
    
    public function setChartId($strChartId) {
        $this->strChartId = $strChartId;
    }
    
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
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
        $this->intNrOfWrittenLabels = $intNrOfWrittenLabels;
    }

    /**
     * Creates a JSON string with options for the flot chart
     */
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

    /**
     * Renders the chart.
     */
    public function showGraph($strChartId) {
        //convert it to JSON
        $strData = $this->dataToJSON();
        $strOptions = $this->optionsToJSON();

        return " $(document).ready(function() {
            ".$this->showChartToolTips($strChartId)."\n
            $.plot($(\"#" . $strChartId . "\"), [" . $strData . "], {" . $strOptions . "});
         });";
    }

    public function dataToJSON() {
        //set series data
        $chartTypeArr = $this->arrChartTypes;
        
        //count number of different series data types
        $barsCount = 0;
        $barsStackedCount = 0;
        foreach($this->arrFlotSeriesData as $intKey => $seriesData) {
            $chartType = $seriesData->getStrSeriesChartType();
            switch($chartType) {
            case class_graph_flot_seriesdatatypes::PIE:
                break;
            case class_graph_flot_seriesdatatypes::LINE:
                break;
            case class_graph_flot_seriesdatatypes::BAR:
                $barsCount++;
                break;
            case class_graph_flot_seriesdatatypes::STACKEDBAR:
                $barsStackedCount++;
                break;
            }
        }
               
        //now set the series data strings
        foreach($this->arrFlotSeriesData as $intKey => $seriesData) {
            $chartType = $seriesData->getStrSeriesChartType();
            $seriesDataString = $chartTypeArr[$seriesData->getStrSeriesChartType()];
            $nrOfElements = sizeof($seriesData->getArrayData());
            
            switch($chartType) {
            case class_graph_flot_seriesdatatypes::PIE:
                $seriesData->setStrSeriesData($seriesDataString);
                break;
            case class_graph_flot_seriesdatatypes::LINE:
                $seriesData->setStrSeriesData($seriesDataString);
                break;
            case class_graph_flot_seriesdatatypes::BAR:
                $alignment = $barsCount==1? "center": "left";
                $order = $intKey+1;

                $substract = 20; //Y-Axis
                if($this->strYAxisTitle!="") {
                    $substract  +=15;
                }
                if($this->bShowLegend) {
                    $substract  +=140;
                }

                $calcWidth = $this->intWidth-$substract;
                $calcWidth = $calcWidth>300? 300:$calcWidth;
                $barWidth = $calcWidth / $nrOfElements / $barsCount;
                $barWidth = $barWidth/100;
                $seriesData->setStrSeriesData(sprintf($seriesDataString, $barWidth, $alignment, $order));
                break;
            case class_graph_flot_seriesdatatypes::STACKEDBAR:
                $alignment = "center";

                $substract = 20;//Y-Axis
                if($this->strYAxisTitle!="") {
                    $substract  +=15;
                }
                if($this->bShowLegend) {
                    $substract  +=140;
                }

                $calcWidth = $this->intWidth-$substract;
                $calcWidth = $calcWidth>300? 300:$calcWidth;
                $barWidth = $calcWidth / $nrOfElements;
                $barWidth = $barWidth/100;
                $seriesData->setStrSeriesData(sprintf($seriesDataString, $barWidth, $alignment));
                break;
            }
        }
        
        // now create a JSON string
        $data = "";
        foreach ($this->arrFlotSeriesData as $objValue) {
            $data.= $objValue->toJSON() . ",";
        }
        $data = substr($data, 0, -1);
        return $data;
    }

}