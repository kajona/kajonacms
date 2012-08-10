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
        $objGraph->setBitRenderLegend("false");
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 1");
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 3");
        echo $objGraph->renderGraph();
        
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->createPieChart(array(1,20,30,40), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->getObjChartData()->formatLabels("font-size:11px ;text-align:center; padding:2px; color:white");
        $objGraph->getObjChartData()->show3d();
        $objGraph->getObjChartData()->showLabelBackground();
        $objGraph->getObjChartData()->setPieChartRaduis("0.8");
        $objGraph->getObjChartData()->setLablesInsidePieChart("0.6");
        $objGraph->setBitRenderLegend("false");
        //$objGraph->getObjChartData()->disableLabels();
        echo $objGraph->renderGraph();
        
        "<div stlye=\"-webkit-transform: rotate(120deg);\">\"+val+\"</div>";
        
        echo"<br/>";
    }
}

