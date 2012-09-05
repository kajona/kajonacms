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
        $xaxis = "xaxis: { tickFormatter:function(val, axis) {return  flotHelper.getTickFormatter(".$this->intXAxisAngle.", val)}, 
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
        if(count($this->arrXAxisTickLabels)==0)
            return "null";
        
        $data = "[";
        foreach ($this->arrXAxisTickLabels as $intKey => $objValue) {
            $data.= "[".$intKey.",'".$this->arrXAxisTickLabels[$intKey]."'],";
        }
        $data = substr($data, 0, -1);
        $data.="]";
        return $data;
    }
    
    public function showChartToolTips($strChartId) {
        $tooltip =
                "<script type='text/javascript'>
                    function showTooltip(x, y, contents, z) {
                        $('<div id=\"tooltip\">' + contents + '</div>').css( {
                            position: 'absolute',
                            display: 'none',
                            top: y + 5,
                            left: x + 5,
                            border: '2px solid '+z,
                            padding: '2px',
                            'background-color': '#fee',
                            opacity: 0.80
                        }).appendTo(\"body\").fadeIn(200);
                    }

                    var previousPoint = null;
                    $(\"#" . $strChartId . "\").bind(\"plothover\", function (event, pos, item) {
                        $(\"#x\").text(pos.x.toFixed(2));
                        $(\"#y\").text(pos.y.toFixed(2));


                        if (item) {
                            if (previousPoint != item.dataIndex) {
                                previousPoint = item.dataIndex;

                                $(\"#tooltip\").remove();
                                var x = item.datapoint[0].toFixed(2),
                                    y = item.datapoint[1].toFixed(2),
                                    z = item.series.color;

                                showTooltip(item.pageX, item.pageY,
                                            '<b>'+item.series.label+'</b><br/>' + x + ' = ' + y, z);
                            }
                        }
                        else {
                            $(\"#tooltip\").remove();
                            previousPoint = null;            
                        }

                    });
                </script>";

        return $tooltip;
    }

}

