<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

require_once(_systempath_."/pChart/pChart.class");
require_once(_systempath_."/pChart/pData.class");


/**
 * This class could be used to create graphs based on the pChart API.
 * pChart renders charts on the serverside and passes them back as images.
 *
 * @package module_system
 * @since 3.3.0
 * @author sidler@mulchprod.de
 */
class class_graph_pchart implements interface_graph {


	private $strXAxisTitle = "";
	private $strYAxisTitle = "";
	private $strGraphTitle = "";

    private $intWidth = 720;
    private $intHeight = 200;

    private $strBackgroundColor = "#EFEFEF";
    private $strGraphBackgroundColor = "#FAFAFA";
    private $strOuterFrameColor = "#E1E1E1";
    private $strFontColor = "#686868";
    private $strGridColor = "#E6E6E6";

    private $bitRoundedCorners = true;
    private $bitRenderLegend = true;
    private $bitAdditionalDatasetAdded = false;
    private $bitScaleFromAdditionalDataset = false;

    private $strFont = "/fonts/dejavusans.ttf";
    private $arrDefaultColorPalette = array();

    private $intLegendBreakCount = 15;
    private $intLegendAdditionalMargin = 0;

    private $intXAxisAngle = 0;

    private $arrValueSeriesToRender = array();



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
     * @var pChart
     */
    private $objChart = null;

    /**
     *
     * @var pData
     */
    private $objDataset = null;

    /**
     *
     * @var pData
     */
    private $objAdditionalDataset = null;


	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->objDataset = new pData();
        $this->objAdditionalDataset = new pData();
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
	 *  $objGraph->addBarChartSet(array(1,2,4,5) "serie 1");
	 *
	 * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
	 */
	public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        if($this->intCurrentGraphMode > 0) {
            //only allow this method to be called again if in bar-mode
            if($this->intCurrentGraphMode != $this->GRAPH_TYPE_BAR)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);
        }

		$this->intCurrentGraphMode = $this->GRAPH_TYPE_BAR;
        $strInternalSerieName = generateSystemid();

        $this->objDataset->AddPoint($arrValues, $strInternalSerieName);
        $this->objDataset->AddSerie($strInternalSerieName);
        if($bitWriteValues)
            $this->arrValueSeriesToRender[] = $strInternalSerieName;

        $this->objDataset->SetSerieName($this->stripLegend($strLegend), $strInternalSerieName);
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

		$this->intCurrentGraphMode = $this->GRAPH_TYPE_STACKEDBAR;
        $strSerieName = generateSystemid();

        $this->objDataset->AddPoint($arrValues, $strSerieName);
        $this->objDataset->AddSerie($strSerieName);

        $this->objDataset->SetSerieName($this->stripLegend($strLegend), $strSerieName);
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
            if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR) {
                $this->bitAdditionalDatasetAdded = true;
                $strSerieName = generateSystemid();

                $this->objAdditionalDataset->AddPoint($arrValues, $strSerieName);
                $this->objAdditionalDataset->AddSerie($strSerieName);

                $this->objAdditionalDataset->SetSerieName($this->stripLegend($strLegend), $strSerieName);

                //jump out since only additional
                return;
            }
            //only allow this method to be called again if in line-mode
            else if($this->intCurrentGraphMode != $this->GRAPH_TYPE_LINE)
                throw new class_exception("Chart already initialized", class_exception::$level_ERROR);


        }

        $this->intCurrentGraphMode = $this->GRAPH_TYPE_LINE;

        $strSerieName = generateSystemid();

        $this->objDataset->AddPoint($arrValues, $strSerieName);
        $this->objDataset->AddSerie($strSerieName);

        $this->objDataset->SetSerieName($this->stripLegend($strLegend), $strSerieName);



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

        $strSerieName = generateSystemid();

        $this->objDataset->AddPoint($arrValues, $strSerieName);
        $this->objDataset->AddSerie($strSerieName);

        $strSerieName = generateSystemid();

        foreach($arrLegends as &$strValue)
            $strValue = $this->stripLegend($strValue);

        $this->objDataset->AddPoint($arrLegends, $strSerieName);
        $this->objDataset->AddSerie($strSerieName);

        $this->objDataset->SetAbsciseLabelSerie($strSerieName);

    }



    /**
     * Creates the object and prepares it for rendering.
     * Does all the calculation like borders, margins, paddings ....
     *
     * @return void
     */
    private function preGraphCreation() {

        // Initialize the graph
        $this->objChart = new pChart($this->intWidth, $this->intHeight);

        //set the color palette to be used
        foreach($this->arrDefaultColorPalette as $intKey => $strCurrentColor) {
            $arrCurColor = hex2rgb($strCurrentColor);
            $this->objChart->setColorPalette($intKey, $arrCurColor[0], $arrCurColor[1], $arrCurColor[2]);
        }

        //calculate all needed params, draw that funky shit

        //the outer bounding and pane - rounded and with sharp corners
        $arrBackgroundColor = hex2rgb($this->strBackgroundColor);
        if($this->bitRoundedCorners) {
            $this->objChart->drawFilledRoundedRectangle(2,2,$this->intWidth-3 ,$this->intHeight-3, 5, $arrBackgroundColor[0], $arrBackgroundColor[1], $arrBackgroundColor[2]);
            $arrOuterBack = hex2rgb($this->strOuterFrameColor);
            $this->objChart->drawRoundedRectangle(0,0,$this->intWidth-1,$this->intHeight-1,5, $arrOuterBack[0], $arrOuterBack[1], $arrOuterBack[2]);
        }
        else {
            $this->objChart->drawFilledRectangle(0, 0, $this->intWidth, $this->intHeight, $arrBackgroundColor[0], $arrBackgroundColor[1], $arrBackgroundColor[2]);
        }

        //the graph area - x and or y-axis label present?
        if($this->bitRenderLegend)
            $intRightMargin = 10;
        else
            $intRightMargin = 20;
        $intTopMargin = 15;
        $intBottomMargin = 30;
        $intLeftMargin = 40;

        $intLegendWidth = 0;
        if($this->bitRenderLegend)
            $intLegendWidth = 120;

        $intWidth = $this->intWidth - $intRightMargin - $intLegendWidth;
        $intHeight = $this->intHeight - $intBottomMargin;

        $intLeftStart = $intLeftMargin;
        $intTopStart = $intTopMargin;

        if($this->strYAxisTitle != "") {
            $intLeftStart += 15;
            //$intWidth -= 15; //TODO: why not needed?
        }



        if($this->strXAxisTitle != "")
            $intHeight -=15;
        if($this->strGraphTitle != "") {
            //$intHeight -= 12; //TODO: why not needed???
            $intTopStart += 12;
        }

        if($this->intCurrentGraphMode != $this->GRAPH_TYPE_PIE) {
            $this->objChart->setGraphArea($intLeftStart, $intTopStart, $intWidth, $intHeight);
            $arrPaneBackground = hex2rgb($this->strGraphBackgroundColor);
            $this->objChart->drawGraphArea($arrPaneBackground[0], $arrPaneBackground[1], $arrPaneBackground[2], true);
        }

        $arrFontColors = hex2rgb($this->strFontColor);
        $this->objChart->setFontProperties(_systempath_.$this->strFont, 8);

        //set up the axis-titles
        if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR ||
           $this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR ||
           $this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE) {


            if($this->strXAxisTitle != "")
                $this->objDataset->SetXAxisName($this->strXAxisTitle);
            if($this->strYAxisTitle != "")
                $this->objDataset->SetYAxisName($this->strYAxisTitle);

        }



        //the x- and y axis, in- / exclusive margins
        if($this->bitAdditionalDatasetAdded && $this->bitScaleFromAdditionalDataset)
            $this->objChart->drawScale($this->objAdditionalDataset->GetData(), $this->objAdditionalDataset->GetDataDescription(), SCALE_START0, $arrFontColors[0], $arrFontColors[1], $arrFontColors[2], TRUE, $this->intXAxisAngle, 1, true);
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR)
            $this->objChart->drawScale($this->objDataset->GetData(), $this->objDataset->GetDataDescription(), SCALE_START0, $arrFontColors[0], $arrFontColors[1], $arrFontColors[2], TRUE, $this->intXAxisAngle, 1, true);
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR)
            $this->objChart->drawScale($this->objDataset->GetData(), $this->objDataset->GetDataDescription(), SCALE_ADDALLSTART0, $arrFontColors[0], $arrFontColors[1], $arrFontColors[2], TRUE, $this->intXAxisAngle, 1, true);
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE)
            $this->objChart->drawScale($this->objDataset->GetData(), $this->objDataset->GetDataDescription(), SCALE_NORMAL, $arrFontColors[0], $arrFontColors[1], $arrFontColors[2], TRUE, $this->intXAxisAngle, 1, false);

        //the background grid
        if($this->intCurrentGraphMode != $this->GRAPH_TYPE_PIE) {
            $arrGridColor = hex2rgb($this->strGridColor);
            $this->objChart->drawGrid(4, true, $arrGridColor[0], $arrGridColor[1], $arrGridColor[2], 50);
        }


        if($this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE) {

            // Draw the line graph
            $this->objChart->drawLineGraph($this->objDataset->GetData(),$this->objDataset->GetDataDescription());
            //dots in line
            $this->objChart->drawPlotGraph($this->objDataset->GetData(),$this->objDataset->GetDataDescription(), 3,2 , 255, 255, 255);
        }
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR) {

            //the zero-line
            $this->objChart->setFontProperties(_systempath_.$this->strFont, 6);
            $this->objChart->drawBarGraph($this->objDataset->GetData(),$this->objDataset->GetDataDescription(), TRUE);
            $this->objChart->drawTreshold(0, 143,55,72, TRUE, TRUE);

            //if given, render the line-plots on top
            if($this->bitAdditionalDatasetAdded) {
                //the line itself
                $this->objChart->drawLineGraph($this->objAdditionalDataset->GetData(),$this->objAdditionalDataset->GetDataDescription());
                //the dots
                $this->objChart->drawPlotGraph($this->objAdditionalDataset->GetData(),$this->objAdditionalDataset->GetDataDescription(), 3,2 , 255, 255, 255);
            }

        }
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_STACKEDBAR) {

            //the zero-line
            $this->objChart->setFontProperties(_systempath_.$this->strFont, 6);
            $this->objChart->drawTreshold(0, 143,55,72, TRUE, TRUE);
            $this->objChart->drawStackedBarGraph($this->objDataset->GetData(),$this->objDataset->GetDataDescription(), 75);
        }
        else if($this->intCurrentGraphMode == $this->GRAPH_TYPE_PIE) {

            $this->objChart->drawPieGraph($this->objDataset->GetData(),$this->objDataset->GetDataDescription(), ceil($this->intWidth/2)-20, ceil($this->intHeight/2) , ceil($intHeight/2)+20, PIE_PERCENTAGE, TRUE,50,20,5);
        }

        //render values?
        if(count($this->arrValueSeriesToRender) > 0) {
            $this->objChart->writeValues($this->objDataset->GetData(), $this->objDataset->GetDataDescription(), $this->arrValueSeriesToRender);
        }



        // Finish the graph
        $this->objChart->setFontProperties(_systempath_.$this->strFont, 7);

        //set up the legend
        if($this->bitRenderLegend) {
            if($this->intCurrentGraphMode == $this->GRAPH_TYPE_PIE)
                $this->objChart->drawPieLegend($this->intWidth-$intLegendWidth-$intRightMargin+10-$this->intLegendAdditionalMargin, $intTopStart, $this->objDataset->GetData(), $this->objDataset->GetDataDescription(),255,255,255);
            else {
                $arrLegend = $this->objDataset->GetDataDescription();
                //merge legends
                if($this->bitAdditionalDatasetAdded) {
                    $arrAdditionalLegend = $this->objAdditionalDataset->GetDataDescription();
                    foreach($arrAdditionalLegend["Description"] as $strKey => $strName) {
                        $arrLegend["Description"][$strKey] = $strName;
                    }
                }
                $this->objChart->drawLegend($this->intWidth-$intLegendWidth-$intRightMargin+10-$this->intLegendAdditionalMargin, $intTopStart, $arrLegend,255,255,255);
            }
        }

        //draw the title
        if($this->strGraphTitle != "") {
        $this->objChart->setFontProperties(_systempath_.$this->strFont, 10);
            $this->objChart->drawTitle(0, $intTopMargin, $this->strGraphTitle, $arrFontColors[0], $arrFontColors[1], $arrFontColors[2], $this->intWidth, 10);
        }


    }

    /**
	 * Does the magic. Creates all necessary stuff and finally
	 * sends the graph directly (!!!) to the browser.
     * Execution should be terminated afterwards.
	 *
	 */
	public function showGraph() {
		$this->preGraphCreation();
        $this->objChart->Stroke();
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

        if(strtolower(substr($strFilename, -3) != "png"))
            throw new class_exception("Filename must be a png-file", class_exception::$level_ERROR);

        $this->objChart->Render($strFilename);
	}

	/**
	 * Inserts a line-break to long legend-values
	 *
	 * @param $strLegend
	 * @return string
	 */
	private function stripLegend($strLegend) {
	    $intStart = $this->intLegendBreakCount;

	    while(uniStrlen($strLegend) > $intStart) {
	        $strLegend = uniSubstr($strLegend, 0, $intStart)."\n".uniSubstr($strLegend, $intStart);
	        $intStart += $this->intLegendBreakCount;
	    }

	    return $strLegend;
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
     * If using rounded edged, use this color for the outer frame
     *
     * @param string $strOuterFrameColor
     */
    public function setStrOuterFrameColor($strOuterFrameColor) {
        $this->strOuterFrameColor = $strOuterFrameColor;
    }

    /**
     * Set the color of the plots background pane
     *
     * @param string $strGraphBackgroundColor
     */
    public function setStrGraphBackgroundColor($strGraphBackgroundColor) {
        $this->strGraphBackgroundColor = $strGraphBackgroundColor;
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

        $this->objDataset->AddPoint($arrMadeUpLabels, $strSerieName);
        $this->objDataset->SetAbsciseLabelSerie($strSerieName);

        if($this->bitAdditionalDatasetAdded) {
            $this->objAdditionalDataset->AddPoint($arrMadeUpLabels, $strSerieName);
            $this->objAdditionalDataset->SetAbsciseLabelSerie($strSerieName);
        }
    }

    /**
     * Sets whether the created chart should be rendered using rounded corners or not
     *
     * @param bool $bitRoundedCorners
     */
    public function setBitRoundedCorners($bitRoundedCorners) {
        $this->bitRoundedCorners = $bitRoundedCorners;
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
     * Set the array of colors to be used within the charts
     *
     * @param $arrColorPalette
     */
    public function setArrColorPalette($arrColorPalette) {
        $this->arrDefaultColorPalette = $arrColorPalette;
    }

    /**
     * Set the number of chars before a linebreak is inserted into the legend-string
     *
     * @param int $intLegendBreakCount
     */
    public function setIntLegendBreakCount($intLegendBreakCount) {
        $this->intLegendBreakCount = $intLegendBreakCount;
    }

    /**
     * Shift the legend leftwards
     *
     * @param int $intLegendAdditionalMargin
     */
    public function setIntLegendAdditionalMargin($intLegendAdditionalMargin) {
        $this->intLegendAdditionalMargin = $intLegendAdditionalMargin;
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
     * If set to true, the scale is calculated from the additional dataset
     * instead of from the regular set.
     *
     * @param bool $bitScaleFromAdditionalDataset
     */
    public function setBitScaleFromAdditionalDataset($bitScaleFromAdditionalDataset) {
        $this->bitScaleFromAdditionalDataset = $bitScaleFromAdditionalDataset;
    }




}

