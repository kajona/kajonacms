<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_flotchart
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_flot_seriesdata {
    
    protected $strLabel = "";
    protected $arrayData = array();
    protected $strSeriesData = "";
    protected $strSeriesChartType = "";
    
    /**
    * Constructor
    *
    */
    public function __construct() {
    }
    
    public function getStrLabel() {
        return $this->strLabel;
    }
    
    public function setStrLabel($strLabel) {
        $this->strLabel = $strLabel;
    }

    public function getArrayData() {
        return $this->arrayData;
    }
    
    public function setArrayData($arrayData) {
        $this->arrayData = array_values($arrayData);
    }

    public function getStrSeriesChartType() {
        return $this->strSeriesChartType;
    }
    
    public function setStrSeriesChartType($strSeriesChartType) {
        $this->strSeriesChartType = $strSeriesChartType;
    }
    
    public function setStrSeriesData($strSeriesData) {
        $this->strSeriesData = $strSeriesData;
    }

    public function toJSON() {
        $strComma = ",";
        $str = "{";
            $str .= "label:\"".$this->strLabel."\"".$strComma;
            $str .= "data:".json_encode($this->convertToFlotArrayDataStructure($this->arrayData)).$strComma;
            $str .=  $this->strSeriesData;
        $str .= "}";
        
        return $str;
    }
    

    //converts the php array to an array for flot
    protected function convertToFlotArrayDataStructure($arrayData) {
        //return $arrayData;
        //pie + line
        $arrTempTemp = array();
        $i = 0;
        foreach($arrayData as $intKey => $objValue) {
            $arrTemp = array();
            $arrTemp[0] = $i++;
            $arrTemp[1] = $objValue;
            
            $arrTempTemp[]=$arrTemp;
        }
        
        return $arrTempTemp;
    }
}


