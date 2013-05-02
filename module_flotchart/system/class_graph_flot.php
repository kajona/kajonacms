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

    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = null) {
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
        
        //widths and heights of the divs
        $titleHeight = 0;
        $titleWidth = 0;
        $legendHeight = 0;
        $legendWidth = 0;
        $xAxisHeight = 0;
        $yAxisWidth = 0;
        $chartHeight = $this->intHeight;
        $chartWidth = $this->intWidth;

        //Calculate X-Axis label height
        if($this->strXAxisTitle!="") {
            $xAxisHeight = 15;
        }
        //Calculate Y-Axis label width
        if($this->strYAxisTitle!="") {
            $yAxisWidth = 15;
        }
        //Calculate title
        if($this->strGraphTitle!="") {
            $titleHeight = 15;
        }
        //Create Legend Div
        if($this->bShowLegend) {
            $legendWidth=140;
            $legendHeight=$chartHeight-$titleHeight+$xAxisHeight;
            $legendLeft=$this->intWidth-$legendWidth+$yAxisWidth;
            $legendBottom=$legendHeight-7;
            $legendId="legend_" . $this->strChartId;
            $legendStyles="position:relative;width:".$legendWidth."px;left:".$legendLeft."px;bottom:".$legendBottom."px;";
            $strDivLegend = "<div id=\"".$legendId."\"  style=\"".$legendStyles."\" > &nbsp; </div>";
        }
        //Create Title Div
        if($this->strGraphTitle!="") {
            $titleWidth=$this->intWidth - $legendWidth;
            $titleId = "title_" . $this->strChartId;
            $titleStyles = "text-align:center;width:".$titleWidth."px; height:".$titleHeight."px;";
            $strDivTitle =  "<div id=\"".$titleId."\"   style=\"".$titleStyles."\"> ".$this->strGraphTitle."</div>";
        }
        
        //Create Chart Div
        $chartHeight -= $titleHeight+$xAxisHeight;
        $chartWidth -= $legendWidth+$yAxisWidth;
        $fontStyle = "font-family:".$this->strFont.";";
        $chartStyles = $fontStyle."width:".$chartWidth."px;height:".$chartHeight."px;";
        $strDivChart =  "<div id=\"" . $this->strChartId . "\" style=\"".$chartStyles." \" > &nbsp; </div>";
        
        //generate the wrapping js-code and all requirements
        $strReturn = "<div id=\"chart_" . $this->strChartId . "\" style=\"width:".$this->intWidth."px; height:".$this->intHeight."px;\">";
        $strReturn = $strReturn.$strDivTitle.$strDivChart.$strDivLegend."</div>";
        
        $strReturn .= "<script type='text/javascript'>

            KAJONA.admin.loader.loadFile(['/core/module_flotchart/admin/scripts/js/flot/jquery.flot.min.js'], function() {
                KAJONA.admin.loader.loadFile([
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.pie.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.stack.min.js',
                    '/core/module_flotchart/admin/scripts/js/flot/jquery.flot.axislabels.js',
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