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
class class_graph_flot_seriesdata_pie extends class_graph_flot_seriesdata{

    protected $strLabelArray = array();
    
    /**
    * Constructor
    *
    */
    public function __construct() {
        
    }
    
    public function getStrLabelArray() {
        return $this->strLabelArray;
    }

    public function setStrLabelArray($strLabelArray) {
        $this->strLabelArray = $strLabelArray;
    }

    
    public function toJSON() {
        /*
         * data format example
         * var data = [
                { label: "IE",  data: 19.5, color: "#4572A7"},
                { label: "Safari",  data: 4.5, color: "#80699B"},
                { label: "Firefox",  data: 36.6, color: "#AA4643"},
                { label: "Opera",  data: 2.3, color: "#3D96AE"},
                { label: "Chrome",  data: 36.3, color: "#89A54E"},
                { label: "Other",  data: 0.8, color: "#3D96AE"}
            ];
         * 
         * 
         */
        $strComma=",";
        $dataStr = "";
        foreach($this->strLabelArray as $intKey => $objValue) {
            $str = "{";
            $strLabel = $objValue;
            $data = $this->arrayData[$intKey];
            
            $str .= "label:\"".$strLabel."\"".$strComma;
            $str .= "data:".$data;
            $str .= "}";
            
            $dataStr.=$str.$strComma;
        }
        $dataStr = substr($dataStr, 0, -1);
        
        return $dataStr;
    }
        
    //converts the php array to an array for flot
    protected function convertToFlotArrayDataStructure($arrayData) {
        return $arrayData;
    }
}


