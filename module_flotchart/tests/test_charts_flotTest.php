<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_charts_flotTest extends class_testbase  {

    public function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";


        //JS-Imports for minimal system setup
        //echo "<link rel=\"stylesheet\" type=\"text/css\" href=\""._webpath_."/core/module_flotchart/admin/css/flotStyleSheets.css\">";
        echo "<script type=\"text/javascript\">KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = '"._system_browser_cachebuster_."';</script>\n";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_system/admin/scripts/jquery/jquery.min.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_system/system/scripts/loader.js\"></script>";
        echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_system/admin/scripts/kajona.js\"></script>";
        //echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/flot_helper.js\"></script>";
        //echo "<script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.flot.js\"></script>";

        //create bar charts
        for ($i = 1; $i < 6; $i++) {
            //class_test_charts_flotTest::createBarChart(class_graph_flot_seriesdatatypes::BAR, $i,$i);
        }
        
        //create stacked bar charts
        for ($i = 1; $i < 6; $i++) {
            //class_test_charts_flotTest::createBarChart(class_graph_flot_seriesdatatypes::STACKEDBAR, $i,$i);
        }

        //create line chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
        $objGraph->addLinePlot(array(8.112,1,2,4), "serie 1");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addLinePlot(array(4,7,1,2), "serie 3");
        $objGraph->addLinePlot(array(4,3,2,1), "serie 4");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrBackgroundColor("#F0F0F0");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        echo $objGraph->renderGraph();
        
        $objGraph->setStrFontColor("#FF0000");
        echo $objGraph->renderGraph();
        
        $objGraph->setStrFontColor(null);
        $objGraph->setStrFont("Arial");
        echo $objGraph->renderGraph();
        
        $objGraph->setStrFontColor("#FF0000");
        $objGraph->setStrFont("Verdana");
        echo $objGraph->renderGraph();
        
        //create a bar chart with different widths
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("A Bar Chart");
        $objGraph->addBarChartSet(array(1,4,3,6), "serie 1");
        $objGraph->addBarChartSet(array(3,3,6,2), "serie 2");
        $objGraph->addBarChartSet(array(4,4,8,6), "serie 3");
        $objGraph->addBarChartSet(array(10,7,3,3), "serie 4");
        $objGraph->addBarChartSet(array(6,7,3,20), "serie 5");
        $objGraph->addBarChartSet(array(6,8,3,20), "serie 5");
        $objGraph->addBarChartSet(array(10,9,3,12), "serie 5");
        $objGraph->addBarChartSet(array(6,45,3,30), "serie 5");  
        $objGraph->addBarChartSet(array(9,2,3,40), "serie 5");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));
        $objGraph->setIntXAxisAngle(-20);
        $objGraph->setIntHeight(350);
        $objGraph->setIntWidth(300);
        $objGraph->setBitRenderLegend(false);
        echo $objGraph->renderGraph();
        
        for ($i = 400; $i < 1000; $i+=100) {
            $objGraph->setIntWidth($i);
            //echo $objGraph->renderGraph();
        }
        
        //create a stacked bar chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        $objGraph->addStackedBarChartSet(array(8,-5,7,8,4,12,1,1,1,3,4,5,6), "serie 1");
        $objGraph->addStackedBarChartSet(array(3,-4,6,2,5,2,2,2,2,3,4,5,6), "serie 2");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6","v7","v8","v9","v10","v11","v12","v13"));
        echo $objGraph->renderGraph();
        
        //create a mixed chart with lines and bars
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("A Mixed Chart");
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 3");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 4");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 5");
        $objGraph->setBitRenderLegend(true);
        echo $objGraph->renderGraph();
        
        
        //create pie chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("A Pie Chart");
        $objGraph->createPieChart(array(1,20,30,40), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->setBitRenderLegend(true);
        echo $objGraph->renderGraph();

        
        //create pie chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("A Pie Chart 2");
        $objGraph->createPieChart(array(1), array("val 1"));
        $objGraph->setBitRenderLegend(true);
        echo $objGraph->renderGraph();
        
        //create a bar chart with different widths
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->setStrGraphTitle("Searchqueries");
        $objGraph->addBarChartSet(array(12,11,10,9,8,7,6,5), "serie 1");
        $objGraph->setArrXAxisTickLabels(array("Tags", "https", "cache", "portallogin", "suche", "dokumentation", "template", "toolkit"));
        $objGraph->setIntXAxisAngle(10);
        $objGraph->setIntHeight(350);
        $objGraph->setIntWidth(700);
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrXAxisTitle("My new X-Axis");
        $objGraph->setStrYAxisTitle("My new Y-Axis");
        echo $objGraph->renderGraph();
        
        
        
        
        //create line chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->addLinePlot(array(8.112,1,2,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0), "serie 1");
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        echo $objGraph->renderGraph();
        
        
        //create line chart
        $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);
        $objGraph->addLinePlot(array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0), "serie 1");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6","v7","v8","v9","v10","v11","v12","v13","v14","v15","v16","v17","v18","v19","v20","v21","v22","v23","v24","v25","v26","v27","v28","v29","v30"));
        $objGraph->setBitRenderLegend(true);
        $objGraph->setStrXAxisTitle("XXX");
        $objGraph->setStrYAxisTitle("YYY");
        $objGraph->setStrGraphTitle("My First Line Chart");
        $objGraph->setIntHeight(500);
        $objGraph->setIntWidth(700);
        echo $objGraph->renderGraph();
        //class_carrier::getInstance()->getObjTestmanager()->generateRequiredTestdata();
        
        echo"<br/>";
    }
    
    
    public static function createBarChart($barChartType, $noOfSets = 1, $nrOfBarsPerSet = 1) {
        $width = 140;
        $height = 100;
        for ($i = 0; $i < 10; $i++) {
             $objGraph = class_graph_factory::getGraphInstance(class_graph_factory::$STR_TYPE_FLOT);;
             $objGraph->setStrGraphTitle("A Bar Chart");
             
             for ($j = 0; $j < $noOfSets; $j++) {
                $arr = array();
                for ($k = 0; $k < $nrOfBarsPerSet; $k++) {
                   $randVal = rand(1,15);
                   array_push($arr, $randVal);
                   
               }
               
               if($barChartType == class_graph_flot_seriesdatatypes::BAR) {
                    $objGraph->addBarChartSet($arr, "serie ".$j);
               }
               else if($barChartType == class_graph_flot_seriesdatatypes::STACKEDBAR) {
                   $objGraph->addStackedBarChartSet($arr, "serie ".$j);
               }
               
             }
             
             $objGraph->setIntHeight($width);
             $objGraph->setIntWidth($height);
             echo $objGraph->renderGraph();
             $width += 50;
             $height += 50;
        }
    }
}

