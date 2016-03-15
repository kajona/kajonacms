<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

namespace Kajona\Jqplot\System;

use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\GraphCommons;
use Kajona\System\System\GraphInterface;
use Kajona\System\System\Resourceloader;


/**
 * This class could be used to create graphs based on the jqPlot API.
 * jqPlot renders charts on the client side.
 *
 * @package module_jqplot
 * @since 4.3
 * @author stefan.meyer1@yahoo.de
 */
class GraphJqplot implements GraphInterface
{

    private $intWidth = 700;
    private $intHeight = 350;

    private $arrXAxisTickLabels = null;
    private $arrYAxisTickLabels = null;
    private $intNrOfWrittenLabelsXAxis = null;
    private $intNrOfWrittenLabelsYAxis = null;
    private $arrSeriesColors = null;

    private $bitIsHorizontalBar = false;

    private $bitIsResizeable = true;
    private $bitDownloadLink = true;

    const STRING_FORMAT = "%s";


    /**
     * contains all series data per added chart
     * @var GraphJqplotSeriesdata[]
     */
    private $arrSeriesData = array(); //

    // array which contains all used jqPlot-Options.
    private $arrOptions = array(
        "seriesColors" => array("#8bbc21", "#2f7ed8", "#f28f43", "#1aadce", "#77a1e5", "#0d233a", "#c42525", "#a6c96a", "#910000"),
        "title" => array(
            "text" => null,
            "rendererOptions" => array(
                "textColor" => null,
                "fontFamily" => null
            )
        ),
        "highlighter" => array(
            "show" => false,
            "bringSeriesToFront" => false,
            "showMarker" => false
        ),

        "legend" => array(
            "renderer" => "$.jqplot.EnhancedLegendRenderer",
            "show" => true,
            "rendererOptions" => array(
                "textColor" => null,
                "fontFamily" => null,
                "numberRows" => null,
                "numberColumns" => 5,
                "seriesToggleReplot" => array(
                    "resetAxes" => true,
                    "clear" => true,
                ),
                "location" => "n",
                "placement" => "outsideGrid",
                "rowSpacing" => "1px",
            ),
        ),
        "grid" => array(
            "background" => "transparent",
            "shadow" => false,
            "drawBorder" => false,
            "borderWidth" => 0.5
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
                "fontSize" => null,
                "formatString" => self::STRING_FORMAT
            )
        ),
        "seriesDefaults" => array(
            "useNegativeColors" => false,
            "rendererOptions" => array(
                "animation" => array(
                    "show" => true,
                    "speed" => 1000
                )
            )
        ),
        "axes" => array(
            "xaxis" => array(
                "renderer" => null,
                "label" => null,
                "max" => null,
                "min" => null,
                "ticks" => null,
                "showTicks" => null,
                "tickOptions" => array(
                    "angle" => null,
                    "showGridline" => false
                )
            ),
            "yaxis" => array(
                "renderer" => null,
                "label" => null,
                "max" => null,
                "min" => null,
                "ticks" => null,
                "showTicks" => null,
                "tickOptions" => array(
                    "showGridline" => true
                )
            ),
            "y2axis" => array(
                "renderer" => null,
                "label" => null,
                "max" => null,
                "min" => null,
                "ticks" => null,
                "showTicks" => null,
                "tickOptions" => array(
                    "showGridline" => false
                )
            )
        ),
        "series" => array()
    );


    /**
     * Checks if the chart contains the given chart type
     *
     * @param $intChartType
     *
     * @return bool
     */
    private function containsChartType($intChartType)
    {
        foreach ($this->arrSeriesData as $objSeriesData) {
            if ($objSeriesData->getIntChartType() === $intChartType) {
                return true;
            }
        }
        return false;
    }


    /**
     * Gets series objects of the given chart type
     *
     * @param array $arrChartTypes
     *
     * @return GraphJqplotSeriesdata[]
     */
    private function getSeriesObjectsByChartType(array $arrChartTypes)
    {
        $arrSeriesObjects = array();

        foreach ($this->arrSeriesData as $objSeriesData) {
            if (in_array($objSeriesData->getIntChartType(), $arrChartTypes)) {
                $arrSeriesObjects[] = $objSeriesData;
            }
        }
        return $arrSeriesObjects;
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
     *
     *  //simple array
     *      $objGraph->addBarChartSet(array(1,2,4,5) "serie 1");
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->addBarChartSet(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param string $strLegend
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
     *
     * @throws Exception
     */
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false)
    {
        $arrDataPoints = GraphCommons::convertArrValuesToDataPointArray($arrValues);

        if ($this->containsChartType(GraphJqplotCharttype::PIE)) {
            throw new Exception("Chart already contains a Pie chart. Combinations of pie charts and bar charts are not allowed", Exception::$level_ERROR);
        }
        if ($this->containsChartType(GraphJqplotCharttype::STACKEDBAR)) {
            throw new Exception("Chart already contains a stacked bar chart. Combinations of bar charts and stacked bar charts are not allowed", Exception::$level_ERROR);
        }

        $objSeriesData = new GraphJqplotSeriesdata(GraphJqplotCharttype::BAR, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);
        $objSeriesData->setBitWriteValues($bitWriteValues);

        $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";

        $this->arrSeriesData[] = $objSeriesData;
    }


    /**
     * Used to create a stacked bar-chart.
     * For each set of bar-values you can call this method once.
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *
     *
     *  //simple array
     *      $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 1");
     *      $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 2");
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->addStackedBarChartSet(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param string $strLegend
     *
     * @throws Exception
     */
    public function addStackedBarChartSet($arrValues, $strLegend)
    {
        $arrDataPoints = GraphCommons::convertArrValuesToDataPointArray($arrValues);

        if ($this->containsChartType(GraphJqplotCharttype::PIE)) {
            throw new Exception("Chart already contains a Pie chart. Combinations of pie charts and stacked bar charts are not allowed", Exception::$level_ERROR);
        }
        if ($this->containsChartType(GraphJqplotCharttype::BAR)) {
            throw new Exception("Chart already contains a bar chart. Combinations of bar charts and stacked bar charts are not allowed", Exception::$level_ERROR);
        }

        $objSeriesData = new GraphJqplotSeriesdata(GraphJqplotCharttype::STACKEDBAR, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";

        $this->arrOptions["stackSeries"] = true;
        $this->arrSeriesData[] = $objSeriesData;
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
     *
     *  //simple array
     *      $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->addLinePlot(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param string $strLegend the name of the single plot
     *
     * @throws Exception
     */
    public function addLinePlot($arrValues, $strLegend)
    {
        $arrDataPoints = GraphCommons::convertArrValuesToDataPointArray($arrValues);

        if ($this->containsChartType(GraphJqplotCharttype::PIE)) {
            throw new Exception("Chart already contains a pie chart. Combinations of pie charts and line charts are not allowed", Exception::$level_ERROR);
        }

        $objSeriesData = new GraphJqplotSeriesdata(GraphJqplotCharttype::LINE, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrSeriesData[] = $objSeriesData;
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
     *
     *  //simple array
     *      $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->addLinePlot(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param string $strLegend the name of the single plot
     *
     * @throws Exception
     */
    public function addLinePlotY2Axis($arrValues, $strLegend)
    {
        $arrDataPoints = GraphCommons::convertArrValuesToDataPointArray($arrValues);

        if ($this->containsChartType(GraphJqplotCharttype::PIE)) {
            throw new Exception("Chart already contains a pie chart. Combinations of pie charts and line charts are not allowed", Exception::$level_ERROR);
        }

        $objSeriesData = new GraphJqplotSeriesdata(GraphJqplotCharttype::LINE_Y2AXIS, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrSeriesData[] = $objSeriesData;
    }

    /**
     * Creates a new pie-chart. Pass the values as the first param. If
     * you want to use a legend and / or Colors use the second and third param.
     * Make sure the array have the same number of elements, ohterwise they won't
     * be uses.
     * A sample-code could be:
     *  $objChart = new class_graph();
     *  $objChart->setStrGraphTitle("Test Pie Chart");
     *
     * //simple array
     *      $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->createPieChart(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) , array("val 1", "val 2", "val 3", "val 4"), "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param array $arrLegends
     *
     * @throws Exception
     */
    public function createPieChart($arrValues, $arrLegends)
    {
        $arrDataPoints = GraphCommons::convertArrValuesToDataPointArray($arrValues);

        if ($this->containsChartType(GraphJqplotCharttype::LINE)
            || $this->containsChartType(GraphJqplotCharttype::BAR)
            || $this->containsChartType(GraphJqplotCharttype::STACKEDBAR)
        ) {
            throw new Exception("Chart already contains either a line, bar or stacked bar chart. Combinations of pie charts with other charts are not allowed", Exception::$level_ERROR);
        }
        if ($this->containsChartType(GraphJqplotCharttype::PIE)) {
            throw new Exception("Chart already contains either a pie chart.Only one pie chart per chart is allowed", Exception::$level_ERROR);
        }

        $objSeriesData = new GraphJqplotSeriesdata(GraphJqplotCharttype::PIE, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);

        $this->arrXAxisTickLabels = $arrLegends;//set to this array, as the data array is built up similar
        $this->arrSeriesData[] = $objSeriesData;
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * sends the graph directly (!!!) to the browser.
     * Execution should be terminated afterwards.
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use GraphInterface::renderGraph() instead
     */
    public function showGraph()
    {
        $this->renderGraph();
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * saves the graph to the specified filename
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use GraphInterface::renderGraph() instead
     */
    public function saveGraph($strFilename)
    {
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
     * @throws Exception
     * @return mixed
     */
    public function renderGraph()
    {
        if (count($this->arrSeriesData) == 0) {
            throw new Exception("Chart not initialized yet", Exception::$level_ERROR);
        }

        $this->preGraphGeneration();

        //1. create id's
        $strSystemId = generateSystemid();
        $strResizeableId = "resize_" . $strSystemId;
        $strChartId = "chart_" . $strSystemId;
        $strTooltipId = "tooltip_" . $strSystemId;
        $strImageExportId = $strChartId . "_exportpng";

        //create div where the chart is being put
        $strReturn = "<div onmouseover='$(\"#\"+\"{$strImageExportId}\").show();' onmouseout='$(\"#\"+\"{$strImageExportId}\").hide();' id=\"$strResizeableId\" style=\"width:" . $this->intWidth . "px; height:" . $this->intHeight . "px;\">";

        //chart div
        $strReturn .= "<div id=\"$strChartId\" style=\"width:95%; height:100%; float: left;\"></div>";

        //image export div
        $strReturn .= "<div style=\"width:5%; height:100%; float: left;\">";

        if ($this->isBitDownloadLink()) {
            $strReturn .= "<a style=\"display:none; cursor: pointer; \" id=\"{$strImageExportId}\" onclick=\"KAJONA.admin.jqplotHelper.exportAsImage('{$strChartId}')\"'>
                                   <i class='fa fa-download'></i>
                               </a>";
        }

        $strReturn .= "</div>";

        $strReturn .= "</div>";


        //2. Sort charts by type
        $this->sortBySeriesType();

        //3. create the data array and options object for the jqPlot method
        $strChartOptions = $this->strCreateJSOptions();
        $strChartData = $this->strCreateJSDataArray();
        $strDataPointObjects = $this->strCreateDataPointObjects();
        $arrPostPlotOptions = array(
            "intNrOfWrittenLabelsXAxis" => $this->intNrOfWrittenLabelsXAxis,
            "intNrOfWrittenLabelsYAxis" => $this->intNrOfWrittenLabelsYAxis
        );
        $strPostPlotOptions = json_encode($arrPostPlotOptions);

        //4. Get decimal styles
        $strDecChar = Carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system");
        $strThousandsChar = Carrier::getInstance()->getObjLang()->getLang("numberStyleThousands", "system");

        //5. Init Chart
        $strCoreDirectory = Resourceloader::getInstance()->getCorePathForModule("module_jqplot");
        $strReturn .= "<script type='text/javascript'>
                KAJONA.admin.loader.loadFile([
                '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/excanvas.js',
                '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.js',
                '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.css'], function() {
                    KAJONA.admin.loader.loadFile([
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.logAxisRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.barRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.categoryAxisRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasTextRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pointLabels.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.cursor.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.dateAxisRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.enhancedLegendRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pieRenderer.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.highlighter.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasOverlay.js',

                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom_helper.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom.css',

                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/filesaver/Blob.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/filesaver/canvas-toBlob.js',
                        '{$strCoreDirectory}/module_jqplot/admin/scripts/js/filesaver/FileSaver.js'

                    ], function() {
                        $.jqplot.sprintf.thousandsSeparator = '$strThousandsChar';
                        $.jqplot.sprintf.decimalMark = '$strDecChar';

                        var objChart_$strChartId = new KAJONA.admin.jqplotHelper.jqPlotChart('$strChartId', '$strTooltipId', '$strResizeableId', '$this->bitIsResizeable', $strChartData, $strChartOptions, $strPostPlotOptions, $strDataPointObjects);
                        objChart_$strChartId.render();
                    });
                });
        </script>";

        return $strReturn;
    }


    private function preGraphGeneration()
    {
        if ($this->bitIsHorizontalBar &&
            ($this->containsChartType(GraphJqplotCharttype::LINE) || $this->containsChartType(GraphJqplotCharttype::PIE))
        ) {
            throw new Exception("When option horizontal is set, chart cannot contain line or pie charts", Exception::$level_ERROR);
        }

        //1. Special handling if horizontal flag for bar charts is set
        if ($this->bitIsHorizontalBar) {

            //Swap X and Y Axis
            if (count($this->arrXAxisTickLabels) > 0 || $this->intNrOfWrittenLabelsXAxis == 0) {
                //keep xaxis and yaxis information
                $arrXLabelsTemp = $this->arrXAxisTickLabels;
                $intXNumberLabelsTemp = $this->intNrOfWrittenLabelsXAxis;

                //set y-Axis options - reverse the array
                if (count($arrXLabelsTemp) > 0) {
                    $arrXLabelsTemp = array_reverse($arrXLabelsTemp);
                }
                $this->setArrYAxisTickLabels($arrXLabelsTemp, $intXNumberLabelsTemp);
                $this->arrOptions["axes"]["yaxis"]["renderer"] = $this->arrOptions["axes"]["xaxis"]["renderer"];

                //reset xAxis
                $this->arrOptions["axes"]["xaxis"]["renderer"] = null;
                $this->arrOptions["axes"]["xaxis"]["ticks"] = null;
                $this->arrXAxisTickLabels = null;
                $this->intNrOfWrittenLabelsXAxis = null;
            }

            //add to each series options which are required for horizontal bar chart rendering
            foreach ($this->arrSeriesData as $objSeriesData) {
                //reverse the arrays as labels are also reversed)
                $arrData = $objSeriesData->getArrDataPoints();
                if (count($arrData) > 0) {
                    $arrData = array_reverse($arrData);
                }

                $objSeriesData->setArrDataPoints($arrData);
                if ($objSeriesData->getIntChartType() == GraphJqplotCharttype::STACKEDBAR) {
                    $arrSeriesOptions = $objSeriesData->getArrSeriesOptions();
                    $objSeriesData->setArrSeriesOptions($arrSeriesOptions);
                }
                if ($objSeriesData->getIntChartType() == GraphJqplotCharttype::BAR) {
                    $arrSeriesOptions = $objSeriesData->getArrSeriesOptions();
                    $objSeriesData->setArrSeriesOptions($arrSeriesOptions);
                }
            }

            //additionally set required global options
            $this->arrOptions["seriesDefaults"]["renderer"] = "$.jqplot.BarRenderer";
            $this->arrOptions["seriesDefaults"]["rendererOptions"]["barDirection"] = "horizontal";
        }


        //2. Change padding and margin of bars, if the chart contains only one bar chart series
        $arrSeriesBarCharts = $this->getSeriesObjectsByChartType(array(GraphJqplotCharttype::BAR, GraphJqplotCharttype::BAR_HORIZONTAL));
        if (count($arrSeriesBarCharts) == 1) {
            $objSeriesData = $arrSeriesBarCharts[0];
            $arrSeriesOptions = $objSeriesData->getArrSeriesOptions();
            $arrSeriesOptions["rendererOptions"]["barPadding"] = 1;
            $arrSeriesOptions["rendererOptions"]["barMargin"] = 4;
            $objSeriesData->setArrSeriesOptions($arrSeriesOptions);
        }

        //3. Change padding and margin of bars, if the chart contains one or more stackedbar series and each series has exactly one series value
        $arrSeriesStackedBarCharts = $this->getSeriesObjectsByChartType(array(GraphJqplotCharttype::STACKEDBAR, GraphJqplotCharttype::STACKEDBAR_HORIZONTAL));
        if (count($arrSeriesStackedBarCharts) > 0) {
            $bitChangeMarginAndPadding = true;

            //Check if each series has exactly one data point
            foreach ($arrSeriesStackedBarCharts as $objSeriesData) {
                if (!count($objSeriesData->getArrDataPoints()) == 1) {
                    $bitChangeMarginAndPadding = false;
                }
            }

            //Only if every bar series has exactly one data point, set padding and margin
            if ($bitChangeMarginAndPadding) {
                foreach ($arrSeriesStackedBarCharts as $objSeriesData) {
                    $arrSeriesOptions = $objSeriesData->getArrSeriesOptions();
                    $arrSeriesOptions["rendererOptions"]["barPadding"] = 1;
                    $arrSeriesOptions["rendererOptions"]["barMargin"] = 4;
                    $objSeriesData->setArrSeriesOptions($arrSeriesOptions);
                }
            }
        }

        //4. If stacked bar chart and line chart disable stack for the line charts
        $arrSeriesStackedBarCharts = $this->getSeriesObjectsByChartType(array(GraphJqplotCharttype::STACKEDBAR));
        $arrSeriesLineCharts = $this->getSeriesObjectsByChartType(array(GraphJqplotCharttype::LINE, GraphJqplotCharttype::LINE_Y2AXIS));
        if (count($arrSeriesStackedBarCharts) > 0 && count($arrSeriesLineCharts) > 0) {
            foreach ($arrSeriesLineCharts as $objSeriesData) {
                $arrSeriesOptions = $objSeriesData->getArrSeriesOptions();
                $arrSeriesOptions["disableStack"] = true;
                $objSeriesData->setArrSeriesOptions($arrSeriesOptions);
            }
        }
    }


    /**
     * Create a deep copy of the given array containing no elements with null values.
     * Also removes empty arrays from the given array.
     *
     * @param $arrInput
     *
     * @return array|null
     */
    private function cleanUpArray($arrInput)
    {
        // If it is an element, then just return it
        if (!is_array($arrInput)) {
            return $arrInput;
        }
        $arrNonEmptyItems = array();

        foreach ($arrInput as $key => $value) {
            // Ignore null values
            if ($value !== null) {
                // Use recursion to evaluate values
                $returnValue = $this->cleanUpArray($value);
                if ($returnValue !== null) {
                    $arrNonEmptyItems[$key] = $this->cleanUpArray($value);
                }
            }
        }

        //Only return the array if it contains elements, else null
        if (count($arrNonEmptyItems) > 0) {
            return $arrNonEmptyItems;
        } else {
            return null;
        }
    }

    private function sortBySeriesType()
    {
        /*
        Sort the series data array
        Bar charts must be plotted before line charts
        Also consider the order in which the series were added)
        */
        uasort($this->arrSeriesData, function (GraphJqplotSeriesdata $objLeft, GraphJqplotSeriesdata $objRight) {
            $intLeft = $objLeft->getIntChartType();
            $intRight = $objRight->getIntChartType();

            if ($intLeft == $intRight) {
                //consider order in which the series was added
                if ($objLeft->getIntSeriesDataOrder() < $objRight->getIntSeriesDataOrder()) {
                    return -1;
                } else {
                    return 1;
                }
            }
            if ($intLeft < $intRight) {
                return -1;
            }
            if ($intLeft > $intRight) {
                return 1;
            }
        });
    }

    private function strCreateDataPointObjects()
    {
        //add series options of each series to $arrOptions
        $arrSeries2DataPoints = array();
        foreach ($this->arrSeriesData as $objSeriesData) {
            $arrDataPoints = array();
            foreach ($objSeriesData->getArrDataPoints() as $objDataPoint) {
                $arrDataPoints[] = array(
                    "floatvalue" => $objDataPoint->getFloatValue(),
                    "actionhandlervalue" => $objDataPoint->getObjActionHandlerValue(),
                    "actionhandler" => $objDataPoint->getObjActionHandler()
                );
            }
            $arrSeries2DataPoints[] = $arrDataPoints;
        }

        $strEncode = json_encode($arrSeries2DataPoints);
        return $strEncode;
    }

    private function strCreateJSOptions()
    {
        //add series options of each series to $arrOptions
        foreach ($this->arrSeriesData as $objSeriesData) {
            $this->arrOptions["series"][] = $objSeriesData->getArrSeriesOptions();
        }

        //remove all values which are null
        $this->arrOptions = $this->cleanUpArray($this->arrOptions);

        //now encode to JSON
        $strEncode = json_encode($this->arrOptions);
        $strEncode = preg_replace('/\\"\\$\\.jqplot\\.([a-zA-Z]+)\\"/', "$.jqplot.$1", $strEncode);//remove '"' where a jquery call is executed

        return $strEncode;
    }

    private function strCreateJSDataArray()
    {
        $arrData = array();

        if ($this->containsChartType(GraphJqplotCharttype::PIE) && $this->arrXAxisTickLabels != null) {
            foreach ($this->arrSeriesData as $keySeries => $objSeriesData) {
                $arrSeries = array();
                $arrDataPointsTemp = $objSeriesData->getArrDataPoints();
                foreach ($this->arrXAxisTickLabels as $keyLabel => $strLabelData) {
                    $arrSeries[] = array($strLabelData, $arrDataPointsTemp[$keyLabel]->getFloatValue());
                }
                $arrData[] = $arrSeries;
            }
        } else {
            foreach ($this->arrSeriesData as $objSeriesData) {
                $arrData[] = GraphCommons::getDataPointFloatValues($objSeriesData->getArrDataPoints());
            }
        }

        return json_encode($arrData);
    }

    /**
     * Set the title of the x-axis
     *
     * @param string $strTitle
     */
    public function setStrXAxisTitle($strTitle)
    {
        $this->arrOptions["axes"]["xaxis"]["label"] = $strTitle;

    }

    /**
     * Set the title of the y-axis
     *
     * @param string $strTitle
     */
    public function setStrYAxisTitle($strTitle)
    {
        $this->arrOptions["axes"]["yaxis"]["label"] = $strTitle;
    }

    /**
     * Set the title of the y-axis
     *
     * @param string $strTitle
     */
    public function setStrY2AxisTitle($strTitle)
    {
        $this->arrOptions["axes"]["y2axis"]["label"] = $strTitle;
    }

    /**
     * Set the title of the graph
     *
     * @param string $strTitle
     */
    public function setStrGraphTitle($strTitle)
    {
        $this->arrOptions["title"]["text"] = $strTitle;
    }

    /**
     * Set the color of the margin-areas, so the color of the area not being
     * the plot-area.
     * In most cases this is the background.
     *
     * @param string $strColor in hex-values: #ccddee
     */
    public function setStrBackgroundColor($strColor)
    {
        $this->arrOptions["grid"]["background"] = $strColor;
    }

    /**
     * Set the total width of the chart
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth)
    {
        $this->intWidth = $intWidth;
    }

    /**
     * Set the total height of the chart
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight)
    {
        $this->intHeight = $intHeight;
    }

    /**
     * Set the labels to be used for the x-axis.
     * Make sure to set them before adding datasets!
     *
     * @param array $arrXAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of x-axis labels to be printed
     */
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12)
    {
        if ($arrXAxisTickLabels != null && is_array($arrXAxisTickLabels)) {
            $this->arrXAxisTickLabels = $arrXAxisTickLabels;
            $this->arrOptions["axes"]["xaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";
            $this->arrOptions["axes"]["xaxis"]["ticks"] = $arrXAxisTickLabels;
        }

        $this->intNrOfWrittenLabelsXAxis = $intNrOfWrittenLabels;
        if ($intNrOfWrittenLabels === 0) {
            $this->arrOptions["axes"]["xaxis"]["showTicks"] = false;
        }
    }

    /**
     * Set the labels to be used for the y-axis.
     * Make sure to set them before adding datasets!
     *
     * @param array $arrYAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of y-axis labels to be printed
     */
    private function setArrYAxisTickLabels($arrYAxisTickLabels, $intNrOfWrittenLabels = 12)
    {
        if ($arrYAxisTickLabels != null && is_array($arrYAxisTickLabels)) {
            $this->arrYAxisTickLabels = $arrYAxisTickLabels;
            $this->arrOptions["axes"]["yaxis"]["renderer"] = "$.jqplot.CategoryAxisRenderer";
            $this->arrOptions["axes"]["yaxis"]["ticks"] = $arrYAxisTickLabels;
            $this->arrOptions["axes"]["yaxis"]["numberTicks"] = $intNrOfWrittenLabels;
        }

        $this->intNrOfWrittenLabelsYAxis = $intNrOfWrittenLabels;
        if ($intNrOfWrittenLabels === 0) {
            $this->arrOptions["axes"]["yaxis"]["showTicks"] = false;
        }
    }

    /**
     * Sets if to render a legend or not
     *
     * @param bool $bitRenderLegend
     */
    public function setBitRenderLegend($bitRenderLegend)
    {
        if ($bitRenderLegend === true) {
            $this->arrOptions["legend"]["show"] = $bitRenderLegend;
            $this->arrOptions["legend"]["renderer"] = "$.jqplot.EnhancedLegendRenderer";
            $this->arrOptions["legend"]["rendererOptions"]["placement"] = "outsideGrid";
        } else {
            $this->arrOptions["legend"]["show"] = null;
            $this->arrOptions["legend"]["renderer"] = null;
            $this->arrOptions["legend"]["rendererOptions"]["placement"] = null;
        }
    }

    /**
     * Set the font to be used in the chart
     *
     * @param string $strFont
     */
    public function setStrFont($strFont)
    {
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
    public function setStrFontColor($strFontColor)
    {
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
    public function setIntXAxisAngle($intXAxisAngle)
    {
        $this->arrOptions["axes"]["xaxis"]["tickOptions"]["angle"] = $intXAxisAngle;
    }

    /**
     * @param GraphJqplotSeriesdata[] $arrSeriesData
     */
    public function setArrSeriesData($arrSeriesData)
    {
        $this->arrSeriesData = $arrSeriesData;
    }

    /**
     * @return GraphJqplotSeriesdata[]
     */
    public function getArrSeriesData()
    {
        return $this->arrSeriesData;
    }

    /**
     * Setter for setting custom series colors.
     *
     * @param $arrSeriesColors
     *
     * @return mixed
     */
    public function setArrSeriesColors($arrSeriesColors)
    {
        $this->arrSeriesColors = $arrSeriesColors;
        $this->arrOptions["seriesColors"] = $arrSeriesColors;
    }


    /**
     * Sets the range for the xAxis.
     *
     * @param int $intMin
     * @param int $intMax
     * @param int $intTickInterval
     */
    public function setXAxisRange($intMin = null, $intMax = null, $intTickInterval = null)
    {
        if ($intMin !== null) {
            $this->arrOptions["axes"]["xaxis"]["min"] = $intMin;
        }
        if ($intMax !== null) {
            $this->arrOptions["axes"]["xaxis"]["max"] = $intMax;
        }
        if ($intTickInterval !== null) {
            $this->arrOptions["axes"]["xaxis"]["tickInterval"] = $intTickInterval;
        }
    }


    /**
     * Sets the range for the yAxis.
     *
     * @param int $intMin
     * @param int $intMax
     * @param int $intTickInterval
     */
    public function setYAxisRange($intMin = null, $intMax = null, $intTickInterval = null)
    {
        if ($intMin !== null) {
            $this->arrOptions["axes"]["yaxis"]["min"] = $intMin;
        }
        if ($intMax !== null) {
            $this->arrOptions["axes"]["yaxis"]["max"] = $intMax;
        }
        if ($intTickInterval !== null) {
            $this->arrOptions["axes"]["yaxis"]["tickInterval"] = $intTickInterval;
        }
    }

    /**
     * Sets the range for the yAxis.
     *
     * @param int $intMin
     * @param int $intMax
     * @param int $intTickInterval
     */
    public function setY2AxisRange($intMin = null, $intMax = null, $intTickInterval = null)
    {
        if ($intMin !== null) {
            $this->arrOptions["axes"]["y2axis"]["min"] = $intMin;
        }
        if ($intMax !== null) {
            $this->arrOptions["axes"]["y2axis"]["max"] = $intMax;
        }
        if ($intTickInterval !== null) {
            $this->arrOptions["axes"]["y2axis"]["tickInterval"] = $intTickInterval;
        }
    }


    /**
     * Method to render a horizontal bar chart
     *
     * @param bool $bitIsHorizontalBar
     */
    public function setBarHorizontal($bitIsHorizontalBar)
    {
        $this->bitIsHorizontalBar = $bitIsHorizontalBar;
    }


    /**
     * Hides the xAxis labels.
     * Also hide the grid line for the xAxis.
     *
     * @param bool $bitHideXAxis
     */
    public function setHideXAxis($bitHideXAxis)
    {
        if ($bitHideXAxis) {
            $this->arrOptions["axes"]["xaxis"]["rendererOptions"]["drawBaseline"] = false;
            $this->arrOptions["axes"]["xaxis"]["showTicks"] = false;
            $this->arrOptions["axes"]["xaxis"]["drawMajorTickMarks"] = false;
            $this->arrOptions["axes"]["xaxis"]["tickOptions"]["showGridline"] = false;
            $this->arrOptions["axes"]["xaxis"]["tickOptions"]["show"] = false;
            $this->arrOptions["axes"]["xaxis"]["label"] = null;
        }
    }


    /**
     * Hides the xAxis labels.
     * Also hide the grid line for the xAxis.
     *
     * @param bool $bitHideYAxis
     */
    public function setHideYAxis($bitHideYAxis)
    {
        if ($bitHideYAxis) {
            $this->arrOptions["axes"]["yaxis"]["rendererOptions"]["drawBaseline"] = false;
            $this->arrOptions["axes"]["yaxis"]["showTicks"] = false;
            $this->arrOptions["axes"]["yaxis"]["drawMajorTickMarks"] = false;
            $this->arrOptions["axes"]["yaxis"]["tickOptions"]["showGridline"] = false;
            $this->arrOptions["axes"]["yaxis"]["tickOptions"]["show"] = false;
            $this->arrOptions["axes"]["yaxis"]["label"] = null;
        }
    }

    /**
     * For each series the bar colors will vary
     *
     * @param $bitVaryBarColors
     */
    public function setVaryBarColorsForAllSeries($bitVaryBarColors)
    {
        if ($this->containsChartType(GraphJqplotCharttype::BAR)) {
            /** $arrData GraphJqplotSeriesdata */
            foreach ($this->arrSeriesData as $arrData) {
                if ($arrData->getIntChartType() == GraphJqplotCharttype::BAR) {
                    $arrSeriesOptions = $arrData->getArrSeriesOptions();
                    $arrSeriesOptions["rendererOptions"]["varyBarColor"] = $bitVaryBarColors;
                    $arrData->setArrSeriesOptions($arrSeriesOptions);
                }
            }
        }
    }

    public function setTickFormatString($strFormat)
    {
        $this->arrOptions["axes"]["xaxis"]["tickOptions"]["formatString"] = $strFormat;
        $this->arrOptions["axes"]["yaxis"]["tickOptions"]["formatString"] = $strFormat;
    }

    public function drawBorder($bitDrawBorder)
    {
        $this->arrOptions["grid"]["drawBorder"] = $bitDrawBorder;
    }

    /**
     * @return boolean
     */
    public function isBitIsResizeable()
    {
        return $this->bitIsResizeable;
    }

    /**
     * @param boolean $bitIsResizeable
     */
    public function setBitIsResizeable($bitIsResizeable)
    {
        $this->bitIsResizeable = $bitIsResizeable;
    }

    /**
     * @return boolean
     */
    public function isBitDownloadLink()
    {
        return $this->bitDownloadLink;
    }

    /**
     * @param boolean $bitDownloadLink
     */
    public function setBitDownloadLink($bitDownloadLink)
    {
        $this->bitDownloadLink = $bitDownloadLink;
    }
}
