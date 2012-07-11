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


        $objGraph = new class_graph_flot();
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addLinePlot(array(4,7,1,2), "serie 3");
        $objGraph->addLinePlot(array(4,3,2,1), "serie 4");
        echo $objGraph->renderGraph();
        
        $objGraph = new class_graph_flot();
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 1");
        echo $objGraph->renderGraph();
        
        $objGraph = new class_graph_flot();
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 3");
        echo $objGraph->renderGraph();
        
        $objGraph = new class_graph_flot();
        $objGraph->createPieChart(array(1,2,3,4), array("val 1", "val 2", "val 3", "val 4"));
        echo $objGraph->renderGraph();
        
        
        echo"<br/>";
    }
}

