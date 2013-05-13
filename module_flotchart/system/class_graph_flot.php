<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/


/**
 * This class could be used to create graphs based on the flot API.
 * Flot renders charts on the client side.
 *
 * @package module_flotchart
 * @since 4.0
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_flot implements interface_graph {

    /**
     * @var class_graph_flot_chartdata_base
     */
    private $objChartData = null;
    private $strChartId = null;

    private $intWidth = 700;
    private $intHeight = 350;
    
    //line and barchart
    private $strXAxisTitle = "";
    private $strYAxisTitle = "";
    private $intXAxisAngle = 0;
    private $arrXAxisTickLabels = array();
    private $intNrOfWrittenLabels = null;
    
    //line char, bar chart, pie chart
    private $bShowLegend = true;
    private $strGraphTitle = "";
    private $strBackgroundColor = null;//white
    private $strFont = null;//e.g. Verdana, Arial, Helvetica, sans-serifs
    private $strFontColor = null;//e.g. #000000
    
    
    /**
    * Constructor
    *
    */
    public function __construct() {
    }
    
    public function getObjChartData() {
        return $this->objChartData;
    }
    
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
            $seriesData = new class_graph_flot_seriesdata();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabel($strLegend);
            $seriesData->setStrSeriesChartType(class_graph_flot_seriesdatatypes::BAR);
            $this->objChartData->addSeriesData($seriesData);
        }
    }

    public function addLinePlot($arrValues, $strLegend) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
            $seriesData = new class_graph_flot_seriesdata();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabel($strLegend);
            $seriesData->setStrSeriesChartType(class_graph_flot_seriesdatatypes::LINE);
            $this->objChartData->addSeriesData($seriesData);
        }
        
    }

    public function addStackedBarChartSet($arrValues, $strLegend) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_impl();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_impl) {
            $seriesData = new class_graph_flot_seriesdata();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabel($strLegend);
            $seriesData->setStrSeriesChartType(class_graph_flot_seriesdatatypes::STACKEDBAR);
            $this->objChartData->addSeriesData($seriesData);
        }
    }

    public function createPieChart($arrValues, $arrLegends) {
        if($this->objChartData == null) 
            $this->objChartData = new class_graph_flot_chartdata_base_pie();
        
        if($this->objChartData instanceof class_graph_flot_chartdata_base_pie) {
            $seriesData = new class_graph_flot_seriesdata_pie();
            $seriesData->setArrayData($arrValues);
            $seriesData->setStrLabelArray($arrLegends);
            $seriesData->setStrSeriesChartType(class_graph_flot_seriesdatatypes::PIE);
            $this->objChartData->addSeriesData($seriesData);
        }
    }

    public function saveGraph($strFilename) {
        $this->objChartData->saveGraph($strFilename);
    }

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $this->arrXAxisTickLabels = $arrXAxisTickLabels;
        $this->intNrOfWrittenLabels = $intNrOfWrittenLabels;
    }

    public function setBitRenderLegend($bitRenderLegend) {
        $this->bShowLegend = $bitRenderLegend;
    }

    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
    }

    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
    }

    public function setIntXAxisAngle($intXAxisAngle) {
        $this->intXAxisAngle = $intXAxisAngle;
    }
    
    public function setStrBackgroundColor($strColor) {
        $this->strBackgroundColor = $strColor;
    }

    public function setStrFont($strFont) {
        $this->strFont = $strFont;
    }

    public function setStrFontColor($strFontColor) {
        $this->strFontColor = $strFontColor;
    }

    public function setStrGraphTitle($strTitle) {
        $this->strGraphTitle = $strTitle;
    }

    public function setStrXAxisTitle($strTitle) {
        $this->strXAxisTitle = $strTitle;
    }

    public function setStrYAxisTitle($strTitle) {
        $this->strYAxisTitle = $strTitle;
    }

    public function showGraph() {
        $this->renderGraph();
    }

    /**
     * Common way to get a chart. The engine should save the chart
     * to the filesystem (if required) and returns the chart with a complete
     * code to embed the chart into a html-page.
     * Please be aware that the method may return a large amount of code depending on
     * the type of engine - from a simple img-tag up to a full js-logic.
     *
     * @since 4.0
     * @throws class_exception
     * @return mixed
     */
    public function renderGraph() {
        if($this->objChartData == null)
             throw new class_exception("Chart not initialized yet", class_exception::$level_ERROR);
        
        //set attributes
        $this->objChartData->setIntWidth($this->intWidth);
        $this->objChartData->setIntHeight($this->intHeight);
        $this->objChartData->setBitRenderLegend($this->bShowLegend);
        $this->objChartData->setIntXAxisAngle($this->intXAxisAngle);
        $this->objChartData->setStrBackgroundColor($this->strBackgroundColor);
        $this->objChartData->setStrFont($this->strFont);
        $this->objChartData->setStrFontColor($this->strFontColor);
        $this->objChartData->setStrGraphTitle($this->strGraphTitle);
        $this->objChartData->setStrXAxisTitle($this->strXAxisTitle);
        $this->objChartData->setStrYAxisTitle($this->strYAxisTitle);
        $this->objChartData->setArrXAxisTickLabels($this->arrXAxisTickLabels, $this->intNrOfWrittenLabels);
        $this->objChartData->setChartId($this->strChartId = generateSystemid());
                
        //create chart
        $strChartCode = $this->objChartData->showGraph($this->strChartId);
        $this->objChartData->showChartToolTips($this->strChartId);
        
        //divs
        $strDivTitle = "";
        $strDivChart = "";
        $strDivLegend = "";
        $strDivLabelXAxis = "";
        $strDivLabelYAxis = "";
        $showBorder = "";//border:1px solid;";
        
        //widths and heights of the divs
        $titleHeight = 0;
        $titleWidth = 0;
        $legendHeight = 0;
        $legendWidth = 0;
        $xAxisLabelHeight = 0;
        $yAxisLabelWidth = 0;

        //global styles
        $fontFamily = ($this->strFont!=null)?"font-family:".$this->strFont.";":"";
        $fontColor = ($this->strFontColor!=null)?"color:".$this->strFontColor.";":"";
        
        //Set X-Axis label height
        if($this->strXAxisTitle!="") {
            $xAxisLabelHeight = 15;
        }
        //Set Y-Axis label width
        if($this->strYAxisTitle!="") {
            $yAxisLabelWidth = 15;
        }
        //Set title height
        if($this->strGraphTitle!="") {
            $titleHeight = 15;
        }
        //Create Legend Div
        if($this->bShowLegend) {
            //calculation of width, height and position
            $legendWidth  = 140;
            $legendHeight = $this->intHeight - $titleHeight - $xAxisLabelHeight;
            $legendLeft   = $this->intWidth - $legendWidth;
            $legendTop    = $titleHeight;
            
            //create the div
            $legendId="legend_" . $this->strChartId;
            $legendPosition = "left:".$legendLeft."px;"."top:".$legendTop."px;";
            $legendDimension= "width:".$legendWidth."px;";
            $legendStyles = "position:absolute;".$legendPosition.$legendDimension.$fontFamily.$fontColor;
            $strDivLegend = "<div id=\"".$legendId."\"  style=\"".$legendStyles."\" > &nbsp; </div>";
        }
        //Create Title Div
        if($this->strGraphTitle!="") {
            //calculation of width
            $titleWidth=$this->intWidth - $legendWidth - $yAxisLabelWidth;
            $titleLeft = $yAxisLabelWidth;
            //create the div
            $titleId = "title_" . $this->strChartId;
            $titlePosition = "left:".$titleLeft."px;";
            $titleDimension = "width:".$titleWidth."px; height:".$titleHeight."px;";
            $titleStyles = "position:absolute;text-align:center;".$titlePosition.$titleDimension.$fontFamily.$fontColor;
            $strDivTitle =  "<div id=\"".$titleId."\"   style=\"".$titleStyles."\"> ".$this->strGraphTitle."</div>";
        }
        
        //Create x-Axis label div
        if($this->strXAxisTitle!="") {
            //calculation
            $xAxisLabelWidth=$this->intWidth - $legendWidth - $yAxisLabelWidth;
            $xAxisLabelLeft = $yAxisLabelWidth;
            $xAxisLabelTop = $this->intHeight - $xAxisLabelHeight;
            //create the div
            $xAxisLabelId="xAxisLabel_" . $this->strChartId;
            $xAxisLabelPosition = "top:".$xAxisLabelTop."px;"."left:".$xAxisLabelLeft."px;";
            $xAxisLabelDimension= "width:".$xAxisLabelWidth."px; height:".$xAxisLabelHeight."px;";
            $xAxisLabelStyles = "position:absolute;text-align:center;".$xAxisLabelPosition.$xAxisLabelDimension.$fontFamily.$fontColor;
            $strDivLabelXAxis = "<div id=\"".$xAxisLabelId."\"  style=\"".$xAxisLabelStyles."\" > ".$this->strXAxisTitle." </div>";
        }
        
        //Create y-Axis label div
        if($this->strYAxisTitle!="") {
            //create the div
            $yAxisLabelId="yAxisLabel_" . $this->strChartId;
            //$yAxisLabelPosition = "top:".($this->intHeight/1.5)."px;";
            $yAxisLabelPosition = "top:60%;";
            //rotation
            $yAxisLabelRotationWebKit = "-webkit-transform: rotate(-90deg);"."-webkit-transform-origin:0% 5%;";
            $yAxisLabelRotationMoz = "-moz-transform: rotate(-90deg);"."-moz-transform-origin:0% 5%;";
            $yAxisLabelRotationIE8 = "filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);";
            $yAxisLabelRotationIE = "-ms-transform: rotate(-90deg);"."-ms-transform-origin:0% 5%;";
            $yAxisLabelRotationO = "-o-transform: rotate(-90deg);"."-o-transform-origin:0% 5%;";
            $yAxisLabelRotation = $yAxisLabelRotationWebKit.$yAxisLabelRotationMoz.$yAxisLabelRotationIE8.$yAxisLabelRotationIE.$yAxisLabelRotationO;
            $yAxisLabelStyles = "position:absolute;".$yAxisLabelPosition.$yAxisLabelRotation.$fontFamily.$fontColor;
            $strDivLabelYAxis = "<div id=\"".$yAxisLabelId."\"  style=\"".$yAxisLabelStyles."\" > ".$this->strYAxisTitle." </div>";
        }
        
        //Calculate actual chart height
        $flotChartHeight = $this->intHeight - $titleHeight - $xAxisLabelHeight;
        $flotChartWidth =  $this->intWidth  - $legendWidth - $yAxisLabelWidth;
        
        //Create the div for the chart
        $chartPosition = "left:".$yAxisLabelWidth."px;top:".$titleHeight."px;";
        $charDimension = "width:".$flotChartWidth."px;height:".$flotChartHeight."px;";
        $chartStyles = $chartPosition.$charDimension."position:absolute;".$showBorder;
        $strDivChart =  "<div id=\"" . $this->strChartId . "\" style=\"".$chartStyles." \" > &nbsp; </div>";
        
        //now connect the created divs 
        $strReturn = "<div id=\"chart_" . $this->strChartId . "\" style=\"position:relative;width:".$this->intWidth."px; height:".$this->intHeight."px;".$showBorder."\">";
        $strReturn = $strReturn.$strDivTitle.$strDivChart.$strDivLabelXAxis.$strDivLabelYAxis.$strDivLegend."</div>";
        
        //generate the wrapping js-code and all requirements
        $strReturn .= "<script type='text/javascript'>
            KAJONA.admin.loader.loadFile(['/core/module_flotchart/admin/scripts/js/flot/jquery.flot.min.js'], function() {
                KAJONA.admin.loader.loadFile([
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.pie.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.stack.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.resize.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.orderBars.js',
                    '/core/module_flotchart/admin/scripts/js/flot/excanvas.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/flot_helper.js',
                    '/core/module_flotchart/admin/scripts/flot.css'
                ], function() {
                    ".$strChartCode."    
                });
            });
        </script>";
        //<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="excanvas.min.js"></script><![endif]-->
        //enable tooltips
        return $strReturn;
    }

}