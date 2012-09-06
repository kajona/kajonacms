<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_charts_flotTest extends class_testbase  {

    public function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";


        //JS-Imports for minimal system setup
        echo "<script type=\"text/javascript\">KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = '"._system_browser_cachebuster_."';</script>\n";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_system/admin/scripts/jquery/jquery.min.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_system/admin/scripts/kajona.js\"></script>";


        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addLinePlot(array(4,7,1,2), "serie 3");
        $objGraph->addLinePlot(array(4,3,2,1), "serie 4");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrFontColor("red");
        $objGraph->setStrBackgroundColor("#F0F0F0");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->setStrGraphTitle("A Bar Chart");
        $objGraph->addBarChartSet(array(1,4,3,6), "serie 1");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setIntHeight(150);
        $objGraph->setIntWidth(150);
        
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        $objGraph->addStackedBarChartSet(array(8,-5,7,8,4,12), "serie 1");
        $objGraph->addStackedBarChartSet(array(3,-4,6,2,5,2 ), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->setStrGraphTitle("A Mixed Chart");
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 3");
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("A Pie Chart");
        $objGraph->createPieChart(array(1,20,30,40), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->setBitRenderLegend(false);
        echo $objGraph->renderGraph();
        
        echo"<br/>";
    }
}

