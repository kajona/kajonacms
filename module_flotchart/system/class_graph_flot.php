<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph_flot.php 4527 2012-03-07 10:38:46Z sidler $                                             *
********************************************************************************************************/

//require_once(_corepath_."/module_flotchart/system/flot/class_graph_flot_chartdata.php");
//require_once(_corepath_."/module_flotchart/system/flot/class_graph_flot_seriesdata.php");

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
        $this->objChartData = new class_graph_flot_chartdata();

    }
    
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        $seriesData = new class_graph_flot_seriesdata();
        $seriesData->setArrayData($arrValues);
        $seriesData->setStrLabel($strLegend);
        $tempArr = $this->objChartData->getArrChartTypes();
        $seriesData->setStrSeriesChartType($tempArr["bars"]);
        
        $this->objChartData->addSeriesData($seriesData);
    }

    public function addLinePlot($arrValues, $strLegend) {
        $seriesData = new class_graph_flot_seriesdata();
        $seriesData->setArrayData($arrValues);
        $seriesData->setStrLabel($strLegend);
        $tempArr = $this->objChartData->getArrChartTypes();
        $seriesData->setStrSeriesChartType($tempArr["lines"]);
        
        $this->objChartData->addSeriesData($seriesData);
        
    }

    public function addStackedBarChartSet($arrValues, $strLegend) {
        
    }

    public function createPieChart($arrValues, $arrLegends) {
        
    }

    public function saveGraph($strFilename) {
        //does nothing;
        
    }

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        
    }

    public function setBitRenderLegend($bitRenderLegend) {
        
    }

    public function setIntHeight($intHeight) {
        
    }

    public function setIntWidth($intWidth) {
        
    }

    public function setIntXAxisAngle($intXAxisAngle) {
        
    }

    public function setStrBackgroundColor($strColor) {
        
    }

    public function setStrFont($strFont) {
        
    }

    public function setStrFontColor($strFontColor) {
        
    }

    public function setStrGraphTitle($strTitle) {
        
    }

    public function setStrXAxisTitle($strTitle) {
        $this->objChartData->setStrXAxisTitle($strTitle);
        
    }

    public function setStrYAxisTitle($strTitle) {
        $this->objChartData->setStrYAxisTitle($strTitle);
        
    }

    public function showGraph() {
        $strData = $this->objChartData->dataToJSON();
        $strOptions = $this->objChartData->optionsToJSON();
        $strChartId = generateSystemid();
        
        //create div - in this div the chart will be generated
        $strDiv = "\t <div id=\"".$strChartId."\" class=\"graph\"></div>";
     
        //function which will be called afetr the pages loading
        $strCall = "
        <script type=\"text/javascript\">
            $(document).ready(function(){
                $.plot($(\"#".$strChartId."\"),[".$strData."],{".$strOptions."})
            })
        </script>
        ";
        
        return $strDiv."\n".$strCall;
    }
}