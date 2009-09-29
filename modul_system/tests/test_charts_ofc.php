<?php

include_once(_systempath_."/class_modul_system_common.php");

class class_test_charts_ofc implements interface_testable {



    public function test() {

        
        $this->testCharts();

    }



    private function testCharts() {

        echo "<script type=\"text/javascript\" src=\""._webpath_."/admin/scripts/yui/yuiloader-dom-event/yuiloader-dom-event.js\"></script>";
        echo "<script type=\"text/javascript\" src=\""._webpath_."/admin/scripts/kajona.js\"></script>";
        echo "<script type=\"text/javascript\" >var KAJONA_WEBPATH = '"._webpath_."';</script>";


        include_once(_systempath_."/class_graph_ofc.php");
        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";

        echo "\tbar chart...\n";
        $objChart = new class_graph_ofc();
        $objChart->setStrGraphTitle("Test Bar Chart");
        $objChart->setStrXAxisTitle("X Axis Title");
        $objChart->setStrYAxisTitle("Y Axis Title");
        
        $objChart->addBarChartSet(array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)), "Set 1");
        $objChart->setArrXAxisTickLabels(array("val1", "val2", "val3", "val4"));
        echo $objChart->getCompleteJsAndHtmlCode();
        echo "\n";


        echo "\tgrouped bar chart...\n";
        $objChart = new class_graph_ofc();
        $objChart->setArrColorPalette(class_graph_colorpalettes::$arrBlueColorPalette);
        $objChart->setStrGraphTitle("Test Grouped Bar Chart");
        $objChart->setStrXAxisTitle("X Axis Title");
        $objChart->setStrYAxisTitle("Y Axis Title");

        $objChart->addBarChartSet(array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)), "Set 1");
        $objChart->addBarChartSet(array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)), "Set 2");
        $objChart->addBarChartSet(array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)), "Set 3");
        $objChart->setArrXAxisTickLabels(array("val1", "val2", "val3", "val4"));
        echo $objChart->getCompleteJsAndHtmlCode();
        echo "\n";

        echo "\tstacked bar chart...\n";
        $objChart = new class_graph_ofc();
        $objChart->setStrGraphTitle("Test Grouped Bar Chart");
        $objChart->setStrXAxisTitle("X Axis Title");
        $objChart->setStrYAxisTitle("Y Axis Title");

        $objChart->addStackedBarChartSet(array(
                                               array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)),
                                               array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)),
                                               array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20))  ));
        $objChart->setArrXAxisTickLabels(array("val1", "val2", "val3"));
        echo $objChart->getCompleteJsAndHtmlCode();
        echo "\n";

        echo "\tline chart...\n";
        $objChart = new class_graph_ofc();
        $objChart->setStrGraphTitle("Test Line Bar Chart");
        $objChart->setStrXAxisTitle("X Axis Title");
        $objChart->setStrYAxisTitle("Y Axis Title");

        $objChart->addLinePlot(array(rand(11, 20), rand(11, 20), rand(11, 20), rand(11, 20), rand(11, 20), rand(11, 20), rand(11, 20)), "Set 1");
        $objChart->addLinePlot(array(rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10), rand(0, 10)), "Set 2");
        $objChart->addLinePlot(array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)), "Set 3");
        $objChart->setArrXAxisTickLabels(array("val1", "val2", "val3", "val4", "5", "asd", "sd"));
        echo $objChart->getCompleteJsAndHtmlCode();
        echo "\n";

        echo "\tpie chart...\n";
        $objChart = new class_graph_ofc();
        $objChart->setStrGraphTitle("Test Pie Chart");

        $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
        echo $objChart->getCompleteJsAndHtmlCode();
        echo "\n";

        
    }



    
}

?>