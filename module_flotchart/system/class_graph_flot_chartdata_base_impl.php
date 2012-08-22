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

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
    }

    public function optionsToJSON() {
        $xaxis = "xaxis: { tickFormatter:function(val, axis) {return  flotHelper.getTickFormatter(".$this->intXAxisAngle.", val)}, 
                           axisLabel: '" . $this->strXAxisTitle . "',
                           axisLabelUseCanvas: true, 
                           axisLabelPadding:30
                        }";
        $yaxis = "yaxis: {axisLabel: '" . $this->strYAxisTitle . "',
                            axisLabelUseCanvas: true, 
                            axisLabelPadding:8
                        }";

        $legend = "legend: {show:".$this->bShowLegend."}";
        
        $hoverable = "grid: { hoverable: true, 
                                clickable: true,
                                backgroundColor:'".$this->strBackgroundColor."',
                                color:'".$this->strFontColor."'    
                            }";


        $options = "";
        $options.=$xaxis . ",";
        $options.=$yaxis . ",";
        $options.=$legend . ",";
        $options.=$hoverable;

        return $options;
    }

    public function showChartToolTips($strChartId) {
        $tooltip =
                "<script type='text/javascript'>
                    function showTooltip(x, y, contents) {
                        $('<div id=\"tooltip\">' + contents + '</div>').css( {
                            position: 'absolute',
                            display: 'none',
                            top: y + 5,
                            left: x + 5,
                            border: '1px solid #fdd',
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
                                    y = item.datapoint[1].toFixed(2);

                                showTooltip(item.pageX, item.pageY,
                                            item.series.label + \" of \" + x + \" = \" + y);
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

