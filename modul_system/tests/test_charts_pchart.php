<?php


class class_test_charts_pchart implements interface_testable {


    public function test() {     
        $this->testCharts();
    }


    private function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";
        
        
        echo "\tstacked pie chart...\n";
        $objGraph = new class_graph_pchart();
        $objGraph->setBitRoundedCorners(true);
        $objGraph->setStrGraphTitle("Test Pie Chart");
        
        $objGraph->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
        
        $objGraph->saveGraph(_images_cachepath_."/graph4.png");
        echo "\t <img src=\""._webpath_."/portal/pics/cache/graph4.png\" />\n";
        
        echo "\tstacked bar chart...\n";
        $objGraph = new class_graph_pchart();
        $objGraph->setBitRoundedCorners(true);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        
        $objGraph->addStackedBarChartSet(array(8,-5,7,8,4,12), "serie 1");
        $objGraph->addStackedBarChartSet(array(3,-4,6,2,5,2 ), "serie 2");
        
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));
        $objGraph->saveGraph(_images_cachepath_."/graph3.png");
        echo "\t <img src=\""._webpath_."/portal/pics/cache/graph3.png\" />\n";
        
        echo "\tbar chart...\n";
        $objGraph = new class_graph_pchart();
        $objGraph->setArrColorPalette(class_graph_colorpalettes::$arrBlueColorPalette);
        $objGraph->setBitRoundedCorners(true);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Bar Chart");
        
        $objGraph->addBarChartSet(array(8,5,7,8,4,12), "serie 1");
        $objGraph->addBarChartSet(array(3,4,-6,2,5,2 ), "serie 2");
        
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));
        
        $objGraph->saveGraph(_images_cachepath_."/graph2.png");
        echo "\t <img src=\""._webpath_."/portal/pics/cache/graph2.png\" />\n";

        echo "\tbar chart variation...\n";
        $objGraph = new class_graph_pchart();
        $objGraph->setArrColorPalette(class_graph_colorpalettes::$arrBlueColorPalette);
        $objGraph->setBitRoundedCorners(true);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Bar Chart");

        $objGraph->addBarChartSet(array(8,0,0,0), "serie 1");
        $objGraph->addBarChartSet(array(0,4,0,0), "serie 2");
        $objGraph->addBarChartSet(array(0,0,6,0), "serie 3");
        $objGraph->addBarChartSet(array(0,0,0,2), "serie 4");

        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));

        $objGraph->saveGraph(_images_cachepath_."/graph2b.png");
        echo "\t <img src=\""._webpath_."/portal/pics/cache/graph2b.png\" />\n";

        echo "\tline chart...\n";
        $objGraph = new class_graph_pchart();
        $objGraph->setBitRoundedCorners(true);
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Line Chart");
        
        $objGraph->addLinePlot(array(8,5,7,8,4,12,10,11,9), "serie 1");
        $objGraph->addLinePlot(array(3,4,6,2,5,2 ,5, 3, 4), "serie 2");
        
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9"));
        $objGraph->saveGraph(_images_cachepath_."/graph1.png");
        echo "\t <img src=\""._webpath_."/portal/pics/cache/graph1.png\" />\n";
        
        
        


        

        
    }



    
}

?>