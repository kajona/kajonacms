<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_graph_flot_chartdata_base_impl extends  class_graph_flot_chartdata_base{

    public function optionsToJSON() {
        $fontFamily = $this->strFont==null?"null":"'".$this->strFont."'";
        $fontColor = $this->strFontColor==null?"null":"'".$this->strFontColor."'";
        $backGroundColor = $this->strBackgroundColor==null?"null":"'".$this->strBackgroundColor."'";
        
        $fontObj = "";
        if($fontFamily!="null" || $fontColor!= "null") {
            $fontObj = ",font: {";
            $fontObj .= "family:".$fontFamily.",";
            $fontObj .= "color:".$fontColor;
            $fontObj .= "}";
        }
        
        
        $xaxis = "xaxis: { tickFormatter:function(val, axis) {
                                return flotHelper.getTickFormatter(".$this->intXAxisAngle.", val, axis);
                            }, 
                           axisLabel: '" . $this->strXAxisTitle . "',
                           axisLabelUseHtml: false,
                           axisLabelUseCanvas: true, 
                           axisLabelPadding:15,
                           axisLabelFontFamily:".$fontFamily.",
                           ticks:".$this->ticksToJSON()."  
                           ".$fontObj."
                        }";
        
        $yaxis = "yaxis: {axisLabel: '" . $this->strYAxisTitle . "',
                            axisLabelUseHtml: false,
                            axisLabelUseCanvas: true, 
                            axisLabelPadding:15,
                            axisLabelFontFamily:".$fontFamily."
                            ".$fontObj."
                        }";

        $legend = "legend: {show:".$this->bShowLegend.",
                            container:$('#legend_".$this->strChartId."')
                            }";
        
        $grid = "grid: {borderWidth: 1,
                        hoverable: true, 
                        clickable: true,
                        backgroundColor:".$backGroundColor."   
                    }";
        
        $options = "";
        $options.=$xaxis . ",";
        $options.=$yaxis . ",";
        $options.=$legend . ",";
        $options.=$grid;

        return $options;
    }

    public function ticksToJSON() {
        if(count($this->arrXAxisTickLabels)==0) {
            return "null";
        }

        //return the tick generator function
        return "function(axis) {
                var angle = eval(".$this->intXAxisAngle.");
                var tickArray = eval(".json_encode($this->arrXAxisTickLabels).");
                var noOfWrittenLabels = eval(".$this->intNrOfWrittenLabels.");
                return flotHelper.getTickArray.call(this, angle, axis, tickArray, noOfWrittenLabels);
            }";
    }
    
    public function showChartToolTips($strChartId) {
        $tooltip = "previousSeries = null; \n
                    previousPoint = null; \n
                    $(\"#" . $strChartId . "\").bind(\"plothover\",flotHelper.doToolTip);";

        return $tooltip;
    }

}

