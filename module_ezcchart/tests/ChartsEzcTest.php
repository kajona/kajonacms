<?php

namespace Kajona\Ezcchart\Tests;

use Kajona\System\Tests\Testbase;

class ChartsEzcTest extends Testbase  {

    public function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------


        echo "\t pie chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrGraphTitle("Test Pie Chart");
        $objGraph->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->saveGraph(_images_cachepath_."/graph4.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph4.png");
        echo $objGraph->renderGraph();

        echo "\t pie chart without legend...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrGraphTitle("Test Pie Chart without legend");
        $objGraph->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
        $objGraph->setBitRenderLegend(false);
        $objGraph->saveGraph(_images_cachepath_."/graph4_wl.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph4_wl.png");
        echo $objGraph->renderGraph();

        echo "\tbar chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Bar Chart");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));
        $objGraph->addBarChartSet(array(8,5,7,8,4,12), "serie 1");
        $objGraph->addBarChartSet(array(3,4,-6,2,5,2 ), "serie 2", true);


        $objGraph->saveGraph(_images_cachepath_."/graph2.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph2.png");
        echo $objGraph->renderGraph();

        echo "\tstacked bar chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Stacked Bar Chart");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));

        $objGraph->addStackedBarChartSet(array(8,-5,7,8,4,12), "serie 1");
        $objGraph->addStackedBarChartSet(array(3,-4,6,2,5,2 ), "serie 2");

        $objGraph->saveGraph(_images_cachepath_."/graph3.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph3.png");
        echo $objGraph->renderGraph();



        echo "\tbar chart variation...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Bar Chart with 0 values");
        $objGraph->setBit3d(false);
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4"));

        $objGraph->addBarChartSet(array(8,0,0,0), "serie 1");
        $objGraph->addBarChartSet(array(0,4,0,0), "serie 2");
        $objGraph->addBarChartSet(array(0,0,6,0), "serie 3");
        $objGraph->addBarChartSet(array(0,0,0,2), "serie 4");


        $objGraph->saveGraph(_images_cachepath_."/graph2b.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph2b.png");
        echo $objGraph->renderGraph();

        echo "\tline chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Line Chart");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9"));

        $objGraph->addLinePlot(array(8,5,7,8,4,12,10,11,9), "serie 1");
        $objGraph->addLinePlot(array(3,4,6,2,5,2 ,5, 3, 4), "serie 2");

        $objGraph->saveGraph(_images_cachepath_."/graph1.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph1.png");
        echo $objGraph->renderGraph();


        echo "\tcombined line / bar chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test combined Bar /  Line Chart");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6", "v7", "v8", "v9"));
        $objGraph->setIntXAxisAngle(45);
        $objGraph->setBit3d(false);

        $objGraph->addBarChartSet(array(4,5,7,8,5,14,13,12,3), "bar");
        $objGraph->addLinePlot(array(8,5,7,8,4,12,10,11,9), "serie 1");
        $objGraph->addLinePlot(array(3,4,6,2,5,2 ,5, 3, 4), "serie 2");

        $objGraph->saveGraph(_images_cachepath_."/graph6.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph6.png");
        echo $objGraph->renderGraph();


        echo "\thorizontal bar chart...\n";
        $objGraph = new \Kajona\Ezcchart\System\GraphEzc();
        $objGraph->setStrXAxisTitle("x-axis");
        $objGraph->setStrYAxisTitle("y-axis");
        $objGraph->setStrGraphTitle("Test Horizontal Bar Chart");
        $objGraph->setArrXAxisTickLabels(array("v1", "v2", "v3", "v4", "v5", "v6"));
        $objGraph->addBarChartSet(array(8,5,7,8,4,12), "serie 1");
        $objGraph->addBarChartSet(array(3,4,-6,2,5,2 ), "serie 2", true);
        $objGraph->setBarHorizontal(true);
        $objGraph->setIntXAxisAngle(45);
        $objGraph->saveGraph(_images_cachepath_."/graph7.png");
        $this->assertFileExists(_realpath_._images_cachepath_."/graph7.png");
        echo $objGraph->renderGraph();
    }




}

