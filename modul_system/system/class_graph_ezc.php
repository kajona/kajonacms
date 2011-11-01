<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

require_once(_systempath_."/ezcomponents/Base/src/base.php");
spl_autoload_register( array( 'ezcBase', 'autoload' ) );



/**
 * This class could be used to create graphs based on the ez components API.
 * ezc renders charts on the serverside and passes them back as images, including full support
 * of SVG images.
 *
 * @package modul_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
class class_graph_ezc implements interface_graph {


	private $strXAxisTitle = "";
	private $strYAxisTitle = "";
	private $strGraphTitle = "";

    private $intWidth = 720;
    private $intHeight = 200;

    private $strBackgroundColor = "#FFFFFF";
    private $strTitleBackgroundColor = "#FFFFFF";
    private $strFontColor = "#6F6F6F";
    private $strTitleFontColor = "#000000";

    private $bitRenderLegend = true;
    private $bitLegendPositionBottom = false;
    private $strFont = "/fonts/dejavusans.ttf";

    private $intXAxisAngle = 0;
    private $arrXAxisLabels = array();
    private $intMaxLabelCount = 12;

    private $bit3d = null;

    private $intMaxValue = 0;
    private $intMinValue = 0;



	//---------------------------------------------------------------------------------------------------
	//   The following values are used to seperate the graph-modes, because not all
	//   methods are allowed with every chart-type

	private $GRAPH_TYPE_BAR = 1;
    private $GRAPH_TYPE_STACKEDBAR = 4;
	private $GRAPH_TYPE_LINE = 2;
	private $GRAPH_TYPE_PIE = 3;

    private $intCurrentGraphMode = -1;

	//---------------------------------------------------------------------------------------------------

    /**
     *
     * @var ezcGraphChart
     */
    private $objGraph = null;

    /**
     *
     * @var array
     */
    private $arrDataSets = array();


	/**
	 * Contructor
	 *
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_graph";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;

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
	 *  $objGraph->addBarChartSet(array(1,2,4,5) "serie 1");
	 *
	 * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
	 */
	public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        if($this->intCurrentGraphMode > 0) {
            //only allow this method to be called again if in bar-mode
            if($this->intCurrentGraphMode != $this->GRAPH_TYPE_BAR && $this->intCurrentGraphMode != $this->GRAPH_TYPE_STACKEDBAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

		$this->intCurrentGraphMode = $this->GRAPH_TYPE_BAR;

        $arrEntries = array();
        $intCounter = 0;
        foreach($arrValues as $intKey => $strValue) {
            $arrEntries[$this->getArrXAxisEntry($intCounter)] = $strValue;

            if($strValue > $this->intMaxValue && $this->intCurrentGraphMode != $this->GRAPH_TYPE_STACKEDBAR)
                $this->intMaxValue = $strValue;

            if($strValue < $this->intMinValue && $this->intCurrentGraphMode != $this->GRAPH_TYPE_STACKEDBAR)
                $this->intMinValue = $strValue;

            $intCounter++;
        }

        $this->arrDataSets[$strLegend] = array("data" => new ezcGraphArrayDataSet($arrEntries));
        if($bitWriteValues)
            $this->arrDataSets[$strLegend]["data"]->highlight = true;

	}


    /**
	 * Used to create a stacked bar-chart.
     * For each set of bar-values you can call this method once.
	 * A sample-code could be:
	 *
	 *  $objGraph = new class_graph();
	 *  $objGraph->setStrXAxisTitle("x-axis");
	 *  $objGraph->setStrYAxisTitle("y-axis");
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 1");
	 *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 2");
	 *
	 * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
	 */
    public function addStackedBarChartSet($arrValues, $strLegend) {
        if($this->intCurrentGraphMode > 0) {
            //only allow this method to be called again if in stackedbar-mode
            if($this->intCurrentGraphMode != $this->GRAPH_TYPE_STACKEDBAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

        //add max value from each set to max value
        $intMax = 0;
        $intMin = 0;
        foreach($arrValues as $intKey => $strValue) {
            if($strValue > $intMax)
                $intMax = $strValue;

            if($strValue < $intMin)
                $intMin = $strValue;
        }

        $this->intMaxValue += $intMax;
        $this->intMinValue -= $intMin;

        $this->intCurrentGraphMode = $this->GRAPH_TYPE_STACKEDBAR;
        $this->addBarChartSet($arrValues, $strLegend);
        $this->intCurrentGraphMode = $this->GRAPH_TYPE_STACKEDBAR;

	}


    /**
     * Registers a new plot to the current graph. Works in line-plot-mode only.
     * Add a set of linePlot to a graph to get more then one line.
     *
     * If you created a bar-chart before, it it is possible to add line-plots on top of
     * the bars. Nevertheless, the scale is calculated out of the bars, so make
     * sure to remain inside the visible range!
     *
     * A sample-code could be:
     *
     *  $objGraph = new class_graph();
	 *  $objGraph->setStrXAxisTitle("x-axis");
	 *  $objGraph->setStrYAxisTitle("y-axis");
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * @param array $arrValues e.g. array(1,3,4,5,6)
     * @param string $strLegend the name of the single plot
     */
    public function addLinePlot($arrValues, $strLegend) {
        if($this->intCurrentGraphMode > 0) {
            //in bar mode, its ok. just place on top
            if($this->intCurrentGraphMode != $this->GRAPH_TYPE_LINE && $this->intCurrentGraphMode != $this->GRAPH_TYPE_BAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }


        if($this->intCurrentGraphMode < 0)
            $this->intCurrentGraphMode = $this->GRAPH_TYPE_LINE;

        $arrEntries = array();
        $intCounter = 0;
        foreach($arrValues as $strValue) {
            $strAxisLabel = $this->getArrXAxisEntry($intCounter);
            if($strAxisLabel != "")
                $arrEntries[$strAxisLabel] = $strValue;
            else
                $arrEntries[$intCounter+1] = $strValue;

            if($strValue < 0)
                $strValue *= -2;

            if($strValue > $this->intMaxValue)
                $this->intMaxValue = $strValue;

            if($strValue < $this->intMinValue)
                $this->intMinValue = $strValue;

            $intCounter++;
        }

        $this->arrDataSets[$strLegend] = array("data" => new ezcGraphArrayDataSet($arrEntries), "symbol" => ezcGraph::BULLET, "displayType" => ezcGraph::LINE);

        //enables the rendering of values
        //$this->arrDataSets[$strLegend]["data"]->highlight = true;
    }

    /**
     * Creates a new pie-chart. Pass the values as the first param. If
     * you want to use a legend and / or Colors use the second and third param.
     * Make sure the array have the same number of elements, ohterwise they won't
     * be uses.
     * A sample-code could be:
     *
     *  $objChart = new class_graph();
     *  $objChart->setStrGraphTitle("Test Pie Chart");
     *  $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
     *
     * @param array $arrValues
     * @param array $arrLegends
     */
    public function createPieChart($arrValues, $arrLegends) {
        if($this->intCurrentGraphMode > 0) {
            throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

        $this->intCurrentGraphMode = $this->GRAPH_TYPE_PIE;

        $arrEntries = array();
        foreach($arrValues as $intKey => $strValue)
            $arrEntries[$arrLegends[$intKey]] = $strValue;

        $objData = new ezcGraphArrayDataSet($arrEntries);
        $objData->highlight = true;

        $this->arrDataSets[generateSystemid().""] = array("data" => $objData);
    }



    /**
     * Creates the object and prepares it for rendering.
     * Does all the calculation like borders, margins, paddings ....
     *
     * @return void
     */
    private function preGraphCreation() {

        $objPalette = new ezcGraphPaletteKajona();

        //Initialize the graph-object depending on the type
        if($this->intCurrentGraphMode == $this->GRAPH_TYPE_PIE) {
            $this->objGraph = new ezcGraphPieChart();

            if($this->bit3d === null || $this->bit3d === true)
                $this->objGraph->renderer = new ezcGraphRenderer3d();
            else
                $this->objGraph->renderer = new ezcGraphRenderer2d();

            $this->objGraph->palette = $objPalette;

            //layouting
            if($this->bit3d === null || $this->bit3d === true) {
                $this->objGraph->renderer->options->pieChartGleam = .5;
                $this->objGraph->renderer->options->pieChartGleamColor = '#FFFFFF';
                $this->objGraph->renderer->options->pieChartGleamBorder = 2;
                $this->objGraph->renderer->options->pieChartRotation = .7;
                $this->objGraph->renderer->options->pieChartShadowSize = 10;
                $this->objGraph->renderer->options->pieChartShadowColor = '#000000';
                $this->objGraph->renderer->options->pieChartHeight = 15;
            }
            $this->objGraph->renderer->options->dataBorder = .0;
        }

        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR || $this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR ) {
            $this->objGraph = new ezcGraphBarChart();

            if($this->bit3d === null || $this->bit3d === true)
                $this->objGraph->renderer = new ezcGraphRenderer3d();
            else
                $this->objGraph->renderer = new ezcGraphRenderer2d();

            $this->objGraph->palette = $objPalette;

            if($this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR)
                $this->objGraph->options->stackBars = true;

            //layouting
            if($this->bit3d === null || $this->bit3d === true) {
                $this->objGraph->renderer->options->barChartGleam = .5;
                $this->objGraph->renderer->options->depth = .05;
            }
        }

        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE ) {
            $this->objGraph = new ezcGraphLineChart();
            if($this->bit3d === true)
                $this->objGraph->renderer = new ezcGraphRenderer3d();
            else
                $this->objGraph->renderer = new ezcGraphRenderer2d();

            $this->objGraph->palette = $objPalette;

            $this->objGraph->options->fillLines = 245;
            $this->objGraph->options->highlightLines = true;
            $this->objGraph->options->lineThickness = 3;
        }

        //data sets

        foreach($this->arrDataSets as $strName => $arrSet) {

            $this->objGraph->data[$strName] = $arrSet["data"];
            if(isset($arrSet["symbol"]))
                $this->objGraph->data[$strName]->symbol = $arrSet["symbol"];

            if(isset($arrSet["displayType"]))
                $this->objGraph->data[$strName]->displayType = $arrSet["displayType"];

        }


        if($this->objGraph == null)
            throw new class_exception("trying to render unitialized graph", class_exception::$level_FATALERROR);


        //set up params
        $this->objGraph->title = $this->strGraphTitle;

        //set the font properties
        $this->objGraph->options->font = _systempath_.$this->strFont;
        $this->objGraph->options->font->color = $this->strFontColor;
        $this->objGraph->options->font->maxFontSize = 9;

        //$this->objGraph->options->font->minFontSize = 5;
        if($this->strGraphTitle != "") {
            $this->objGraph->title->padding = 1;
            $this->objGraph->title->margin = 2;
            $this->objGraph->title->font->maxFontSize = 9;
            $this->objGraph->title->font->color = $this->strTitleFontColor;
            $this->objGraph->title->background = $this->strTitleBackgroundColor;
        }

        //colors
        $this->objGraph->background = $this->strBackgroundColor;
        $this->objGraph->background->padding = 5;

        if($this->bitRenderLegend === true) {
            //place the legend at the bottom by default
            if($this->bitLegendPositionBottom)
                $this->objGraph->legend->position = ezcGraph::BOTTOM;
            else
                $this->objGraph->legend->position = ezcGraph::RIGHT;

            $this->objGraph->legend->margin = 2;
            $this->objGraph->legend->padding = 1.5;

            //legend rendering
            $this->objGraph->renderer->options->legendSymbolGleam = .1;
            $this->objGraph->renderer->options->legendSymbolGleamSize = .9;
            $this->objGraph->renderer->options->legendSymbolGleamColor = '#FFFFFF';
        }
        else
            $this->objGraph->legend = false;

        //x-axis lables?
        if($this->intCurrentGraphMode != $this->GRAPH_TYPE_PIE) {

            if($this->intXAxisAngle != 0) {
                $this->objGraph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
                $this->objGraph->xAxis->axisLabelRenderer->angle = $this->intXAxisAngle;

                $this->objGraph->xAxis->axisSpace = .2;
            }

            if($this->intMaxLabelCount > 1)
                $this->objGraph->xAxis->labelCount = $this->intMaxLabelCount;


            $this->objGraph->xAxis->label = $this->strXAxisTitle;
            $this->objGraph->yAxis->label = $this->strYAxisTitle;

            //FIXME: removed due to rendering exceptions
//            if($this->strXAxisTitle != "")
//                $this->objGraph->xAxis->axisLabelRenderer->labelPadding = 4;
//
//            if($this->strYAxisTitle != "")
//                $this->objGraph->yAxis->axisLabelRenderer->labelPadding = 4;

            $intMaxValue = $this->intMaxValue;
            $intMinValue = $this->intMinValue;

            if($intMaxValue <= 0 && $intMinValue < 0)
                $this->objGraph->yAxis->max = 0;

            $intTotal = $intMaxValue;
            if($intMinValue < 0)
                $intTotal = $intMaxValue - $intMinValue;

            $intTotal = $this->getNextMaxPowValue($intTotal);

            if($intTotal != 0) {
                $this->objGraph->yAxis->majorStep = ceil($intTotal / 5);
                $this->objGraph->yAxis->minorStep = ceil($intTotal / 5)*0.5;
            }

        }




        //choose the renderer based on the extensions available
        if(extension_loaded("cairo"))
            $this->objGraph->driver = new ezcGraphCairoOODriver();
        else
            $this->objGraph->driver = new ezcGraphGdDriver();

        $this->objGraph->renderer->options->axisEndStyle = ezcGraph::NO_SYMBOL;

    }

    /**
	 * Does the magic. Creates all necessary stuff and finally
	 * sends the graph directly (!!!) to the browser.
     * Execution should be terminated afterwards.
	 *
	 */
	public function showGraph() {
		$this->preGraphCreation();
        $this->objGraph->renderToOutput($this->intWidth, $this->intHeight);
	}

	/**
	 * Does the magic. Creates all necessary stuff and finally
	 * saves the graph to the specified filename
	 *
	 */
	public function saveGraph($strFilename) {
		$this->preGraphCreation();

		if(strpos($strFilename, _realpath_) === false)
			$strFilename = _realpath_.$strFilename;

//        if(strtolower(substr($strFilename, -3) != "svg"))
//            throw new class_exception("Filename must be a svg-file", class_exception::$level_ERROR);

        $this->objGraph->render($this->intWidth, $this->intHeight, $strFilename);
	}

    /**
     * Calculates the next power to base 10 relative to the passed value
     *
     * @param float $floatSource
     * @return int
     */
    private function getNextMaxPowValue($floatSource) {
        return pow(10, strlen(ceil($floatSource)));
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
     * Set the color of the margin-areas, so the color of the area not being
     * the plot-area.
     * In most cases this is the background.
     *
     * @param string $strColor in hex-values: #ccddee
     */
    public function setStrBackgroundColor($strColor) {
        $this->strBackgroundColor = $strColor;
    }

    /**
     * Set the total width of the chart
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    /**
     * Set the total height of the chart
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    /**
     * Set the labels to be used for the x-axis.
     * Make sure to set them before adding datasets!
     *
     * @param array $arrXAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of x-axis labels to be printed
     */
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $arrMadeUpLabels = array();

//            if(count($arrXAxisTickLabels) > $intNrOfWrittenLabels) {
//                //not more than $intNrOfWrittenLabels labels
//                $intCounter = ceil( count($arrXAxisTickLabels) / $intNrOfWrittenLabels);
//                $arrMadeUpLabels = array();
//                $intKeyCount = 0;
//                foreach($arrXAxisTickLabels as $strOneLabel) {
//                     if(++$intKeyCount % $intCounter == 1)
//                         $arrMadeUpLabels[] = $strOneLabel;
//                     else
//                         $arrMadeUpLabels[] = "";
//                }
//            }
//            else
//                $arrMadeUpLabels = $arrXAxisTickLabels;
//         else
             $arrMadeUpLabels = $arrXAxisTickLabels;


        $this->arrXAxisLabels = $arrMadeUpLabels;
        $this->intMaxLabelCount = $intNrOfWrittenLabels;


    }

    /**
     * Returns the entry on the x-axis to be rendered
     * @param int $intPos
     * @return string
     */
    private function getArrXAxisEntry($intPos) {
        $intCount = 0;
        foreach($this->arrXAxisLabels as $strOneLabel) {
            if($intCount == $intPos)
                return $strOneLabel;

            $intCount++;
        }
        return "";
    }

    /**
     * Sets if to render a legend or not
     *
     * @param bool $bitRenderLegend
     */
    public function setBitRenderLegend($bitRenderLegend) {
        $this->bitRenderLegend = $bitRenderLegend;
    }

    /**
     * Set the font to be used in the chart
     *
     * @param string $strFont
     */
    public function setStrFont($strFont) {
        $this->strFont = $strFont;
    }

    /**
     * Set the color of the fonts used in the chart
     *
     * @param string $strFontColor
     */
    public function setStrFontColor($strFontColor) {
        $this->strFontColor = $strFontColor;
    }


    /**
     * Sets the angle to be used for rendering the x-axis lables
     *
     * @aram int $intXAxisAngle
     */
    public function setIntXAxisAngle($intXAxisAngle) {
        $this->intXAxisAngle = $intXAxisAngle;
    }

    /**
     * En- or disables 3d. Otherwise default beaviour.
     * @param boolean $bit3d
     */
    public function setBit3d($bit3d) {
        $this->bit3d = $bit3d;
    }

    /**
     * Sets the background-color of the title in html-notation
     *
     * @param string $strTitleBackgroundColor
     */
    public function setStrTitleBackgroundColor($strTitleBackgroundColor) {
        $this->strTitleBackgroundColor = $strTitleBackgroundColor;
    }

    /**
     * Sets the font-color of the title in html-notation
     *
     * @param string $strTitleFontColor
     */
    public function setStrTitleFontColor($strTitleFontColor) {
        $this->strTitleFontColor = $strTitleFontColor;
    }

    /**
     * By default, the legend is rendered at the right side of the chart.
     * Using this setter, the legend may be shifted to the bottom.
     *
     * @param bool $bitLegendPositionBottom
     */
    public function setBitLegendPositionBottom($bitLegendPositionBottom) {
        $this->bitLegendPositionBottom = $bitLegendPositionBottom;
    }




}

?>