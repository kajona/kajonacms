//   (c) 2013-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

define([
    'jquery',
    'folderview',
    'util',
    "jqueryui",
    'jqplot',
    'jqplot.logAxisRenderer',
    'jqplot.barRenderer',
    'jqplot.categoryAxisRenderer',
    'jqplot.canvasTextRenderer',
    'jqplot.canvasAxisTickRenderer',
    'jqplot.canvasAxisLabelRenderer',
    'jqplot.pointLabels',
    'jqplot.cursor',
    'jqplot.dateAxisRenderer',
    'jqplot.enhancedLegendRenderer',
    'jqplot.enhancedPieLegendRenderer',
    'jqplot.pieRenderer',
    'jqplot.highlighter',
    'jqplot.canvasOverlay',
    'filesaver.blob',
    'filesaver.c2b',
    'filesaver',
    'excanvas'

], function($, folderview, util) {

    var arrChartObjects = []; //container for all chart objects

    var previousNeighbor = null; //used in methods mouseLeave and mouseMove

    var jqplotHelper = {};


    /**
     *
     * @param strChartId - id of the chart
     * @param strTooltipId - id of the tooltip of the chart
     * @param strResizeableId - id of the resizable container
     * @param bitEnableChartResizing -
     * @param arrChartData - data array for the chart
     * @param objChartOptions - chart rendering options
     * @param objPostPlotOptions - options set for pos plotting
     * @param arrSeriesToDataPoints - two dimensional array which may contains urls for each data point of a series. format: array[seriesIndex][dataPointIndex] => strURL
     */
    jqplotHelper.jqPlotChart = function (strChartId, strTooltipId, strResizeableId, bitEnableChartResizing, arrChartData, objChartOptions, objPostPlotOptions, arrSeriesToDataPoints) {
        this.strTooltipId = strTooltipId;
        this.strChartId = strChartId;
        this.strResizeableId = strResizeableId;
        this.arrChartData = arrChartData;
        this.objChartOptions = objChartOptions;
        this.objPostPlotOptions = objPostPlotOptions;
        this.arrSeriesToDataPoints = arrSeriesToDataPoints;

        this.objJqplotChart = null;//the actual jqPlot object
        this.bitIsRendered = false;//flag to tell if the chart was already rendered or not (needed in case the chart should replotted)
        this.bitEnableChartResizing = bitEnableChartResizing;

        this.objChartOptions.axesDefaults.tickOptions.formatter = jqplotHelper.customJqPlotNumberFormatter;


        /**
         * Called after the chart was plotted
         */
        this.postPlot = function () {
            if (this.objPostPlotOptions.hasOwnProperty("intNrOfWrittenLabelsXAxis") && this.objPostPlotOptions["intNrOfWrittenLabelsXAxis"] != null) {
                this.objJqplotChart.postDrawHooks.add(jqplotHelper.setLabelsInvisible, [this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsXAxis"], "xaxis"]);
                jqplotHelper.setLabelsInvisible(this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsXAxis"], "xaxis");
            }
            if (this.objPostPlotOptions.hasOwnProperty("intNrOfWrittenLabelsYAxis") && this.objPostPlotOptions["intNrOfWrittenLabelsYAxis"] != null) {
                this.objJqplotChart.postDrawHooks.add(jqplotHelper.setLabelsInvisible, [this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsYAxis"], "yaxis"]);
                jqplotHelper.setLabelsInvisible(this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsYAxis"], "yaxis");
            }
        };

        /**
         * Plots the chart
         */
        this.plot = function () {
            this.objJqplotChart = $.jqplot(this.strChartId, this.arrChartData, this.objChartOptions);

            jqplotHelper.bindMouseEvents(this.strChartId, this.strTooltipId);
            jqplotHelper.bindDataClickEvents(this.strChartId);

            if (this.bitEnableChartResizing) {
                jqplotHelper.enableChartResizing(this.strChartId, this.strResizeableId);
            }
        };

        /**
         * Renders and plot the charts
         */
        this.render = function () {
            if (this.bitIsRendered) {
                this.objJqplotChart.replot();
                return;
            }
            this.plot();
            this.postPlot();
            this.bitIsRendered = true;
        };

        arrChartObjects[this.strChartId] = this;
    };

    jqplotHelper.enableChartResizing = function (strChartId, strResizeableId) {
        //make it resizable
        $('#' + strResizeableId).resizable({
            delay: 20,
            helper: "ui-resizable-helper-jqplot",
            stop: function (event, ui) {
                arrChartObjects[strChartId].render();
            }
        });
    };

    jqplotHelper.bindDataClickEvents = function (strChartId) {
        $('#' + strChartId).bind('jqplotDataClick',
            function (ev, seriesIndex, pointIndex, data) {
                var objChart = arrChartObjects[this.id];

                //check if a url and call it in a dialogue
                if (objChart.arrSeriesToDataPoints && objChart.arrSeriesToDataPoints[seriesIndex]) {
                    if (objChart.arrSeriesToDataPoints[seriesIndex][pointIndex]) {
                        var objDataPoint = objChart.arrSeriesToDataPoints[seriesIndex][pointIndex];

                        if (objDataPoint.actionhandler && objDataPoint.actionhandler != null) {
                            var objFunction = eval("(" + objDataPoint.actionhandler + ")");
                            if ($.isFunction(objFunction)) {
                                objFunction.call(this, ev, seriesIndex, pointIndex, data, objDataPoint);
                            }
                        }
                        else {
                            jqplotHelper.dataPointOnClickURLHandler(ev, seriesIndex, pointIndex, data, objDataPoint);
                        }
                    }
                }
            });
    };

    jqplotHelper.bindMouseEvents = function (strChartId, strTooltipId) {
        $('#' + strChartId).bind('jqplotMouseMove', function (ev, gridpos, datapos, neighbor, plot) {
            jqplotHelper.mouseMove(ev, gridpos, datapos, neighbor, plot, strTooltipId)
        });
        $('#' + strChartId).bind('jqplotMouseLeave', function (ev, gridpos, datapos, neighbor, plot) {
            jqplotHelper.mouseLeave(ev, gridpos, datapos, neighbor, plot, strTooltipId)
        });
    };


    /**
     * Sets the created canvasLabels invisible depending on the intNoOfWrittenLabels
     *
     * @param strChartId
     * @param intNoOfWrittenLabels
     * @param strAxis
     */
    jqplotHelper.setLabelsInvisible = function (strChartId, intNoOfWrittenLabels, strAxis) {
        //get the axis canvas ticks
        var tickArray = $('#' + strChartId + ' div.jqplot-' + strAxis + ' canvas.jqplot-' + strAxis + '-tick');
        var noOfTicks = tickArray.length;

        if (noOfTicks > intNoOfWrittenLabels) {
            var modulo = Math.ceil(noOfTicks / intNoOfWrittenLabels);

            var startFor = 0;
            var endFor = noOfTicks;
            var numberTicksNotInvisible = 0;

            //always keep first an last visible
            if (intNoOfWrittenLabels >= 2) {
                startFor = 1;
                endFor = noOfTicks - 1;
                numberTicksNotInvisible = 2;
            }

            for (var i = startFor; i < endFor; i++) {
                if (numberTicksNotInvisible == intNoOfWrittenLabels) {
                    $(tickArray[i]).css('visibility', 'hidden');
                    continue;
                }
                if ((i % modulo) != 0) {
                    $(tickArray[i]).css('visibility', 'hidden');
                    continue;
                }
                numberTicksNotInvisible++;
            }
        }
    };
    jqplotHelper.mouseLeave = function (ev, gridpos, datapos, neighbor, plot, tooltipId) {
        $('#jqplot_tooltip').remove();
        previousNeighbor = null;
    };
    jqplotHelper.mouseMove = function (ev, gridpos, datapos, neighbor, plot, tooltipId) {
        //Check if a datapoint(neighbor) is selected
        if (neighbor == null) {
            //no datapoint selected
            $('#jqplot_tooltip').remove();

            $(ev.currentTarget).css('cursor', 'auto');

            previousNeighbor = null;
        }
        else {
            var series = plot.series[neighbor.seriesIndex];
            var arrXAxisTicks = plot.axes.xaxis.ticks;
            var arrYAxisTicks = plot.axes.yaxis.ticks;

            var seriesColor = null;
            var xValue = null;
            var yValue = null;
            if (series._type == "pie") {
                seriesColor = series.highlightColorGenerator.get(neighbor.pointIndex);
                xValue = isNaN(neighbor.data[0]) ? neighbor.data[0] : Math.round(neighbor.data[0] * 1000) / 1000;
                yValue = isNaN(neighbor.data[1]) ? neighbor.data[1] : Math.round(neighbor.data[1] * 1000) / 1000;
            }
            else {
                seriesColor = series.color;

                //Either set label value for this datapoint or value (if no x-axis labels were set)
                xValue = arrXAxisTicks.length > 0 && series._primaryAxis == "_xaxis" ?//only if xaxis is primary axis use the tick labels
                    arrXAxisTicks[neighbor.pointIndex] :
                Math.round(neighbor.data[0] * 1000) / 1000;

                //Either set label value for this datapoint or value (if no y-axis labels were set)
                yValue = arrYAxisTicks.length > 0 && series._primaryAxis == "_yaxis" ?//only if yaxis is primary axis use the tick labels
                    arrYAxisTicks[neighbor.pointIndex] :
                Math.round(neighbor.data[1] * 1000) / 1000;

            }

            var objTooltip = {
                seriesObject: series,
                seriesLabel: series.label,
                seriesColor: seriesColor,
                xValue: xValue,
                yValue: yValue,
                seriesIndex: neighbor.seriesIndex,
                plot: plot
            };

            //new data point
            if (previousNeighbor == null //new data point --> create new point
                || (previousNeighbor.seriesIndex != neighbor.seriesIndex) //different series --> create new point
                || ((previousNeighbor.seriesIndex == neighbor.seriesIndex) && (previousNeighbor.pointIndex != neighbor.pointIndex)))//same series but different point --> create new point
            {
                jqplotHelper.showTooltip(ev.pageX, ev.pageY, objTooltip, tooltipId, false);

                if (jqplotHelper.hasDataPoint(ev, neighbor.seriesIndex, neighbor.pointIndex)) {
                    $(ev.currentTarget).css('cursor', 'pointer');
                }

                previousNeighbor = neighbor;
            }
            //same series and point -> only move tooltip
            else if ((previousNeighbor.seriesIndex == neighbor.seriesIndex) && (previousNeighbor.pointIndex == neighbor.pointIndex)) {
                jqplotHelper.showTooltip(ev.pageX, ev.pageY, null, tooltipId, true);
            }
        }
    };

    jqplotHelper.hasDataPoint = function (ev, seriesIndex, pointIndex) {
        var objChart = arrChartObjects[$(ev.currentTarget).attr('id')];

        if (objChart.arrSeriesToDataPoints && objChart.arrSeriesToDataPoints[seriesIndex]) {
            var objDataPoint = objChart.arrSeriesToDataPoints[seriesIndex][pointIndex];
            if (objDataPoint && objDataPoint.actionhandlervalue && objDataPoint.actionhandlervalue != null) {
                return true;
            }
        }
        return false;
    };

    /**
     * Displays the tooltip
     *
     * @param x
     * @param y
     * @param objTooltip
     * @param tooltipId
     * @param move
     */
    jqplotHelper.showTooltip = function (x, y, objTooltip, tooltipId, move) {
        var top = y - 60;
        var left = x + 5;

        if (!move) {
            //set value for primary and secondary xaxis
            var valuePrimaryAxis = null;
            var valueSecondaryAxis = null;
            var tickOptionsrimaryAxis = null;
            var tickOptionsSecondaryAxis = null;
            if (objTooltip.seriesObject._primaryAxis == "_xaxis") {
                valuePrimaryAxis = objTooltip.xValue;
                valueSecondaryAxis = objTooltip.yValue;

                tickOptionsrimaryAxis = objTooltip.seriesObject._xaxis.tickOptions;
                tickOptionsSecondaryAxis = objTooltip.seriesObject._yaxis.tickOptions;
            }
            else {
                valuePrimaryAxis = objTooltip.yValue;
                valueSecondaryAxis = objTooltip.xValue;

                tickOptionsrimaryAxis = objTooltip.seriesObject._yaxis.tickOptions;
                tickOptionsSecondaryAxis = objTooltip.seriesObject._xaxis.tickOptions;
            }


            if ($.isNumeric(valuePrimaryAxis)) {
                valuePrimaryAxis = tickOptionsrimaryAxis.formatter(tickOptionsrimaryAxis.formatString, valuePrimaryAxis)
            }
            if ($.isNumeric(valueSecondaryAxis)) {
                valueSecondaryAxis = tickOptionsSecondaryAxis.formatter(tickOptionsSecondaryAxis.formatString, valueSecondaryAxis);
            }

            //create the toolTip
            $('#jqplot_tooltip').remove();

            var toolTipDiv = $('<div id=\"jqplot_tooltip\" class=\"jqplot-chart-tooltip\">'
                + '<div id=\"jqplot_tooltip_series\" class=\"jqplot-chart-tooltip-series\"></div>'
                + '<div id=\"jqplot_tooltip_content\"  class=\"jqplot-chart-tooltip-content\"></div>'
                + '</div>').appendTo("body");


            $('#jqplot_tooltip_series').html("<span>" + valuePrimaryAxis + "</span>");
            $('#jqplot_tooltip_content').html("<span>" + objTooltip.seriesLabel + " : <b>" + valueSecondaryAxis + "</b></span>");


            toolTipDiv.css("border-color", objTooltip.seriesColor)
                .css("top", top)
                .css("left", left)
                .show();
        }
        else {
            //only move the tooltip
            $('#jqplot_tooltip').css("top", top)
                .css("left", left);
        }
    };

    jqplotHelper.customJqPlotNumberFormatter = function (format, value) {
        if (typeof value == "number") {
            var intNumberDecimals = 0;
            var intWholePart = 3;
            if (value % 1 !== 0) {
                intNumberDecimals = 3;
            }
            value = util.formatNumber(value, intNumberDecimals, intWholePart, $.jqplot.sprintf.thousandsSeparator, $.jqplot.sprintf.decimalMark);
        }

        return $.jqplot.sprintf(format, value);
    };

    jqplotHelper.dataPointOnClickURLHandler = function (ev, seriesIndex, pointIndex, data, objDataPoint) {
        if (objDataPoint.actionhandlervalue && objDataPoint.actionhandlervalue != null && objDataPoint.actionhandlervalue != "") {
            folderview.dialog.setContentIFrame(objDataPoint.actionhandlervalue);
            folderview.dialog.setTitle('');
            folderview.dialog.init();
        }
    };

    jqplotHelperdataRedirect = function (ev, seriesIndex, pointIndex, data, objDataPoint) {
        if (objDataPoint.actionhandlervalue && objDataPoint.actionhandlervalue != null && objDataPoint.actionhandlervalue != "") {
            window.location = objDataPoint.actionhandlervalue;
        }
    };

    jqplotHelper.exportAsImage = function (strElementId) {
        var canvas = $("#" + strElementId).jqplotToImageCanvas();
        canvas.toBlob(function (blob) {
            saveAs(blob, "download.png");
        });
    };

    return jqplotHelper;
});