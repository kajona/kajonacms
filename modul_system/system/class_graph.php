<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_graph.php																						*
* 	Class to create graphs, using the jpgraph lib														*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/

include_once(_systempath_."/jpgraph/jpgraph.php");

/**
 * This class could be used to create graphs.
 * Those can present datasets using different types of graphs as bar-charts, pie-charts or
 * xy-graphs
 *
 * @package modul_system
 */
class class_graph {

	/**
	 * Instance of the current graph
	 *
	 * @var Graph
	 */
	private $objGraph;

	private $strXAxisTitle = "";
	private $strYAxisTitle = "";
	private $strGraphTitle = "";


	//---------------------------------------------------------------------------------------------------
	//   The following values are used to seperate the graph-modes, because not all
	//   methods are allowed with every chart-type

	private $GRAPH_TYPE_BAR = 0;
	private $GRAPH_TYPE_LINE = 1;
	private $GRAPH_TYPE_PIE = 2;

	//---------------------------------------------------------------------------------------------------

	private $intCurrentGraphMode = -1;

	private $bitMarginSet = false;
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
	 * Creates an instance of the graph object. Used as a base for all further operations
	 *
	 * @param int $intWidth
	 * @param int $intHeight
	 * @param string $strFilename
	 */
	private function createGraphInstance($intWidth, $intHeight, $strFilename = "auto") {
		$this->objGraph = new Graph($intWidth, $intHeight, $strFilename);
		$this->objGraph->SetScale("textlin");
		$this->objGraph->SetMarginColor("#efefef");
	}

	/**
	 * Creates an instance of the piegraph object. Used as a base for all further operations
	 *
	 * @param int $intWidth
	 * @param int $intHeight
	 * @param string $strFilename
	 */
	private function createPieGraphInstance($intWidth, $intHeight, $strFilename = "auto") {
		$this->objGraph = new PieGraph($intWidth, $intHeight, $strFilename);
		$this->objGraph->SetAntiAliasing(true);
	}

	/**
	 * Sets the axis-labels to the graph. This is done before stroking the graph.
	 * If no label was set, it won't be published to the graph
	 *
	 */
	private function setAxisTitles() {
		if($this->strYAxisTitle != "")
			$this->objGraph->yaxis->title->Set($this->strYAxisTitle);
		if($this->strXAxisTitle != "")
			$this->objGraph->xaxis->title->Set($this->strXAxisTitle);
	}

	/**
	 * Writes the specified title to the graph-object
	 *
	 */
	private function setGraphTitle() {
		if($this->strGraphTitle != "")
			$this->objGraph->title->Set($this->strGraphTitle);
	}

	/**
	 * Does the magic. Creates all necessary stuff and finally
	 * sends the graph directly (!!!) to the browser
	 *
	 */
	public function showGraph() {
		$this->preGraphCreation();
		$this->objGraph->Stroke();
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

		$this->objGraph->Stroke($strFilename);
	}

	/**
	 * Used to make internal cleanups and the
	 * final call of needed setters
	 *
	 */
	private function preGraphCreation() {
		//axis-labels just, if axises available
		if($this->intCurrentGraphMode == $this->GRAPH_TYPE_BAR ||
		   $this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE  ) {
			$this->setAxisTitles();
			$this->objGraph->legend->SetLayout(LEGEND_HOR);
            $this->objGraph->legend->Pos(0.4,0.95,"center","bottom");
            //adjust margin, if not done before
            if(!$this->bitMarginSet)
                $this->objGraph->img->SetMargin(30,30,10,60);
		}

		$this->setGraphTitle();
		//disable border
		$this->objGraph->frame_weight = 0;
		//set antialiasing, not used in every char, but won't make it worse ;)
		$this->objGraph->img->SetAntiAliasing();
		//reset the graph-mode
		$this->intCurrentGraphMode = -1;
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
	 * For each column in the diagram, one entry is used
	 *
	 * @param array $arrLabels
	 * @param int $intIntervall
	 * @param int $intDegree
	 */
	public function setXAxisTickLabels($arrLabels, $intIntervall = 1, $intDegree = 0) {
	    if(count($arrLabels) == 0) {
	        $arrLabels[] = "0";
	    }
		$this->objGraph->xaxis->SetTickLabels($arrLabels);
		if($intIntervall != 1) {
			$this->objGraph->xaxis->setTextLabelInterval($intIntervall);
		}
		if($intDegree != 0) {
			$this->setXAxisLabelAngle($intDegree);
		}

	}

	/**
	 * Set the angle fpr the x-axis labels
	 *
	 * @param int $intAngle
	 */
	public function setXAxisLabelAngle($intAngle = 0) {
	    $this->objGraph->xaxis->SetLabelAngle($intAngle);
	}

	/**
	 * sets the margin of the graph to the outher container
	 *
	 * @param int $intLeft
	 * @param int $intRight
	 * @param int $intTop
	 * @param int $intBottom
	 */
	public function setMargin($intLeft, $intRight, $intTop, $intBottom) {
	    $this->bitMarginSet = true;
	    $this->objGraph->SetMargin($intLeft, $intRight, $intTop, $intBottom);
	}


	/**
	 * Used to create a bar-chart.
	 * A sample-code could be:
	 *
	 *  $objGraph = new class_graph();
	 *  $objGraph->setStrXAxisTitle("x-axis");
	 *  $objGraph->setStrYAxisTitle("y-axis");
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->createBarChart(array("value 1" => 4, "value 2" => 5, "value 3" => 2, "value 4" => 7));
	 *  $objGraph->saveGraph(_bildergalerie_cachepfad_."/graph.png");
	 *
	 * @param array $arrValues see the example above for the internal array-structure
	 * @param int $intWidth
	 * @param int $intHeight
	 * @param bool $bitHorizontal
	 */
	public function createBarChart($arrValues, $intWidth = 400, $intHeight = 200, $bitHorizontal = false) {
		$this->intCurrentGraphMode = $this->GRAPH_TYPE_BAR;

		include_once(_systempath_."/jpgraph/jpgraph_bar.php");
		$this->createGraphInstance($intWidth, $intHeight);

		//if horozontal, rotate image
		if($bitHorizontal) {
            $this->objGraph->Set90AndMargin();
		}

		$arrTicks = array();
		$arrConcreteValues = array();
		foreach($arrValues as $strTitle => $intValue) {
			$arrTicks[] = $strTitle;
			$arrConcreteValues[] = $intValue;
		}

		$objBarPlot = new BarPlot($arrConcreteValues);
		$objBarPlot->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);
		$objBarPlot->SetColor("navy");
		$this->objGraph->Add($objBarPlot);

		$this->setXAxisTickLabels($arrTicks);
	}


	/**
	 * creates a line-plot graph
	 * example-code:
	 *
	 *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->createLinePlotChart();
     *  $objGraph->addLinePlot(array(2,4,5,7,8), "blue");
	 *  $objGraph->addLinePlot(array(4,4,3,9,1), "red");
	 *  $objGraph->setXAxisTickLabels(array("eins", "zwo", "drii", "fier", "pf�nf"));
     *  $objGraph->showGraph();
     *
     * @param int $intWidth
     * @param int $intHeight
	 *
	 */
	public function createLinePlotChart($intWidth = 400, $intHeight = 200) {
		$this->intCurrentGraphMode = $this->GRAPH_TYPE_LINE;

		include_once(_systempath_."/jpgraph/jpgraph_line.php");
		$this->createGraphInstance($intWidth, $intHeight);
	}


	/**
	 * adds a line-plot to the current graph-instance (if possible)
	 *
	 * @param array $arrValues
	 * @param strig $strColor either in hex-values: #ccddee or as plaintext
	 * @param string $strLegend
	 * @return bool
	 */
	public function addLinePlot($arrValues, $strColor = "red", $strLegend = "") {
		if($this->intCurrentGraphMode == $this->GRAPH_TYPE_LINE) {
			if(count($arrValues) == 0) {
				$arrValues[] = 0;
				$arrValues[] = 0;
			}
			$objLinePlot = new LinePlot($arrValues);
			$objLinePlot->SetColor($strColor);
			if($strLegend != "")
				$objLinePlot->SetLegend($strLegend);
			$this->objGraph->Add($objLinePlot);
			return true;
		}

		return false;
	}

	/**
	 * Creates a pie chart
	 *
	 * Usage:
	 *  $objGraph = new class_graph();
	 *  $objGraph->setStrGraphTitle("Test Graph");
	 *  $objGraph->create3DPieChart(array("v1" => 10, "v2" => 30, "v3" => 30, "v4" => 20, "v5" => 10));
	 *  $objGraph->showGraph();
	 *
	 *
	 * @param array $arrKeyValues
	 * @param int $intWidth
	 * @param int $intHeight
	 */
	public function create3DPieChart($arrKeyValues, $intWidth = 300, $intHeight = 200) {
		$this->intCurrentGraphMode = $this->GRAPH_TYPE_PIE;

		include_once(_systempath_."/jpgraph/jpgraph_pie.php");
		include_once(_systempath_."/jpgraph/jpgraph_pie3d.php");
		$this->createPieGraphInstance($intWidth, $intHeight);

		$arrKeys = array();
		$arrValues = array();
		foreach($arrKeyValues as $strKey => $intValue) {
			$arrKeys[] = $strKey;
			$arrValues[] = $intValue;
		}

		$objPiePlot = new PiePlot3D($arrValues);
		$objPiePlot->SetSize(0.5);
		$objPiePlot->SetCenter(0.45);
		$objPiePlot->SetLegends($arrKeys);
		$this->objGraph->Add($objPiePlot);
		$this->objGraph->SetShadow(false);
	}




} //class_image

?>