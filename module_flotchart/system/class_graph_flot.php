<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/


/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_system
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_flot implements interface_graph {

    /**
     * @var class_graph_flot_chartdata_base
     */
    private $objChartData = null;

    private $intWidth = 600;
    private $intHeight = 350;
    
    //line and barchart
    private $strXAxisTitle = "";
    private $strYAxisTitle = "";
    private $intXAxisAngle = 0;
    private $arrXAxisTickLabels = array();
    
    //line char, bar chart, pie chart
    private $bShowLegend = true;
    private $strGraphTitle = "";
    private $strBackgroundColor="#FFFFFF";
    private $strFont = "Verdana, Arial, Helvetica, sans-serifs";
    private $strFontColor ="#000000";
    
    
    /**
    * Constructor
    *
    */
    public function __construct() {
    }
    
    public function getObjChartData() {
        return $this->objChartData;
    }
    
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
            $seriesData = new class_graph_flot_seriesdata();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabel($strLegend);
            $tempArr = $this->objChartData->getArrChartTypes();
            $seriesData->setStrSeriesChartType($tempArr["bars"]);

            $this->objChartData->addSeriesData($seriesData);
        }
    }

    public function addLinePlot($arrValues, $strLegend) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
            $seriesData = new class_graph_flot_seriesdata();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabel($strLegend);
            $tempArr = $this->objChartData->getArrChartTypes();
            $seriesData->setStrSeriesChartType($tempArr["lines"]);

            $this->objChartData->addSeriesData($seriesData);
        }
        
    }

    public function addStackedBarChartSet($arrValues, $strLegend) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
        }
    }

    public function createPieChart($arrValues, $arrLegends) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_pie();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_pie) {
            $seriesData = new class_graph_flot_seriesdata_pie();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabelArray($arrLegends);
            $tempArr = $this->objChartData->getArrChartTypes();
            $seriesData->setStrSeriesChartType($tempArr["pie"]);

            $this->objChartData->addSeriesData($seriesData);
        }
    }

    public function saveGraph($strFilename) {
        $this->objChartData->saveGraph($strFilename);
    }

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $this->arrXAxisTickLabels = $arrXAxisTickLabels;
    }

    public function setBitRenderLegend($bitRenderLegend) {
        $this->bShowLegend = $bitRenderLegend;
    }

    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
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

    public function showGraph() {
        $this->renderGraph();
    }

    /**
     * Common way to get a chart. The engine should save the chart
     * to the filesystem (if required) and returns the chart with a complete
     * code to embed the chart into a html-page.
     * Please be aware that the method may return a large amount of code depending on
     * the type of engine - from a simple img-tag up to a full js-logic.
     *
     * @since 4.0
     * @return mixed
     */
    public function renderGraph() {
        if($this->objChartData == null)
             throw new class_exception("Chart not initialized yet", class_exception::$level_ERROR);
        
        //set attributes
        $this->objChartData->setBitRenderLegend($this->bShowLegend);
        $this->objChartData->setIntXAxisAngle($this->intXAxisAngle);
        $this->objChartData->setStrBackgroundColor($this->strBackgroundColor);
        $this->objChartData->setStrFont($this->strFont);
        $this->objChartData->setStrFontColor($this->strFontColor);
        $this->objChartData->setStrGraphTitle($this->strGraphTitle);
        $this->objChartData->setStrXAxisTitle($this->strXAxisTitle);
        $this->objChartData->setStrYAxisTitle($this->strYAxisTitle);
        $this->objChartData->setArrXAxisTickLabels($this->arrXAxisTickLabels);
        
        //create chart
        $strChartId = generateSystemid();
        $strChartCode = $this->objChartData->showGraph($strChartId);
        
        //generate the wrapping js-code and all requirements
        $strReturn = "<div style=\"text-align:center; width:".$this->intWidth."px; \">".$this->strGraphTitle;
        $strReturn = $strReturn."\t <div id=\"" . $strChartId . "\" style=\"font-size:11px; font-family:".$this->strFont."; width:".$this->intWidth."px; height:".$this->intHeight."px\"></div>";
        $strReturn = $strReturn."</div>";
        //TODO: eventually create all required css-code based on the current properties. this would make the request to flot.css obsolete

        $strReturn .= "<script type='text/javascript'>

            KAJONA.admin.loader.loadFile(['/core/module_flotchart/admin/scripts/js/flot/jquery.flot.js'], function() {
                KAJONA.admin.loader.loadFile([
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.pie.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.stack.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.axislabels.js',
                    '/core/module_flotchart/admin/scripts/js/flot/flot_helper.js'
                ], function() {
                        console.log('triggering flot for chart ".$strChartId."');
                    ".$strChartCode."
                });
            });
        </script>";

        
        $toolTip = $this->objChartData->showChartToolTips($strChartId);

        return $strReturn.$toolTip;
    }

}