<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_charts_highchartsTest extends class_testbase  {

    public function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";


        //JS-Imports for minimal system setup
        echo "<script type=\"text/javascript\">KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = '"._system_browser_cachebuster_."';</script>\n";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/admin/scripts/jquery/jquery.min.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/system/scripts/loader.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/admin/scripts/kajona.js\"></script>";

        //jqPlot
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.logAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.barRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.categoryAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasTextRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pointLabels.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.highlighter.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.cursor.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.enhancedLegendRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.dateAxisRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.pieRenderer.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/plugins/jqplot.canvasOverlay.js\"></script>";
//        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/jqplot/jquery.jqplot.css\"></link>";
        //custom
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom_helper.js\"></script>";
//        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/custom/jqPlotTest.js\"></script>";
//        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\""._webpath_.class_resourceloader::getInstance()->getCorePathForModule("module_jqplot")."/module_jqplot/admin/scripts/js/custom/jquery.jqplot.custom.css\"></link>";
        //test-Divs
//        echo "<div id=\"ChartDIV\"></div>";
//        echo "<div id=\"ChartDIV2\"></div>";


        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
        $objGraph->addLinePlot(array(8.112, 1, 2, 4), null);
        $objGraph->addLinePlot(array(1, 2, 3, 4), null);
        $objGraph->addLinePlot(array(4, 7, 1, 2), null);
        $objGraph->addLinePlot(array(4, 3, 2, 1), null);
        $objGraph->addLinePlot(array(-5, 3, -2, 1), null);
        $objGraph->setBitRenderLegend(false);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrBackgroundColor("#F0F0F0");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setStrFontColor("#FE2E2E");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setStrFont("Open Sans");
        echo $objGraph->renderGraph();

        echo "<br/>";

        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
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

        echo "<br/>";

        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
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

        echo "<br/>";

        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
        $objGraph->setStrGraphTitle("A Mixed Chart");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 3", true);
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 4", true);
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 5");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 6");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 7");
        $objGraph->addLinePlot(array(8, 1, 2, 4), "serie 8");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 9");
        $objGraph->addBarChartSet(array(1, 2, 3, 4), "serie 10");
        $objGraph->addLinePlot(array(1, 2, 3, 4), "serie 11");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        echo "<br/>";

        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
        $objGraph->addLinePlot(array(1, 2, 7, 0, 0, 0, 2, 0, 0, 0, 5, 0, 0, 0, 0, 6, 0, 0, 0, 20, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1), "serie 1");
        $objGraph->addLinePlot(array(1, 2, 7, 0, 0, 0, 2, 0, 0, 0, 5, 0, 3, 0, 0, 5, 0, 0, 0, 18, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9", "v10", "v11", "v12", "v13", "v14", "v15", "v16", "v17", "v18", "v19", "v20", "v21", "v22", "v23", "v24", "v25", "v26", "v27", "v28", "v29", "v30", "v31", "v32", "v33", "v34", "v35", "v36", "v37", "v38", "v39", "v40"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        echo "<br/>";

        //create a stacked bar chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        $objGraph->addStackedBarChartSet(array(8, -5, 7, 8, 4, 12, 1, 1, 1, 3, 4, 5, 6), "serie 1");
        $objGraph->addStackedBarChartSet(array(3, -4, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9", "v10", "v11", "v12", "v13"));
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        echo "<br/>";

        //create a stacked bar chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Horizontl Bar Chart");
        $objGraph->addStackedBarChartSet(array(8, -5, 7, 8, 4, 12, 1, 1, 1, 3, 4, 5, 6), "serie 1", true);
        $objGraph->addStackedBarChartSet(array(3, -4, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 2", true);
        $objGraph->addStackedBarChartSet(array(3, -4, 6, 2, 5, 2, 2, 2, 2, 3, 4, 5, 6), "serie 3", true);
        $objGraph->setArrXAxisTickLabels(array("x1", "x2", "x3", "x4", "x5", "x6", "x7", "x8", "x9", "x10", "x11", "x12", "x13"), 5);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrFont("open sans");
        echo $objGraph->renderGraph();

        echo "<br/>";
//
//        //create pie chart
//        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
//        $objGraph->setStrGraphTitle("A Pie Chart");
//        $objGraph->createPieChart(array(231.23524234234, 20.2342344, 30, 40), array("val 1", "val 2", "val 3", "val 4"));
//        $objGraph->setBitRenderLegend(true);
//        $objGraph->setStrFont("open sans");
//        echo $objGraph->renderGraph();
//
//
//        //create pie chart
//        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_HIGHCHARTS);
//        $objGraph->setStrGraphTitle("A Pie Chart 2");
//        $objGraph->createPieChart(array(1), array("val 1"));
//        $objGraph->setBitRenderLegend(true);
//        $objGraph->setStrFont("open sans");
//        echo $objGraph->renderGraph();


    }
}

