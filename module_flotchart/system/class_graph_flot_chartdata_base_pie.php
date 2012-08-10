<?php

/* "******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 * -------------------------------------------------------------------------------------------------------*
 * 	$Id$                                             *
 * ****************************************************************************************************** */

/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_system
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_flot_chartdata_base_pie extends class_graph_flot_chartdata_base {

    protected $showLegend = "true";
    protected $showLabels = "true";
    protected $labelStyleSheets = "";
    protected $labelRadius = "1";
    protected $tilt = "1";
    protected $labelBackroundOpacity = "0";
    protected $pieChartRaduis = "1";

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        //pie chart has no x-Axis
    }

    public function setIntXAxisAngle($intXAxisAngle) {
        //pie chart has no y-Axis
    }

    public function setStrXAxisTitle($strTitle) {
        //pie chart has no x-Axis
    }

    public function setStrYAxisTitle($strTitle) {
        //pie chart has no y-Axis
    }

    public function optionsToJSON() {
        //disaply pie chart
        $options = "series: {
                pie: {
                    show: true,
                    tilt:" . $this->tilt . ",
                    radius:" . $this->pieChartRaduis . ",
                    label: {
                        show:" . $this->showLabels . ",
                        formatter: function(label, series) {
                            return '<div style=\"" . $this->labelStyleSheets . "\">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                        },
                        radius:" . $this->labelRadius . ",
                        background: {opacity: " . $this->labelBackroundOpacity . " }
                    }
                }
            }";

        //show legend?
        $options.=",legend: {
            show: " . $this->showLegend . "
        },";

        $hoverable = "grid: { hoverable: true, clickable: true }";
        $options.=$hoverable;
        return $options;
    }

    /**
     * Labels are being placed around the pie chart
     */
    public function showLabels() {
        $this->showLabels = "true";
    }

    /**
     * Removes the labels from the chart
     */
    public function disableLabels() {
        $this->showLabels = "false";
    }

    /**
     *
     * @param type $strStyleSheets - css-Stylesheets as a string
     */
    public function formatLabels($strStyleSheets) {
        $this->labelStyleSheets = $strStyleSheets;
    }

    /**
     * Sets the lables inside the pie chart
     * @param type $labelRaduis - The raduis wehre labels be set. Must be between 0 and 1.
     */
    public function setLablesInsidePieChart($labelRaduis) {
        $this->showLabels = "true";
        $this->labelRadius = $labelRaduis;
    }

    /**
     * Makes the pie chart look like 3d by setting the tilt to 0.5
     */
    public function show3d() {
        $this->tilt = "0.5";
    }

    /**
     * Disables 3d looking pie chart
     */
    public function disabel3d() {
        $this->tilt = "1";
    }

    public function showLabelBackground() {
        $this->labelBackroundOpacity = "0.8";
    }

    public function disableLabelBackground() {
        $this->labelBackroundOpacity = "0";
    }

    /**
     * The raduis must be between 0 and 1
     * @param type $raduis 
     */
    public function setPieChartRaduis($raduis) {
        $this->pieChartRaduis = $raduis;
    }

    public function showChartToolTips($strChartId) {
        $tooltip =
                "<script type='text/javascript'>
                $(\"#" . $strChartId . "\").bind(\"plothover\", pieHover);
                function pieHover(event, pos, obj) {
                    if (!obj)
                        return;

                    percent = parseFloat(obj.series.percent).toFixed(2);
                    $(\"#pieHover\").html('<span style=\"font-weight: bold; color: '+obj.series.color+'\">'+obj.series.label+' ('+percent+'%)</span>');
                }
                </script>";

        return $tooltip;
    }

}

