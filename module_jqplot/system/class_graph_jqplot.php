<?php
/*"******************************************************************************************************
*   (c) 2013 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph_jqplot.php 3295 2013-10-23 09:29:28Z smeyer $                                      *
********************************************************************************************************/

/**
 * This class could be used to create graphs based on the jqPlot API.
 * jqPlot renders charts on the client side.
 *
 * @package module_jqplot
 * @since 4.3
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_jqplot implements interface_graph {

    private $intWidth = 700;
    private $intHeight = 350;

    private $arrXAxisTickLabels = null;
    private $intNrOfWrittenLabels = null;

    /**
     * contains all series data per added chart
     * @var class_graph_jqplot_seriesdata[]
     */
    private $arrSeriesData = array(); //

    // array which contains all used jqPlot-Options.
    private $arrOptions = array(
        "seriesColors" => array("#8bbc21", "#2f7ed8", "#f28f43", "#1aadce", "#77a1e5", "#0d233a", "#c42525", "#a6c96a", "#910000"),
        "title" => array(
            "text"=> null,
            "rendererOptions" => array(
                "textColor" => null,
                "fontFamily" => null
            )
        ),
        "highlighter" => array(
          "show" =>false,
          "bringSeriesToFront" =>false,
          "showMarker" => false
        ),

        "legend" => array(
            "renderer" => "$.jqplot.EnhancedLegendRenderer",
            "rowSpacing" => "0px",
            "show"=> true,
            "rendererOptions" => array(
                "textColor" => null,
                "fontFamily" => null
            ),
        ),
        "grid" => array(
            "background"=> "transparent",
            "shadow" => false
        ),
        "axesDefaults" => array(
            "tickRenderer" => "$.jqplot.CanvasAxisTickRenderer",
            "labelRenderer" => "$.jqplot.CanvasAxisLabelRenderer",
            "labelOptions" => array(
                "textColor" => null,
                "fontFamily" => "'Open Sans', Helvetica, Arial, sans-serif"
            ),
            "tickOptions" => array(
                "textColor" => null,
                "fontFamily" => "'Open Sans', Helvetica, Arial, sans-serif",
                "fontSize" => null
            )
        ),
        "seriesDefaults" => array(
            "useNegativeColors" => false

        ),
        "axes" => array(
            "xaxis"=> array(
                "renderer" => null,
                "label" => null,
                "ticks" => null,
                "numberTicks" => null,
                "tickOptions" => array(
                    "angle" => null
                )
            ),
            "yaxis"=> array(
                "label" => null,
            )
        ),
        "series" => array()
    );


    function containsChartType($intChartType) {
        foreach($this->arrSeriesData as $objSeriesData) {
            if($objSeriesData->getIntChartType() === $intChartType)
                return true;
        }
        return false;
    }

    /**
     * Used to create a bar-chart.
     * For each set of bar-values you can call this method once.
     * This means, calling this method twice creates a grouped bar chart
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addBarChartSet(array(1,2,4,5) "serie 1");
     *
     * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
     *
     * @throws class_exception
     */
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        if($this->containsChartType(class_graph_jqplot_charttype::PIE)) {
            throw new class_exception("Chart already contains a Pie chart. Combinations of pie charts and bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_jqplot_charttype::STACKEDBAR)) {
            throw new class_exception("Chart already contains a stacked bar chart. Combinations of bar charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_jqplot_seriesdata(class_graph_jqplot_charttype::BAR, count($this->arrSeriesData));
        $objSeriesData->setArrDataArray($arrValues);
        $objSeriesData->setStrSeriesLabel($strLegend);
        $objSeriesData->setBitWriteValues($bitWriteValues);

        $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";

        $this->arrSeriesData[]=$objSeriesData;
    }


    /**
     * Used to create a stacked bar-chart.
     * For each set of bar-values you can call this method once.
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 1");
     *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 2");
     *
     * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     *
     * @throws class_exception
     */
    public function addStackedBarChartSet($arrValues, $strLegend) {
        if($this->containsChartType(class_graph_jqplot_charttype::PIE)) {
            throw new class_exception("Chart already contains a Pie chart. Combinations of pie charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_jqplot_charttype::LINE)) {
            throw new class_exception("Chart already contains a line chart. Combinations of line charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_jqplot_charttype::BAR)) {
            throw new class_exception("Chart already contains a bar chart. Combinations of bar charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_jqplot_seriesdata(class_graph_jqplot_charttype::STACKEDBAR, count($this->arrSeriesData));
        $objSeriesData->setArrDataArray($arrValues);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";
        $this->arrOptions["stackSeries"] = true;
        $this->arrSeriesData[]=$objSeriesData;
    }

    /**
     * Registers a new plot to the current graph. Works in line-plot-mode only.
     * Add a set of linePlot to a graph to get more then one line.
     * If you created a bar-chart before, it it is possible to add line-plots on top of
     * the bars. Nevertheless, the scale is calculated out of the bars, so make
     * sure to remain inside the visible range!
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * @param array $arrValues e.g. array(1,3,4,5,6)
     * @param string $strLegend the name of the single plot
     *
     * @throws class_exception
     */
    public function addLinePlot($arrValues, $strLegend) {
        if($this->containsChartType(class_graph_jqplot_charttype::PIE)) {
            throw new class_exception("Chart already contains a pie chart. Combinations of pie charts and line charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_jqplot_charttype::STACKEDBAR)) {
            throw new class_exception("Chart already contains a stacked bar chart. Combinations of stacked bar charts and line charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_jqplot_seriesdata(class_graph_jqplot_charttype::LINE, count($this->arrSeriesData));
        $objSeriesData->setArrDataArray($arrValues);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrSeriesData[]=$objSeriesData;
    }

    /**
     * Creates a new pie-chart. Pass the values as the first param. If
     * you want to use a legend and / or Colors use the second and third param.
     * Make sure the array have the same number of elements, ohterwise they won't
     * be uses.
     * A sample-code could be:
     *  $objChart = new class_graph();
     *  $objChart->setStrGraphTitle("Test Pie Chart");
     *  $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
     *
     * @param array $arrValues
     * @param array $arrLegends
     *
     * @throws class_exception
     */
    public function createPieChart($arrValues, $arrLegends) {
        if($this->containsChartType(class_graph_jqplot_charttype::LINE)
            || $this->containsChartType(class_graph_jqplot_charttype::BAR)
            || $this->containsChartType(class_graph_jqplot_charttype::STACKEDBAR)
        ) {
            throw new class_exception("Chart already contains either a line, bar or stacked bar chart. Combinations of pie charts with other charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_jqplot_charttype::PIE)) {
            throw new class_exception("Chart already contains either a pie chart.Only one pie chart per chart is allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_jqplot_seriesdata(class_graph_jqplot_charttype::PIE, count($this->arrSeriesData));
        $objSeriesData->setArrDataArray($arrValues);

        $this->arrXAxisTickLabels = $arrLegends;//set to this array, as the data array is built up similar
        $this->arrSeriesData[]=$objSeriesData;
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * sends the graph directly (!!!) to the browser.
     * Execution should be terminated afterwards.
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use interface_graph::renderGraph() instead
     */
    public function showGraph() {
         $this->renderGraph();
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * saves the graph to the specified filename
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use interface_graph::renderGraph() instead
     */
    public function saveGraph($strFilename) {
        //not supported
    }

    /**
     * Common way to get a chart. The engine should save the chart
     * to the filesystem (if required) and returns the chart with a complete
     * code to embed the chart into a html-page.
     * Please be aware that the method may return a large amount of code depending on
     * the type of engine - from a simple img-tag up to a full js-logic.
     *
     * @since 4.0
     * @throws class_exception
     * @return mixed
     */
    public function renderGraph() {
        if(count($this->arrSeriesData) == 0) {
            throw new class_exception("Chart not initialized yet", class_exception::$level_ERROR);
        }

        //create id's
        $strSystemId = generateSystemid();
        $strChartId =  "chart_".$strSystemId;
        $strTooltipId =  "tooltip_".$strSystemId;

        //create div where the chart is being put
        $strReturn = "<div id=\"" . $strChartId . "\" style=\"width:".$this->intWidth."px; height:".$this->intHeight."px;\"></div>";

        //create the data array and options object for the jqPlot method
        $strData = $this->strCreateJSDataArray();
        $strOptions = $this->strCreateJSOptions();

        //create the js-Code
        $strChartCode = "$(function() {";
        //plots the graph
        $strChartCode .= "$.jqplot('".$strChartId."',".$strData.",".$strOptions.");";

        //if this variable is set ticks may be set invisible
        if($this->intNrOfWrittenLabels != null) {
            $strChartCode .= "KAJONA.admin.jqplotHelper.setLabelsInvisible('".$strChartId."',".$this->intNrOfWrittenLabels.");";
        }

        $strChartCode .= "$('#".$strChartId."').bind('jqplotMouseMove', function (ev, gridpos, datapos, neighbor, plot) {KAJONA.admin.jqplotHelper.mouseMove(ev, gridpos, datapos, neighbor, plot, '".$strTooltipId."')});";
        $strChartCode .= "$('#".$strChartId."').bind('jqplotMouseLeave', function (ev, gridpos, datapos, neighbor, plot) {KAJONA.admin.jqplotHelper.mouseLeave(ev, gridpos, datapos, neighbor, plot, '".$strTooltipId."')});";

        $strChartCode .="});";



        $strReturn .= "<script type='text/javascript'>
                KAJONA.admin.loader.loadFile(['/core/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.min.js', '/core/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.min.css'], function() {
                    KAJONA.admin.loader.loadFile([
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.logAxisRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.barRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pointLabels.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.cursor.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.enhancedLegendRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pieRenderer.min.js',
                        '/core/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.highlighter.min.js',

                        '/core/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom_helper.js',
                        '/core/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom.css'
                    ], function() {
                        ".$strChartCode."
                    });
                });
        </script>";

        return $strReturn;
    }


    /**
     * Create a deep copy of the given array containing no elements with null values.
     * Also removes empty arrays from the given array.
     *
     * @param $arrInput
     *
     * @return array|null
     */
    private function cleanUpArray($arrInput) {
        // If it is an element, then just return it
        if (!is_array($arrInput)) {
            return $arrInput;
        }
        $arrNonEmptyItems = array();

        foreach ($arrInput as $key => $value) {
            // Ignore null values
            if($value!==null) {
                // Use recursion to evaluate values
                $returnValue = $this->cleanUpArray($value);
                if($returnValue!==null)
                    $arrNonEmptyItems[$key] = $this->cleanUpArray($value);
            }
        }

        //Only return the array if it contains elements, else null
        if(count($arrNonEmptyItems)>0)
            return $arrNonEmptyItems;
        else
            return null;
    }

    private function strCreateJSOptions() {
        /*
        Sort the series data array
        Bar charts must be plotted before line charts
        Also consider the order in which the series were added)
        */
        uasort($this->arrSeriesData, function(class_graph_jqplot_seriesdata $objLeft, class_graph_jqplot_seriesdata $objRight) {
            $intLeft = $objLeft->getIntChartType();
            $intRight = $objRight->getIntChartType();

            if($intLeft == $intRight) {
                //consider order in which the series was added
                if($objLeft->getIntSeriesDataOrder() < $objRight->getIntSeriesDataOrder()) {
                    return -1;
                }
                else {
                    return 1;
                }
            }
            if($intLeft < $intRight)  return -1;
            if($intLeft > $intRight)  return 1;
        });



        //add series options of each series to $arrOptions
        foreach($this->arrSeriesData as $arrSeriesData) {
            $this->arrOptions["series"][] = $arrSeriesData->getArrSeriesOptions();
        }

        //remove all values which are null
        $this->arrOptions = $this->cleanUpArray($this->arrOptions);

        //now encode to JSON
        $strEncode =  json_encode($this->arrOptions);
        $strEncode = preg_replace('/\\"\\$\\.jqplot\\.([a-zA-Z]+)\\"/', "$.jqplot.$1", $strEncode);//remove '"' where a jquery call is executed

        return $strEncode;
    }

    private function strCreateJSDataArray() {
        $arrData = array();

        if($this->containsChartType(class_graph_jqplot_charttype::PIE) && $this->arrXAxisTickLabels != null) {
            foreach($this->arrSeriesData as $keySeries => $arrSeriesData) {
                $arrSeries = array();
                $arrDataTemp = $this->arrSeriesData[$keySeries]->getArrDataArray();
                foreach($this->arrXAxisTickLabels as $keyLabel => $strLabelData) {
                    $arrSeries[]= array($strLabelData, $arrDataTemp[$keyLabel]);
                }
                $arrData[] = $arrSeries;
            }
        }
        else {
            foreach($this->arrSeriesData as $arrSeriesData) {
                $arrData[] = $arrSeriesData->getArrDataArray();
            }
        }

        return json_encode($arrData);
    }

    /**
     * Set the title of the x-axis
     *
     * @param string $strTitle
     */
    public function setStrXAxisTitle($strTitle) {
        $this->arrOptions["axes"]["xaxis"]["label"] = $strTitle;

    }

    /**
     * Set the title of the y-axis
     *
     * @param string $strTitle
     */
    public function setStrYAxisTitle($strTitle) {
        $this->arrOptions["axes"]["yaxis"]["label"] = $strTitle;
    }

    /**
     * Set the title of the graph
     *
     * @param string $strTitle
     */
    public function setStrGraphTitle($strTitle) {
        $this->arrOptions["title"]["text"] = $strTitle;
    }

    /**
     * Set the color of the margin-areas, so the color of the area not being
     * the plot-area.
     * In most cases this is the background.
     *
     * @param string $strColor in hex-values: #ccddee
     */
    public function setStrBackgroundColor($strColor) {
        $this->arrOptions["grid"]["background"] = $strColor;
    }

    /**
     * Set the total width of the chart
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    /**
     * Set the total height of the chart
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    /**
     * Set the labels to be used for the x-axis.
     * Make sure to set them before adding datasets!
     *
     * @param array $arrXAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of x-axis labels to be printed
     */
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $this->arrXAxisTickLabels = $arrXAxisTickLabels;
        $this->intNrOfWrittenLabels = $intNrOfWrittenLabels;

        $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";
        $this->arrOptions["axes"]["xaxis"]["ticks"] = $arrXAxisTickLabels;
    }

    /**
     * Sets if to render a legend or not
     *
     * @param bool $bitRenderLegend
     */
    public function setBitRenderLegend($bitRenderLegend) {
        if($bitRenderLegend === true) {
            $this->arrOptions["legend"]["show"] = $bitRenderLegend;
            $this->arrOptions["legend"]["renderer"] = "$.jqplot.EnhancedLegendRenderer";
            $this->arrOptions["legend"]["rowSpacing"] = "0px";
        }
        else {
            $this->arrOptions["legend"]["show"] = null;
            $this->arrOptions["legend"]["renderer"] = null;
            $this->arrOptions["legend"]["rowSpacing"] = null;
        }
    }

    /**
     * Set the font to be used in the chart
     *
     * @param string $strFont
     */
    public function setStrFont($strFont) {
        $this->arrOptions["fontFamily"] = $strFont;
        $this->arrOptions["title"]["rendererOptions"]["fontFamily"] = $strFont;
        $this->arrOptions["legend"]["rendererOptions"]["fontFamily"] = $strFont;
        $this->arrOptions["axesDefaults"]["tickOptions"]["fontFamily"] = $strFont;
        $this->arrOptions["axesDefaults"]["labelOptions"]["fontFamily"] = $strFont;
    }

    /**
     * Set the color of the fonts used in the chart
     *
     * @param string $strFontColor
     */
    public function setStrFontColor($strFontColor) {
        $this->arrOptions["textColor"] = $strFontColor;
        $this->arrOptions["title"]["rendererOptions"]["textColor"] = $strFontColor;
        $this->arrOptions["legend"]["rendererOptions"]["textColor"] = $strFontColor;
        $this->arrOptions["axesDefaults"]["tickOptions"]["textColor"] = $strFontColor;
        $this->arrOptions["axesDefaults"]["labelOptions"]["textColor"] = $strFontColor;
    }

    /**
     * Sets the angle to be used for rendering the x-axis lables
     *
     * @param int $intXAxisAngle
     */
    public function setIntXAxisAngle($intXAxisAngle) {
        $this->arrOptions["axes"]["xaxis"]["tickOptions"]["angle"] = $intXAxisAngle;
    }

}