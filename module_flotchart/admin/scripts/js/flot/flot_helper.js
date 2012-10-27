/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var flotHelper = {};

flotHelper.tooltipOffsetX = 5;
flotHelper.tooltipOffsetY = -60;

flotHelper.getTickFormatter = function(angle, val, axis) {
    /* Safari */
    var safari = "-webkit-transform: rotate("+angle+"deg); ";

    /* Firefox */
    var firefox = "-moz-transform: rotate("+angle+"deg); ";

    /* IE */
    var ie = "-ms-transform: rotate("+angle+"deg); ";

    /* Opera */
    var opera = "-o-transform: rotate("+angle+"deg);";
    
    return "<div style=\""+safari+firefox+ie+opera+"\">"+val+"</div>"
};

flotHelper.showTooltip = function(x, y, contents, color) {
    $('<div id=\"tooltip\">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + flotHelper.tooltipOffsetX,
        left: x + flotHelper.tooltipOffsetY,
        border: '1px solid '+color,
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.80
    }).appendTo("body").show();
};

flotHelper.doToolTip = function(event, pos, item) {
    $('#x').text(pos.x);
    $('#y').text(pos.y);


    if (item) {
        //if series and datapoint are the same do nothings
        if(previousPoint == item.dataIndex && previousSeries == item.seriesIndex) {
            //move tooltip if still in the same series
            $('#tooltip').css( {
                top: pos.pageY + flotHelper.tooltipOffsetX,
                left: pos.pageX + flotHelper.tooltipOffsetY
            })
        }
        
        //create new tooltip if series or datapoint changes
        if (previousPoint != item.dataIndex || previousSeries != item.seriesIndex) {
            previousPoint = item.dataIndex;
            previousSeries = item.seriesIndex;

            $('#tooltip').remove();
            var x = item.datapoint[0],
            y = item.datapoint[1],
            color = item.series.color,
            content = '<u>'+item.series.label+'</u>\n';

            var ticks = item.series.xaxis.ticks;
            var label = ticks[item.dataIndex].label;

            /*
             * in general all labels are bieng process by flotHelper.getTickFormatter
             * the first value of this regex is always the value within the div-Tag the
             * getTickFormatter produces
             */
            var labelvalue = label.match(/^<div.+>(.*?)<\/div>/)[1];
            
            if(isNaN(labelvalue)) {
                x = labelvalue;
                content += '<br/>' + x + ' = ' + y;
            }
            else {
                content += '<br/>'
                +'x = ' + x
                +'<br/>'
                +'y = ' + y 
            }
            flotHelper.showTooltip(pos.pageX, pos.pageY, content, color);
        }
    }
    else {
        $('#tooltip').remove();
        previousPoint = null;  
        previousSeries = null;
    }
};

flotHelper.showPieToolTip = function(event, pos, item) {
    if(item) {
        //changed tooltip if series changes
        if (previousSeries != item.seriesIndex) {
            $('#tooltip').remove();
            previousSeries = item.seriesIndex;
            //create new tooltip
            var percent = parseFloat(item.series.percent);
            var content = '<span style=\"\">'+'<u>'+item.series.label+'</u><br/>'+percent.toFixed(2)+'%</span>';
            flotHelper.showTooltip(pos.pageX, pos.pageY, content, item.series.color);
        } else {
            //move tooltip if still in the same series
            $('#tooltip').css( {
                top: pos.pageY + flotHelper.tooltipOffsetX,
                left: pos.pageX + flotHelper.tooltipOffsetY
            })
        }
    }
    else {
        $('#tooltip').remove(); 
        previousSeries = null;
    }
};

flotHelper.getTickArray = function(angle, axis, tickArray, noOfWrittenLabels) {
    var nrLableTicks = tickArray.length;
    var noTicks = null;
    
    if(noOfWrittenLabels != null) {
        if(noOfWrittenLabels > 0) {
            noTicks = Math.ceil(nrLableTicks / noOfWrittenLabels);
        }
        else if(noOfWrittenLabels <= 0) {
            noTicks = 0; 
        }
    }

    //iterate ticks
    tickArray.forEach(function(tick, index) {
        //calculate if tick should be included in the chart
        var moduloResult = null;
        if(noTicks != null) {
            if(noTicks > 0) {
                moduloResult = index % noTicks;
            }
            else if(noTicks == 0) {
                moduloResult = 0;  
            }
            
            //set tick value to axis.min -1 so that is will not be diaplayed in the chart
            if(!moduloResult == 0 || !moduloResult == null ) {
                tick[0] =  axis.min -1;
            }
            //hack for executing the tickFormatter even if the value of a tick is being set
            if(tick[1]) {
                tick[1] = flotHelper.getTickFormatter(angle, tick[1], axis)
            }
        }
    });
    
    return tickArray;
};