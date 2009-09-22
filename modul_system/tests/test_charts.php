<?php

include_once(_systempath_."/class_modul_system_common.php");

class class_test_charts implements interface_testable {



    public function test() {
        error_reporting(E_ALL ^ E_NOTICE );

        
        $this->testCharts();

        error_reporting(E_ALL );
    }



    private function testCharts() {
        include_once(_systempath_."/class_graph.php");

        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";

        echo "\tbar chart...\n";

        $objGraph = new class_graph();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Graph");
        $objGraph->createBarChart(array("value 1" => 4, "value 2" => 5, "value 3" => 2, "value 4" => 7));
        $objGraph->saveGraph(_images_cachepath_."/graph1.png");

        echo "\t <img src=\""._webpath_."/../portal/pics/cache/graph1.png\" />\n";


        echo "\tgrouped bar chart...\n";
        $objGraph = new class_graph();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Graph");
        $objGraph->createGroupedBarChart(array( array("value 1" => 4, "value 2" => 5, "value 3" => 2),array("value 1" => 1, "value 2" => 2, "value 3" => 3) ));
        $objGraph->saveGraph(_images_cachepath_."/graph2.png");
        echo "\t <img src=\""._webpath_."/../portal/pics/cache/graph2.png\" />\n";




        echo "\tline chart...\n";
        $objGraph = new class_graph();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Graph");
        $objGraph->createLinePlotChart();
        $objGraph->addLinePlot(array(2,4,5,7,8), "blue");
        $objGraph->addLinePlot(array(4,4,3,9,1), "red");
        $objGraph->setXAxisTickLabels(array("eins", "zwo", "drii", "fier", "pfuenf"));
        $objGraph->saveGraph(_images_cachepath_."/graph3.png");
        echo "\t <img src=\""._webpath_."/../portal/pics/cache/graph3.png\" />\n";

        echo "\t3d pie chart...\n";
        $objGraph = new class_graph();
        $objGraph->setStrGraphTitle("Test Graph");
        $objGraph->create3DPieChart(array("v1" => 10, "v2" => 30, "v3" => 30, "v4" => 20, "v5" => 10));
        $objGraph->saveGraph(_images_cachepath_."/graph4.png");
        echo "\t <img src=\""._webpath_."/../portal/pics/cache/graph4.png\" />\n";
    }



    
}

?>