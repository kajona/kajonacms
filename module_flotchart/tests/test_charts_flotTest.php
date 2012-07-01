<?php
require_once (__DIR__ . "/../../module_system/system/class_testbase.php");

class class_test_charts_flotTest extends class_testbase  {

    public function testCharts() {


        srand((double)microtime()*1000000);
        //--- system kernel -------------------------------------------------------------------------------------
        echo "\tcreating a few charts...\n";
        
        
        //JS-Imports
        echo "
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.js\"></script>
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.flot.js\"></script>
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.flot.pie.js\"></script>
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.flot.stack.js\"></script>    
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/flot_examples.js\"></script>
        <script language=\"javascript\" type=\"text/javascript\" src=\""._webpath_."/core/module_flotchart/admin/scripts/js/flot/jquery.flot.axislabels.js\"></script>
        ";
        
        
        //CSS-Stylesheets
        echo "
            <style type=\"text/css\">
            * {
              font-family: sans-serif;
            }

            body
            {
                padding: 0 1em 1em 1em;
            }

            div.graph
            {
                width: 800;
                height: 400;
                #float: left;
                border: 1px dashed gainsboro;
            }

            h2
            {
                padding-top: 1em;
                margin-bottom: 0;
                clear: both;
                color: #ccc;
            }

        </style>
        ";
        
        /*$objGraph = new class_graph_flot();
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addLinePlot(array(4,7,1,2), "serie 3");
        $objGraph->addLinePlot(array(4,3,2,1), "serie 4");
        echo $objGraph->showGraph();
        
        $objGraph = new class_graph_flot();
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 1");
        echo $objGraph->showGraph();*/
        
        $objGraph = new class_graph_flot();
        $objGraph->addLinePlot(array(8,1,2,4), "serie 1");
        $objGraph->setStrXAxisTitle("X-Axis");
        $objGraph->setStrYAxisTitle("Y-Axis");
        $objGraph->addLinePlot(array(1,2,3,4), "serie 2");
        $objGraph->addBarChartSet(array(1,2,3,4), "serie 3");
        //$objGraph->addBarChartSet(array(1,2,3,4), "serie 4");
        //$objGraph->createPieChart(array(1,2,3,4), array("val 1", "val 2", "val 3", "val 4"));
        echo $objGraph->showGraph();
        
        //$objGraph = new class_graph_flot();
        //echo $objGraph->showGraph();
        
        
        echo"<br/>";
    }
}

