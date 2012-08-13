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
        //TODO: why to data holder? could lead to NpE in case no type is set up yet!
        //$this->objChartData->setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels);
    }

    public function setBitRenderLegend($bitRenderLegend) {
        $this->objChartData->setBitRenderLegend($bitRenderLegend);
    }

    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    public function setIntXAxisAngle($intXAxisAngle) {
        $this->objChartData->setIntXAxisAngle($intXAxisAngle);
    }

    public function setStrBackgroundColor($strColor) {
        $this->objChartData->setStrBackgroundColor($strColor);
    }

    public function setStrFont($strFont) {
        $this->objChartData->setStrFont($strFont);
    }

    public function setStrFontColor($strFontColor) {
        $this->objChartData->setStrFontColor($strFontColor);
    }

    public function setStrGraphTitle($strTitle) {
        //TODO: why to data holder? could lead to NpE in case no type is set up yet!
        //$this->objChartData->setStrGraphTitle($strTitle);
    }

    public function setStrXAxisTitle($strTitle) {
        //TODO: why to data holder? could lead to NpE in case no type is set up yet!
        //$this->objChartData->setStrXAxisTitle($strTitle);
    }

    public function setStrYAxisTitle($strTitle) {
        //TODO: why to data holder? could lead to NpE in case no type is set up yet!
        //$this->objChartData->setStrYAxisTitle($strTitle);
    }

    public function showGraph() {
        return $this->objChartData->showGraph(generateSystemid());
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
        

        $strChartId = generateSystemid();
        $strChartCode = $this->objChartData->showGraph($strChartId);
        
        //generate the wrapping js-code and all requirements
        $strReturn = "\t <div id=\"" . $strChartId . "\" style=\"width:".$this->intWidth."px; height:".$this->intHeight."px\"></div>";

        //TODO: eventually create all required css-code based on the current properties. this would make the request to flot.css obsolete

        $strReturn .= "<script type='text/javascript'>

            KAJONA.admin.loader.loadFile([
                '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.js',
                '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.pie.min.js',
                '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.stack.min.js',
                '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.axislabels.js',
                '/core/module_flotchart/admin/scripts/js/flot/flot_helper.js'
            ], function() {
                    console.log('trggering flot for chart ".$strChartId."');
                ".$strChartCode."
            });
        </script>";

        
        $toolTip = $this->objChartData->showChartToolTips($strChartId);

        return $strReturn.$toolTip;
    }

}