<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph_flot.php 4527 2012-03-07 10:38:46Z sidler $                                             *
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
    
   
    private $objChartData = null;
    
    
    /**
    * Constructor
    *
    */
    public function __construct() {
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
        $this->objChartData->setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels);
    }

    public function setBitRenderLegend($bitRenderLegend) {
        $this->objChartData->setBitRenderLegend($bitRenderLegend);
    }

    public function setIntHeight($intHeight) {
        $this->objChartData->setIntHeight($intHeight);
    }

    public function setIntWidth($intWidth) {
        $this->objChartData->setIntWidth($intWidth);
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
        $this->objChartData->setStrGraphTitle($strTitle);
    }

    public function setStrXAxisTitle($strTitle) {
        $this->objChartData->setStrXAxisTitle($strTitle);
    }

    public function setStrYAxisTitle($strTitle) {
        $this->objChartData->setStrYAxisTitle($strTitle);
    }

    public function showGraph() {
        return $this->objChartData->showGraph();
    }
}