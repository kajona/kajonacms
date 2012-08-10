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
        $xaxis = "xaxis: { tickFormatter:function(val, axis) {return  \"<div style=\'-moz-transform: rotate(-20deg)\'>\"+val+\"</div>\"}, axisLabel: '" . $this->strXAxisTitle . "',axisLabelUseCanvas: true, axisLabelPadding:30}";
        $yaxis = "yaxis: {axisLabel: '" . $this->strYAxisTitle . "',axisLabelUseCanvas: true, axisLabelPadding:8}";
        $hoverable = "grid: { hoverable: true, clickable: true }";
        $legend = "legend: {show:".$this->showLegend."}";


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

