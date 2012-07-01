<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph_flot_ChartData.php 4527 2012-03-07 10:38:46Z sidler $                                             *
********************************************************************************************************/

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_system
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_flot_chartdata {
    
    
    private $strXAxisTitle = "X-Axis";
    private $strYAxisTitle = "Y-Axis";
    private $intWidth = 600;
    private $intHeight = 350;
    
    private $arrFlotSeriesData = array();
    private $arrChartTypes = array();
    
    /**
    * Constructor
    *
    */
    public function __construct() {
    $this->arrChartTypes["bars"]  ="bars:  {show:true, barWidth:0.5, align: 'center'}";
    $this->arrChartTypes["lines"] ="lines: {show:true}, points:{show:true} ";
    $this->arrChartTypes["pie"]   ="pie:   {show:true}";

    }
    
    public function getArrChartTypes() {
        return $this->arrChartTypes;
    }

    public function setPieChartLegends($pieChartLegends) {
        $this->pieChartLegends = $pieChartLegends;
    }
    
    public function addSeriesData($seriesData) {
        $this->arrFlotSeriesData[] = $seriesData;
    }
    
    public function getStrXAxisTitle() {
        return $this->strXAxisTitle;
    }

    public function setStrXAxisTitle($strXAxisTitle) {
        $this->strXAxisTitle = $strXAxisTitle;
    }

    public function getStrYAxisTitle() {
        return $this->strYAxisTitle;
    }

    public function setStrYAxisTitle($strYAxisTitle) {
        $this->strYAxisTitle = $strYAxisTitle;
    }

    
    public function optionsToJSON() {
        $xaxis = "xaxis: {axisLabel: '".$this->getStrXAxisTitle()."',axisLabelUseCanvas: true}";
        $yaxis = "yaxis: {axisLabel: '".$this->getStrYAxisTitle()."',axisLabelUseCanvas: true}";
        
        $options="";
        $options.=$xaxis.",";
        $options.=$yaxis;
        
        return $options;
    
    }
    
    public function dataToJSON() {
        $data = "";
        foreach($this->arrFlotSeriesData as $intKey => $objValue) {
            $data.= $objValue->toJSON().",";
        }
        return $data;
    }
    
    public function converToFlotArray($arrData) {
        $arrTempTemp = array();
        
        //pie + line
        $i = 0;
        foreach($arrData as $intKey => $objValue) {
            $arrTemp = array();
            $arrTemp[0] = $i++;
            $arrTemp[1] = $objValue;
            
            $arrTempTemp[]=$arrTemp;
        }
        
        //pie
        /*$arrTempTemp = array();
        $i = 0;
        foreach($arrData as $intKey => $objValue) {
            $arrTemp = array();
            $arrTemp["data"] = $objValue;
              
            $arrTempTemp[]=$arrTemp;
        }*/
        
        return $arrTempTemp;
    }
}