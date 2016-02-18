<?php

namespace Kajona\Jqplot\Tests;

use Kajona\System\System\GraphFactory;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Testbase;

class ChartsJqPlotTest extends Testbase
{

    public function testCharts()
    {


        srand((double)microtime() * 1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";


        //JS-Imports for minimal system setup
        echo "<script type=\"text/javascript\">KAJONA_WEBPATH = '" . _webpath_ . "'; KAJONA_BROWSER_CACHEBUSTER = '" . SystemSetting::getConfigValue("_system_browser_cachebuster_") . "';</script>\n";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/admin/scripts/jquery/jquery.min.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/system/scripts/loader.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/admin/scripts/kajona.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js\"></script>";

        //jqPlot
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.logAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.barRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.categoryAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasTextRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pointLabels.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.highlighter.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.cursor.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.enhancedLegendRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.dateAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pieRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasOverlay.js\"></script>";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_jqplot") . "/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.css\"></link>";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_system") . "/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css\"></link>";
        //custom
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom_helper.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.Resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/custom/jqPlotTest.js\"></script>";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . _webpath_ . Resourceloader::getInstance()->getCorePathForModule("module_jqplot") . "/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom.css\"></link>";
        //test-Divs


        //create div where the chart is being put
//        echo "<div id=\"ResizeDIV\" style=\"width:700px; height:500px;\">
//                <div id=\"ChartDIV\" style=\"width:100%; height:100%;\"></div>
//            </div>";


        /** @var GraphJqplot $objGraph */
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->addLinePlot(array(8.112, 1, 2, 4), null);
        $objGraph->addLinePlot(array(1, 2, 3, 4), null);
        $objGraph->addLinePlot(array(4, 7, 1, 2), null);
        $objGraph->addLinePlot(array(4, 3, 2, 1), null);
        $objGraph->addLinePlot(array(-5, 3, -2, 1), null);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrBackgroundColor("#F0F0F0");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setStrFontColor("#FF0000");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setStrFont("Open Sans");

        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->addLinePlot(array(8.112, 1, 2, 4), null);
        $objGraph->addLinePlot(array(1, 2, 3, 4), null);
        $objGraph->addLinePlot(array(4, 7, 1, 2), null);
        $objGraph->addLinePlot(array(4, 3, 2, 1), null);
        $objGraph->addLinePlot(array(-5, 3, -2, 1), null);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrBackgroundColor("#F0F0F0");
        $objGraph->setStrGraphTitle("My First Line Chart 2");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setStrFontColor("#FF0000");
        $objGraph->setStrFont("Open Sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Bar Chart");
        $objGraph->addBarChartSet(array(1, 4, 3, 6), "serie 111111111111111");
        $objGraph->addBarChartSet(array(3, 3, 6, 2), "serie 2");
        $objGraph->addBarChartSet(array(4, 4, 8, 6), "serie 3");
        $objGraph->addBarChartSet(array(10, 7, 3, 3), "serie 4");
        $objGraph->addBarChartSet(array(6, 7, 3, 20), "serie 5");
        $objGraph->addBarChartSet(array(9, 2, 3, 40), "serie 9");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setIntHeight(350);
        $objGraph->setIntWidth(300);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFontColor("#FF0000");
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("One Bar Chart (In this case each bar has a differetn color)");
        $objGraph->addBarChartSet(array(9, 2, 3, 40), "serie 9");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setIntHeight(350);
        $objGraph->setIntWidth(300);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFontColor("#FF0000");
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Mixed Chart");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 3", true);
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 4");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 5");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 6");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 7");
        $objGraph->addLinePlot(array(8, 1, 2, 4), "serie 8");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 9");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 10");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 11");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 12");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 13");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 14");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 15");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 16");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 17");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 18");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Mixed stacked Chart");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addStackedBarChartSet(array(4, 2, 3, 4), "serie 3");
        $objGraph->addStackedBarChartSet(array(1, 3, 3, 4), "serie 4");
        $objGraph->addStackedBarChartSet(array(1, 2, 2, 3), "serie 5");
        $objGraph->addStackedBarChartSet(array(2, 2, 3, 1), "serie 6");
        $objGraph->addStackedBarChartSet(array(1, 2, 3, 4), "serie 7");
        $objGraph->addLinePlot(array(8, 1, 2, 4), "serie 8");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 9");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Bar Chart");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 9", true);
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 10");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();


        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Horizontal Bar Chart no xAxis and yAxis");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 9");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 10");
        $objGraph->setBarHorizontal(true);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        $objGraph->setHideXAxis(true);
        $objGraph->setHideYAxis(true);
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Horizontal Bar Chart with labels");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 9");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 10");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setBarHorizontal(true);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->addLinePlot(array(1, 2, 7, 0, 0, 0, 2, 0, 0, 0, 5, 0, 0, 0, 0, 6, 0, 0, 0, 20, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1), "serie 1");
        $objGraph->addLinePlot(array(1, 2, 7, 0, 0, 0, 2, 0, 0, 0, 5, 0, 3, 0, 0, 5, 0, 0, 0, 18, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9", "v10", "v11", "v12", "v13", "v14", "v15", "v16", "v17", "v18", "v19", "v20", "v21", "v22", "v23", "v24", "v25", "v26", "v27", "v28", "v29", "v30", "v31", "v32", "v33", "v34", "v35", "v36", "v37", "v38", "v39", "v40"), 10);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        //create a stacked bar chart
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        $objGraph->addStackedBarChartSet(array(0, -5, 7, 8, 4, 12, 1, 1, 1, 3, 4, 5, 6), "serie 1");
        $objGraph->addStackedBarChartSet(array(3, -4, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9", "v10", "v11", "v12", "v13"));
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        //create a stacked bar chart
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Horizontal Bar Chart");
        $objGraph->addStackedBarChartSet(array(8, -5, 7, 8, 4, 12, 1, 1, 1, 3, 4, 5, 6), "serie 1");
        $objGraph->addStackedBarChartSet(array(3, 0, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 2");
        $objGraph->addStackedBarChartSet(array(3, -4, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 3");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9", "v10", "v11", "v12", "v13"), 5);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrFont("open sans");
        $objGraph->setBarHorizontal(true);
        echo $objGraph->renderGraph();

        //create pie chart
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Pie Chart");
        $objGraph->createPieChart(array(231.23524234234, 20.2342344, 30, 40), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        //create pie chart
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Pie Chart");
        $objGraph->createPieChart(array(231, 20, 30, 40, 2, 3, 4, 5), array("val 1", "val 2", "val 3", "val 4", "v5", "v6", "v7", "v8"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();


        //create pie chart
        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Pie Chart 2");
        $objGraph->createPieChart(array(1), array("val 1"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("A Horizontal Bar Chart with labels");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(2, 4, 6, 3.3), "serie 9", true);
        $objGraph->addBarChartSet(array(5, 1, 3, 4), "serie 10", true);
        $objGraph->addBarChartSet(array(4, 7, 1, 2), "serie 11", true);
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setBarHorizontal(true);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->addLinePlot(array(0, 0, 0, 0, 0, 0, 0.5), null);
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setArrXAxisTickLabels(array("23", "24", "25", "26", "27", "28", "29"));
        echo $objGraph->renderGraph();

        $objGraph = GraphFactory::getGraphInstance(GraphFactory::$STR_TYPE_JQPLOT);
        $objGraph->setStrGraphTitle("An empty chart");
        $objGraph->addBarChartSet(array(), "legend");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        echo $objGraph->renderGraph();
    }
}

