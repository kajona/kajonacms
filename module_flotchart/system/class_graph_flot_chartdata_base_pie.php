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

    protected $bShowLabels = true;
    protected $labelStyleSheets = "font-size:11px ;text-align:center; padding:2px; color:white";
    protected $dLabelRadius = 0.8;
    protected $dTilt = 1;
    protected $dLabelBackroundOpacity = 0.8;
    protected $dPieChartRaduis = 1;

    
    
    public function optionsToJSON() {
        //disaply pie chart
        $series = "
        series: {
            pie: {
                show: true,
                tilt:" . $this->dTilt . ",
                radius:" . $this->dPieChartRaduis . ",
                label: {
                    show:" . $this->bShowLabels . ",
                    formatter: function(label, series) {
                        return '<div style=\"" . $this->labelStyleSheets . "\">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                    },
                    radius:" . $this->dLabelRadius . ",
                    background: {opacity: " . $this->dLabelBackroundOpacity . " }
                }
            }
        }";

        $legend ="
            legend: {
                show: " . $this->bShowLegend . ",
                container:$('#legend_".$this->strChartId."')
            }";

        $grid = "
            grid: { 
                hoverable: true, 
                clickable: true,
                backgroundColor:'".$this->strBackgroundColor."'
            }";
        
        $options = "";
        $options.=$series . ",";
        $options.=$legend . ",";
        $options.=$grid;
        
        return $options;
    }

    /**
     * Labels are being placed around the pie chart
     */
    public function showLabels() {
        $this->bShowLabels = "true";
    }

    /**
     * Removes the labels from the chart
     */
    public function disableLabels() {
        $this->bShowLabels = "false";
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
        $this->bShowLabels = "true";
        $this->dLabelRadius = $labelRaduis;
    }

    /**
     * Makes the pie chart look like 3d by setting the tilt to 0.5
     */
    public function show3d() {
        $this->dTilt = 0.5;
    }

    /**
     * Disables 3d looking pie chart
     */
    public function disabel3d() {
        $this->dTilt = 1;
    }

    public function showLabelBackground() {
        $this->dLabelBackroundOpacity = 0.8;
    }

    public function disableLabelBackground() {
        $this->dLabelBackroundOpacity = 0;
    }

    /**
     * The raduis must be between 0 and 1
     * @param type $raduis 
     */
    public function setPieChartRaduis($dRaduis) {
        $this->dPieChartRaduis = $dRaduis;
    }

    public function showChartToolTips($strChartId) {
        $tooltip = "var previousPoint = null; \n
                    $('#" . $strChartId . "').bind('plothover', flotHelper.showPieToolTip);";
        return $tooltip;
    }

}

