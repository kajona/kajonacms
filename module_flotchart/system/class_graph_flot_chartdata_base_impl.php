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
class class_graph_flot_chartdata_base_impl extends  class_graph_flot_chartdata_base{
    
    protected $strXAxisTitle = "X-Axis";
    protected $strYAxisTitle = "Y-Axis";
    
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
    }

    public function setIntXAxisAngle($intXAxisAngle) {
    }
    
    public function setStrXAxisTitle($strTitle) {
        $this->strXAxisTitle = $strTitle;
    }

    public function setStrYAxisTitle($strTitle) {
        $this->strYAxisTitle = $strTitle;
    }

    public function optionsToJSON() {
        $xaxis = "xaxis: {axisLabel: '".$this->strXAxisTitle."',axisLabelUseCanvas: true}";
        $yaxis = "yaxis: {axisLabel: '".$this->strYAxisTitle."',axisLabelUseCanvas: true}";
        
        $options="";
        $options.=$xaxis.",";
        $options.=$yaxis;
        
        return $options;
    
    }
   
}


