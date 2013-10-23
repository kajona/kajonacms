//   (c) 2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: jquery.jqplot.custom_helper.js 3295 2013-10-23 09:29:28Z smeyer $

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.jqplotHelper = {

    previousNeighbor : null,
    /**
     * Sets the created canvasLabels invisible depending on the intNoOfWrittenLabels
     *
     * @param strChartId
     * @param intNoOfWrittenLabels
     */
    setLabelsInvisible : function(strChartId, intNoOfWrittenLabels) {
        if(intNoOfWrittenLabels<=2) intNoOfWrittenLabels = 3;

        //get the xaxis canvas ticks
        var tickArray = $('#'+strChartId+' div.jqplot-xaxis canvas.jqplot-xaxis-tick');
        var noOfTicks = tickArray.length;

        if(noOfTicks > intNoOfWrittenLabels) {
            var modulo = Math.ceil(noOfTicks/(intNoOfWrittenLabels-2));//-2 because first and last will always be rendered

            //first and last tick will never be set invsible, that's we start from i=1 and end at tickArray.length-1
            for(var i = 1; i<tickArray.length-1; i++ ) {
                if((i%modulo)!=0) {
                    $(tickArray[i]).css('display', 'none')
                }
            }
        }
    },
    mouseLeave : function(ev, gridpos, datapos, neighbor, plot, tooltipId) {
        $('#jqplot_tooltip').remove();
        this.previousNeighbor = null;
    },
    mouseMove : function(ev, gridpos, datapos, neighbor, plot, tooltipId) {
//        console.debug(neighbor);
//        console.debug(plot);
        //no datapoint
        if(neighbor==null) {
            $('#jqplot_tooltip').remove();
            this.previousNeighbor = null;
        }
        else {
            var arrXAxisTicks = plot.axes.xaxis.ticks;
            var objTooltip = {
                seriesLabel:plot.series[neighbor.seriesIndex].label,

                seriesColor:plot.series[neighbor.seriesIndex].highlightColorGenerator ?
                        plot.series[neighbor.seriesIndex].highlightColorGenerator.get(neighbor.pointIndex) : //pieChart
                        plot.series[neighbor.seriesIndex].color, //all other charts

                xValue:arrXAxisTicks.length>0?arrXAxisTicks[neighbor.pointIndex]:neighbor.data[0],
                yValue:neighbor.data[1],
                seriesIndex :neighbor.seriesIndex,
                plot: plot
            };

            //new data point
            if( this.previousNeighbor == null //new data point --> create new point
                || (this.previousNeighbor.seriesIndex != neighbor.seriesIndex) //different series --> create new point
                || ((this.previousNeighbor.seriesIndex == neighbor.seriesIndex) && (this.previousNeighbor.pointIndex != neighbor.pointIndex)))//same series but different point --> create new point
            {
                this.showTooltip(ev.pageX, ev.pageY, objTooltip , tooltipId);
                this.previousNeighbor = neighbor;
            }
            //same series and point -> only move tooltip
            else if((this.previousNeighbor.seriesIndex == neighbor.seriesIndex) && (this.previousNeighbor.pointIndex == neighbor.pointIndex)) {
                this.showTooltip(ev.pageX, ev.pageY, null , tooltipId, true);
            }
        }
    },

    showTooltip : function(x, y, objTooltip, tooltipId, move) {
        var top = y-60;
        var left = x+5;

        if(!move) {
            //create the toolTip
            $('#jqplot_tooltip').remove();

            var toolTipDiv = $('<div id=\"jqplot_tooltip\" class=\"jqplot-chart-tooltip\">'
                + '<div id=\"jqplot_tooltip_series\" class=\"jqplot-chart-tooltip-series\"></div>'
                + '<div id=\"jqplot_tooltip_content\"  class=\"jqplot-chart-tooltip-content\"></div>'
                + '</div>').appendTo("body");

            $('#jqplot_tooltip_series').html("<span>"+objTooltip.xValue+"</span>");
            $('#jqplot_tooltip_content').html("<span>"+objTooltip.seriesLabel+" : <b>"+objTooltip.yValue+"</b></span>");


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