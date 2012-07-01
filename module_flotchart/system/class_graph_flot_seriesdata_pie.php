<?php

/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph_flot_SeriesData.php 4527 2012-03-07 10:38:46Z sidler $                                             *
********************************************************************************************************/

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_system
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


