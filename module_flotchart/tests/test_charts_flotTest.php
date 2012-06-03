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
                width: 400;
                height: 150;
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
        
        
        //<DIV>-Containers for the charts
        echo "<h2>Pie Chart</h2>";
        echo "\t <div id=\"piechart\" class=\"graph\" \"></div>";
        
        echo "<h2>Pie Chart with legend</h2>";
        echo "\t <div id=\"piechart_legend\" class=\"graph\" \"></div>";
        
        echo "<h2>Bar Chart</h2>";
        echo "\t <div id=\"barchart\" class=\"graph\" style=\"width:600px\" \"></div>";
        
        echo "<h2>Stacked Bar Chart</h2>";
        echo "\t <div id=\"stackedbarchart\" class=\"graph\" style=\"width:600px\" \"></div>";
        
        echo "<h2>Line Chart</h2>";
        echo "\t <div id=\"linechart\" class=\"graph\" style=\"width:600px\" \"></div>";
        
        echo "<h2>Combined Line/Bar Chart</h2>";
        echo "\t <div id=\"linebarchart\" class=\"graph\" style=\"width:600px\" \"></div>";
        
        
        //Plot the charts when document is ready
        echo"
        <script type=\"text/javascript\">
            $(document).ready(function(){
                $(plotCharts);
            })
        </script>
        ";
        
        echo"<br/>";
    }
}

