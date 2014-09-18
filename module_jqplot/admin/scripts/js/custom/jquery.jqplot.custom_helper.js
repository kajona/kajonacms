//   (c) 2013-2014 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.jqplotHelper = {

    previousNeighbor : null,//used in methods mouseLeave and mouseMove
    arrChartObjects : [],

    jqPlotChart : function (strChartId, strTooltipId, strResizeableId, arrChartData, objChartOptions, objPostPlotOptions) {
        this.strTooltipId = strTooltipId;
        this.strChartId = strChartId;
        this.strResizeableId = strResizeableId;
        this.arrChartData = arrChartData;
        this.objChartOptions = objChartOptions;
        this.objPostPlotOptions = objPostPlotOptions;

        this.objJqplotChart = null;
        this.bitIsRendered = false;


        this.postPlot = function() {
            if (this.objPostPlotOptions.hasOwnProperty("intNrOfWrittenLabelsXAxis") && this.objPostPlotOptions["intNrOfWrittenLabelsXAxis"] != null) {
                KAJONA.admin.jqplotHelper.setLabelsInvisible(this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsXAxis"], "xaxis");
            }
            if (this.objPostPlotOptions.hasOwnProperty("intNrOfWrittenLabelsYAxis") && this.objPostPlotOptions["intNrOfWrittenLabelsYAxis"] != null) {
                KAJONA.admin.jqplotHelper.setLabelsInvisible(this.strChartId, this.objPostPlotOptions["intNrOfWrittenLabelsYAxis"], "yaxis");
            }
            if (this.objPostPlotOptions.hasOwnProperty("bitXAxisLabelsInvisible") && this.objPostPlotOptions["bitXAxisLabelsInvisible"]) {
                KAJONA.admin.jqplotHelper.setAxisInvisible(this.strChartId, "xaxis");
            }
            if (this.objPostPlotOptions.hasOwnProperty("bitXAxisLabelsInvisible") && this.objPostPlotOptions["bitXAxisLabelsInvisible"]) {
                KAJONA.admin.jqplotHelper.setAxisInvisible(this.strChartId, "yaxis");
            }
        };

        this.plot = function () {
            this.objJqplotChart = $.jqplot(this.strChartId, this.arrChartData, this.objChartOptions);
            KAJONA.admin.jqplotHelper.bindMouseEvents(this.strChartId, this.strTooltipId, this.strResizeableId);
        };

        this.render = function () {
            if (this.bitIsRendered) {
                this.objJqplotChart.replot();
                this.postPlot();
                return;
            }
            this.plot();
            this.postPlot();
            this.bitIsRendered = true;
        };


        KAJONA.admin.jqplotHelper.arrChartObjects[this.strChartId] = this;
    },

    bindMouseEvents : function (strChartId, strTooltipId, strResizeableId) {
        $('#' + strChartId).bind('jqplotMouseMove', function (ev, gridpos, datapos, neighbor, plot) {KAJONA.admin.jqplotHelper.mouseMove(ev, gridpos, datapos, neighbor, plot, strTooltipId)});
        $('#' + strChartId).bind('jqplotMouseLeave', function (ev, gridpos, datapos, neighbor, plot) {KAJONA.admin.jqplotHelper.mouseLeave(ev, gridpos, datapos, neighbor, plot, strTooltipId)});

        $('#'+strResizeableId).resizable({delay:20});
        $('#'+strResizeableId).bind('resize', function(event, ui) {
            KAJONA.admin.jqplotHelper.arrChartObjects[strChartId].render();
        });
    },


    /**
     * Sets the created canvasLabels invisible depending on the intNoOfWrittenLabels
     *
     * @param strChartId
     * @param intNoOfWrittenLabels
     * @param strAxis
     */
    setLabelsInvisible : function (strChartId, intNoOfWrittenLabels, strAxis) {
        //get the axis canvas ticks
        var tickArray = $('#' + strChartId + ' div.jqplot-' + strAxis + ' canvas.jqplot-' + strAxis + '-tick');
        var noOfTicks = tickArray.length;

        if(noOfTicks > intNoOfWrittenLabels) {
            var modulo = Math.ceil(noOfTicks / intNoOfWrittenLabels);

            var startFor = 0;
            var endFor = noOfTicks;
            var numberTicksNotInvisible = 0;

            //always keep first an last visible
            if(intNoOfWrittenLabels >= 2) {
                startFor = 1;
                endFor = noOfTicks - 1;
                numberTicksNotInvisible = 2;
            }

            for(var i = startFor; i<endFor; i++ ) {
                if(numberTicksNotInvisible == intNoOfWrittenLabels) {
                    $(tickArray[i]).css('display', 'none');
                    continue;
                }
                if((i%modulo)!=0) {
                    $(tickArray[i]).css('display', 'none');
                    continue;
                }
                numberTicksNotInvisible++;
            }
        }
    },
    /**
     * Sets the created canvasLabels invisible depending on the intNoOfWrittenLabels
     *
     * @param strChartId
     * @param strAxis
     */
    setAxisInvisible : function (strChartId, strAxis) {
        var tickArray = $('#'+strChartId+' div.jqplot-'+strAxis).hide();
    },
    mouseLeave : function (ev, gridpos, datapos, neighbor, plot, tooltipId) {
        $('#jqplot_tooltip').remove();
        this.previousNeighbor = null;
    },
    mouseMove : function (ev, gridpos, datapos, neighbor, plot, tooltipId) {
        //Check if a datapoint(neighbor) is selected
        if(neighbor==null) {
            //no datapoint selected
            $('#jqplot_tooltip').remove();
            this.previousNeighbor = null;
        }
        else {
            var series = plot.series[neighbor.seriesIndex];
            var arrXAxisTicks = plot.axes.xaxis.ticks;
            var arrYAxisTicks = plot.axes.yaxis.ticks;

            var seriesColor = null;
            var xValue = null;
            var yValue = null;
            if(series._type == "pie") {
                seriesColor = series.highlightColorGenerator.get(neighbor.pointIndex);
                xValue = isNaN(neighbor.data[0]) ? neighbor.data[0] : Math.round(neighbor.data[0] * 1000) / 1000;
                yValue = isNaN(neighbor.data[1]) ? neighbor.data[1] : Math.round(neighbor.data[1] * 1000) / 1000;
            }
            else {
                seriesColor = series.color;

                //Either set label value for this datapoint or value (if no x-axis labels were set)
                xValue = arrXAxisTicks.length > 0 && series._primaryAxis == "_xaxis"?//only if xaxis is primary axis use the tick labels
                        arrXAxisTicks[neighbor.pointIndex] :
                        Math.round(neighbor.data[0] * 1000) / 1000;

                //Either set label value for this datapoint or value (if no y-axis labels were set)
                yValue = arrYAxisTicks.length > 0 && series._primaryAxis == "_yaxis"?//only if yaxis is primary axis use the tick labels
                        arrYAxisTicks[neighbor.pointIndex] :
                        Math.round(neighbor.data[1] * 1000) / 1000;

            }

            var objTooltip = {
                seriesObject: series,
                seriesLabel: series.label,
                seriesColor :seriesColor,
                xValue: xValue,
                yValue: yValue,
                seriesIndex: neighbor.seriesIndex,
                plot: plot
            };

            //new data point
            if( this.previousNeighbor == null //new data point --> create new point
                || (this.previousNeighbor.seriesIndex != neighbor.seriesIndex) //different series --> create new point
                || ((this.previousNeighbor.seriesIndex == neighbor.seriesIndex) && (this.previousNeighbor.pointIndex != neighbor.pointIndex)))//same series but different point --> create new point
            {
                this.showTooltip(ev.pageX, ev.pageY, objTooltip, tooltipId, false);
                this.previousNeighbor = neighbor;
            }
            //same series and point -> only move tooltip
            else if((this.previousNeighbor.seriesIndex == neighbor.seriesIndex) && (this.previousNeighbor.pointIndex == neighbor.pointIndex)) {
                this.showTooltip(ev.pageX, ev.pageY, null , tooltipId, true);
            }
        }
    },

    /**
     * Displays the tooltip
     *
     * @param x
     * @param y
     * @param objTooltip
     * @param tooltipId
     * @param move
     */
    showTooltip : function(x, y, objTooltip, tooltipId, move) {
        var top = y-60;
        var left = x+5;

        if(!move) {
            //set value for primary and secondary xaxis
            var valuePrimaryAxis = null;
            var valueSecondaryAxis = null;
            if(objTooltip.seriesObject._primaryAxis == "_xaxis") {
                valuePrimaryAxis = objTooltip.xValue;
                valueSecondaryAxis = objTooltip.yValue;
            }
            else {
                valuePrimaryAxis = objTooltip.yValue;
                valueSecondaryAxis = objTooltip.xValue;
            }

            if(objTooltip.seriesObject._primaryAxis == "_xaxis") {

            }

            //create the toolTip
            $('#jqplot_tooltip').remove();

            var toolTipDiv = $('<div id=\"jqplot_tooltip\" class=\"jqplot-chart-tooltip\">'
                + '<div id=\"jqplot_tooltip_series\" class=\"jqplot-chart-tooltip-series\"></div>'
                + '<div id=\"jqplot_tooltip_content\"  class=\"jqplot-chart-tooltip-content\"></div>'
                + '</div>').appendTo("body");


            $('#jqplot_tooltip_series').html("<span>"+valuePrimaryAxis+"</span>");
            $('#jqplot_tooltip_content').html("<span>"+objTooltip.seriesLabel+" : <b>"+valueSecondaryAxis+"</b></span>");


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
    }
};