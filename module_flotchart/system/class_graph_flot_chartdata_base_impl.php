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

    public function optionsToJSON() {
        $xaxis = "xaxis: { tickFormatter:function(val, axis) {
                                return flotHelper.getTickFormatter(".$this->intXAxisAngle.", val, axis);
                            }, 
                           axisLabel: '" . $this->strXAxisTitle . "',
                           axisLabelUseCanvas: true, 
                           axisLabelPadding:15,
                           axisLabelFontFamily:'".$this->strFont."',
                           color:'".$this->strFontColor."',
                           ticks:".$this->ticksToJSON()."   
                        }";
        
        $yaxis = "yaxis: {axisLabel: '" . $this->strYAxisTitle . "',
                            axisLabelUseCanvas: true, 
                            axisLabelPadding:15,
                            axisLabelFontFamily:'".$this->strFont."',
                            color:'".$this->strFontColor."'
                        }";

        $legend = "legend: {show:".$this->bShowLegend.",
                            container:$('#legend_".$this->strChartId."')
                            }";
        
        $grid = "grid: { hoverable: true, 
                              clickable: true,
                              backgroundColor:'".$this->strBackgroundColor."'   
                            }";


        $options = "";
        $options.=$xaxis . ",";
        $options.=$yaxis . ",";
        $options.=$legend . ",";
        $options.=$grid;

        return $options;
    }

    public function ticksToJSON() {
        $nrLableTicks = count($this->arrXAxisTickLabels);
        if($nrLableTicks==0) {
            return "null";
        }
        
        //calculate no of ticks 
        $noTicks = null;
        if($this->intNrOfWrittenLabels != null) {
            if($this->intNrOfWrittenLabels > 0) {
                $noTicks = ceil($nrLableTicks / $this->intNrOfWrittenLabels);
            }
            else if($this->intNrOfWrittenLabels <= 0) {
                $noTicks = 0; 
            }
        }
        
        $tickArray = "[";
        foreach ($this->arrXAxisTickLabels as $intKey => $objValue) {
            
            //calculate if tick should be included in the chart
            $moduloResult = null;
            if($noTicks != null) {
                if($noTicks > 0) {
                    $moduloResult = $intKey % $noTicks;
                }
                else if($noTicks == 0) {
                    $moduloResult = 0;  
                }
            }
            
            //add tick
            if($moduloResult == null || ($moduloResult != null && $moduloResult == 0)) {
                $tickArray.= "[".$intKey.",'".$this->arrXAxisTickLabels[$intKey]."'],";
            }
        }
        
        
        //cut off last ",".
        if(strlen($tickArray) > 1) {
            $tickArray = substr($tickArray, 0, -1);
        }
        $tickArray.="]";
        
        return $tickArray;
    }
    
    public function showChartToolTips($strChartId) {
        $tooltip = "var previousPoint = null;
                    $(\"#" . $strChartId . "\").bind(\"plothover\",flotHelper.doToolTip);";

        return $tooltip;
    }

}

