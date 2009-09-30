<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_graph.php 2806 2009-06-20 19:54:33Z sidler $                                             *
********************************************************************************************************/

require_once(_systempath_."/OFC/OFC_Chart.php");

require_once(_systempath_."/class_graph_colorpalettes.php");

/**
 * This class could be used to create graphs based on the Open Flash Chart API.
 * This class can be used to create the js-code / JSON-code needed to render the chart.
 *
 * 
 * Those can present datasets using different types of graphs as bar-charts, pie-charts or
 * xy-graphs
 *
 * @package modul_system
 * @since 3.3.0
 * @author sidler
 */
class class_graph_ofc {

	private $strXAxisTitle = "";
    private $strXAxisStyle = "color: #000000; font-size: 11px;";
	private $strYAxisTitle = "";
    private $strYAxisStyle = "colour: #000000; font-size: 11px;";
	private $strGraphTitle = "";

    private $intWidth = 500;
    private $intHeight = 250;

    private $arrXAxisTickLabels = array();

    private $strBackgroundColour = "#EFEFEF";
    private $strGridColour = "#CCCCCC";
    private $strAxisColour = "#000000";

	//---------------------------------------------------------------------------------------------------
	//   The following values are used to seperate the graph-modes, because not all
	//   methods are allowed with every chart-type

	private $GRAPH_TYPE_BAR = 0;
    private $GRAPH_TYPE_STACKEDBAR = 4;
	private $GRAPH_TYPE_LINE = 1;
	private $GRAPH_TYPE_PIE = 2;

	//---------------------------------------------------------------------------------------------------

	private $intCurrentGraphMode = -1;

    private $arrElementsToAdd = array();
    private $strInternalIdentifier = "";

    private $intMaxYValue = 0;
    private $intMinYValue = 0;
    
    private $intColorCounter = 0;
    private $arrDefaultColorPalette = array();
    
    private static $bitSwfObjIncluded = false;


	/**
	 * Contructor
	 *
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_graph_ofc";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

        $this->strInternalIdentifier = generateSystemid();
        $this->arrDefaultColorPalette = class_graph_colorpalettes::$arrDefaultColorPalette;
	}


    /**
	 * Used to create a bar-chart.
     * For each set of bar-values you can call this method once.
     * This means, calling this method twice creates a grouped bar chart
	 * A sample-code could be:
	 *
	 *  $objGraph = new class_graph();
	 *  $objGraph->setStrXAxisTitle("x-axis");
	 *  $objGraph->setStrYAxisTitle("y-axis");
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->addBarChartSet(array(1,2,4,5), "serie 1");
	 *
	 * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param int $intLegendSize
	 */
	public function addBarChartSet($arrValues, $strLegend, $intLegendSize = 12) {
        if(!$this->intCurrentGraphMode < 0) {
            //only allow this method to be called again if in bar-mode
            if(!$this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
                
        }

		$this->intCurrentGraphMode = $this->GRAPH_TYPE_BAR;


        //$objBar = new OFC_Charts_Bar();
        $objBar = new OFC_Charts_Bar_Glass();
        
        $objBar->set_values(array_values($arrValues));
        $objBar->set_colour($this->arrDefaultColorPalette[$this->intColorCounter++]);

        if($strLegend != "")
            $objBar->set_key($strLegend, $intLegendSize);


        //prepare for a proper y-labeling
        $this->calcMinMaxYValues($arrValues);


		$this->arrElementsToAdd[] = $objBar;
	}


    /**
     * Creates a stacked bar.
     * A sample code could be:
     *
     *   $objChart = new class_graph_ofc();
     *   $objChart->setStrGraphTitle("Test Grouped Bar Chart");
     *   $objChart->setStrXAxisTitle("X Axis Title");
     *   $objChart->setStrYAxisTitle("Y Axis Title");
     *   $objChart->addStackedBarChartSet(array(
     *                                          array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)),
     *                                          array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20)),
     *                                          array(rand(0, 20), rand(0, 20), rand(0, 20), rand(0, 20))  )
     *                                           );
     *   $objChart->setArrXAxisTickLabels(array("val1", "val2", "val3"));
     *
     * @param array $arrStacks
     */
    public function addStackedBarChartSet($arrStacks) {
        if(!$this->intCurrentGraphMode < 0) {
            //only allow this method to be called again if in stackedbar-mode
            if(!$this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

		$this->intCurrentGraphMode = $this->GRAPH_TYPE_STACKEDBAR;
        $intSums = array();

        $objStack = new OFC_Charts_Bar_Stack();
        foreach($arrStacks as $intStackKey => $arrSingleStack) {
            $arrSingleStackEntries = array();

            foreach($arrSingleStack as $intKey => $intSingleValue) {
                $arrSingleStackEntries[] = new OFC_Charts_Bar_Stack_Value($intSingleValue, $this->arrDefaultColorPalette[$intKey] );;

                if(isset($intSums[$intStackKey]))
                    $intSums[$intStackKey] += $intSingleValue;
                else
                    $intSums[$intStackKey] = $intSingleValue;

            }

            $objStack->append_stack($arrSingleStackEntries);
            $objStack->tip = '#val# / #total#';
        }

        if(max($intSums) > $this->intMaxYValue)
            $this->intMaxYValue = max($intSums);

		$this->arrElementsToAdd[] = $objStack;
	}

    /**
     * Registers a new plot to the current graph. Works in line-plot-mode only.
     * Add a set tof linePlot to a graph to get more then one line.
     * A sample-code could be:
     *
     *  $objGraph = new class_graph();
	 *  $objGraph->setStrXAxisTitle("x-axis");
	 *  $objGraph->setStrYAxisTitle("y-axis");
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * @param array $arrValues e.g. array(1,3,4,5,6)
     * @param string $strLegend
     * @param int $intLegendSize
     */
    public function addLinePlot($arrValues, $strLegend, $intLegendSize = 12) {
        if(!$this->intCurrentGraphMode < 0) {
            //only allow this method to be called again if in line-mode
            if(!$this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

        $this->intCurrentGraphMode = $this->GRAPH_TYPE_LINE;


        $objLine = new OFC_Charts_Line();

        $objLine->set_values(array_values($arrValues));
        $objLine->set_colour($this->arrDefaultColorPalette[$this->intColorCounter++]);

        if($strLegend != "")
            $objLine->set_key($strLegend, $intLegendSize);


        //set the tick-labels
        $this->arrXAxisTickLabels = array_keys($arrValues);

        //prepare for a proper y-labeling
        $this->calcMinMaxYValues($arrValues);

		$this->arrElementsToAdd[] = $objLine;

    }

    /**
     * Creates a new pie-chart. Pass the values as the first param. If
     * you want to use a legend and / or colours use the second and third param.
     * Make sure the array have the same number of elements, ohterwise they won't
     * be uses.
     * A sample-code could be:
     *
     *  $objChart = new class_graph_ofc();
     *  $objChart->setStrGraphTitle("Test Pie Chart");
     *  $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
     *
     * @param array $arrValues
     * @param array $arrLegends
     */
    public function createPieChart($arrValues, $arrLegends) {
        if(!$this->intCurrentGraphMode < 0) {
            throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

        $this->intCurrentGraphMode = $this->GRAPH_TYPE_PIE;

        $objPie = new OFC_Charts_Pie();
        $objPie->set_start_angle( 35 );
        $objPie->set_animate( true );

        //build the values
        $arrValuesToAdd = array();
        foreach($arrValues as $intKey => $intSingleValue) {
            if(count($arrLegends) == count($arrValues)) {
                $objValue = new OFC_Charts_Pie_Value($intSingleValue, $arrLegends[$intKey]);
                $objValue->label = $arrLegends[$intKey];
                $arrValuesToAdd[] = $objValue;
            }
            else
                $arrValuesToAdd[] = $intSingleValue;
        }


        $objPie->values = $arrValuesToAdd;
        $objPie->tip = '#val# / #total#<br>#percent# / 100%' ;

        $intNrOfParts = count($arrValues);
        $arrColors = array_slice($this->arrDefaultColorPalette, 0, $intNrOfParts);
        $objPie->colours = $arrColors;

        
        $this->arrElementsToAdd[] = $objPie;


    }

    /**
     * Calculates the max and min values of the passed array
     * in order to set up the y-value
     *
     * @param array $arrValues
     */
    private function calcMinMaxYValues($arrValues) {
        
        $intMaxValue = max($arrValues);
        if($intMaxValue > $this->intMaxYValue)
            $this->intMaxYValue = $intMaxValue;

        $intMinValue = min($arrValues);
        if($intMinValue < $this->intMinYValue)
            $this->intMinYValue = $intMinValue;

        $this->intMaxYValue = ceil($this->intMaxYValue);
        while($this->intMaxYValue % 10 != 0)
            $this->intMaxYValue++;
            
        $this->intMinYValue = floor($this->intMinYValue);
    }
	

    /**
     * Does all the magic, "renderes" the graph an creates the json-code used by the applet.
     *
     * @return string
     */
    public function createJSONCode() {

        //does all the magic
        $objChart = new OFC_Chart();

        if($this->strGraphTitle != "") {
            $objTitle = new OFC_Elements_Title($this->strGraphTitle);
            $objChart->set_title($objTitle);
        }

        $objChart->set_bg_colour($this->strBackgroundColour);

        //add the elements
        foreach($this->arrElementsToAdd as $objOneElement)
            $objChart->add_element($objOneElement);


        //if bar or line chart, additional infos to set up
        if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR ||
           $this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR ||
		   $this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE  ) {


            //set up the tick-labels
            $objXAxis = new OFC_Elements_Axis_X();
            $objXAxis->set_labels_from_array($this->arrXAxisTickLabels);
            $objChart->set_x_axis($objXAxis);

            //axis legends
            if($this->strXAxisTitle != "") {
                $objXLegend = new OFC_Elements_Legend_X($this->strXAxisTitle);
                $objXLegend->set_style($this->strXAxisStyle);
                $objChart->set_x_legend($objXLegend);
                
            }

            if($this->strYAxisTitle != "") {
                $objYLegend = new OFC_Elements_Legend_Y($this->strYAxisTitle);
                $objYLegend->set_style($this->strYAxisStyle);
                $objChart->set_y_legend($objYLegend);


            }

            //set up the grid colour
            $objXGrid = new OFC_Elements_Axis_X();
            $objXGrid->set_colours($this->strAxisColour, $this->strGridColour);
            $objXGrid->set_labels_from_array($this->arrXAxisTickLabels);
            $objChart->set_x_axis($objXGrid);


            $objYGrid = new OFC_Elements_Axis_Y();
            $objYGrid->set_colours($this->strAxisColour, $this->strGridColour);

            //calc the nr of steps
            $intSteps = ($this->intMaxYValue - $this->intMinYValue) / 10;

            $objYGrid->set_range($this->intMinYValue, $this->intMaxYValue, $intSteps);
            $objChart->set_y_axis($objYGrid);

        }

        return $objChart->toString();

    }

    /**
     * Creates all js and html-code needed to create the swf.
     *
     * @return string
     */
    public function getCompleteJsAndHtmlCode() {
        $strReturn = "";
        
        //semi-lazy-loading, swfobject doesn't support to be loaded dynamically
        if(!self::$bitSwfObjIncluded) {
            $strReturn .= "<script type=\"text/javascript\" src=\""._webpath_."/admin/scripts/ofc/js/swfobject.js\"></script>\n";
            self::$bitSwfObjIncluded = true;
        }
        $strReturn .= "<script type=\"text/javascript\"> \n";
        $strReturn .= "  var l_".$this->strInternalIdentifier." = new kajonaAjaxHelper.Loader();\n";
        $strReturn .= "  l_".$this->strInternalIdentifier.".addJavascriptFile(l_".$this->strInternalIdentifier.".jsBase +\"ofc/js/json/json2.js\");\n";
        //$strReturn .= "  l_".$this->strInternalIdentifier.".addJavascriptFile(l_".$this->strInternalIdentifier.".jsBase +\"ofc/js/swfobject.js\");\n";
        $strReturn .= "  l_".$this->strInternalIdentifier.".load( function(){ \n";
        $strReturn .= "    swfobject.embedSWF(\""._webpath_."/admin/scripts/ofc/open-flash-chart.swf\", \"chart_".$this->strInternalIdentifier."\", \"".$this->intWidth."\", \"".$this->intHeight."\", \"9.0.0\", \"expressInstall.swf\",  {\"get-data\":\"getData_".$this->strInternalIdentifier."\", \"loading\":\"Loading data...\"} );\n";
        $strReturn .= "   } );\n";
        
        $strReturn .= "  var data_".$this->strInternalIdentifier." = ".$this->createJSONCode().";  \n";
        $strReturn .= "  function getData_".$this->strInternalIdentifier."() { \n";
        $strReturn .= "     return JSON.stringify(data_".$this->strInternalIdentifier."); \n";
        $strReturn .= "  } \n";
         
        $strReturn .= "</script>\n";
        $strReturn .= "<div id=\"chart_".$this->strInternalIdentifier."\"></div>";

        return $strReturn;
    }

	/**
	 * Set the title of the x-axis
	 *
	 * @param string $strTitle
	 */
	public function setStrXAxisTitle($strTitle) {
		$this->strXAxisTitle = $strTitle;
	}

	/**
	 * Set the title of the y-axis
	 *
	 * @param string $strTitle
	 */
	public function setStrYAxisTitle($strTitle) {
		$this->strYAxisTitle = $strTitle;
	}

	/**
	 * Set the title of the graph
	 *
	 * @param string $strTitle
	 */
	public function setStrGraphTitle($strTitle) {
		$this->strGraphTitle = $strTitle;
	}

	/**
	 * Used to set an array of x-axis labels.
	 *
	 * @param array $arrLabels
	 */
	public function setXAxisTickLabels($arrLabels) {
        $this->arrXAxisTickLabels = $arrLabels;
	}

    /**
     * Set the coloor of the margin-areas, so the coloor of the area not being
     * the plot-area.
     * In most cases this is the background.
     *
     * @param string $strColour in hex-values: #ccddee 
     */
    public function setBackgroundColour($strColour) {
        $this->strBackgroundColour = $strColour;
    }

    /**
     * Set the style of the axis-label in css-commands
     *
     * @param string $strXAxisStyle css-code
     */
    public function setStrXAxisStyle($strXAxisStyle) {
        $this->strXAxisStyle = $strXAxisStyle;
    }

    /**
     * Set the style of the axis-label in css-commands
     *
     * @param string $strYAxisStyle css-code
     */
    public function setStrYAxisStyle($strYAxisStyle) {
        $this->strYAxisStyle = $strYAxisStyle;
    }

    /**
     * Set the total width of the embedded flash-applet
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    /**
     * Set the total height of the embedded flash-applet
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setStrGridColour($strGridColour) {
        $this->strGridColour = $strGridColour;
    }

    
    /**
     * Set the labels to be used for the x-axis
     *
     * @param array $arrXAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of x-axis labels to be printed
     */
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $strSerieName = generateSystemid();
        
        if(count($arrXAxisTickLabels) > $intNrOfWrittenLabels) {
            //not more than 12 labels
            $intCounter = ceil( count($arrXAxisTickLabels) / $intNrOfWrittenLabels);
            $arrMadeUpLabels = array();
            $intKeyCount = 0;
            foreach($arrXAxisTickLabels as $strOneLabel) {
                 if(++$intKeyCount % $intCounter == 1)
                     $arrMadeUpLabels[] = $strOneLabel;
                 else
                     $arrMadeUpLabels[] = "";
            }
        }
        else
            $arrMadeUpLabels = $arrXAxisTickLabels;
        
        
        $this->arrXAxisTickLabels = $arrMadeUpLabels;
    }
    
    /**
     * Set the array of colors to be used within the charts
     * 
     * @param $arrColorPalette
     */
    public function setArrColorPalette($arrColorPalette) {
        $this->arrDefaultColorPalette = $arrColorPalette;
    }

}

?>