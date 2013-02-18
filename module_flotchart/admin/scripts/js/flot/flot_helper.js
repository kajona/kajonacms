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

flotHelper.showTooltip = function(x, y, contents, seriesLabel, color) {
    $('<div id=\"tooltip\">'
            + '<div style=\"float:left; width:15px; height:15px; background-color:'+color+';margin:4px;\"></div>'
            + '<div style=\"float:left;\">  ' + seriesLabel + '</div>'
            + '<div style=\"clear:both; padding-left:23px\">  ' + contents + '</div>'
            + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + flotHelper.tooltipOffsetX,
        left: x + flotHelper.tooltipOffsetY,
        'box-shadow': '5px 5px 5px #444444',
        padding: '2px',
        'border-radius': '5px',
        'background-color': '#000000',
        'color': '#FFFFFF',
        opacity: 1
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
            seriesLabel = item.series.label,
            content = '';
            
            
            if(seriesLabel && seriesLabel.length>1)
            {
                seriesLabel = '<u>'+seriesLabel+'</u><br/>'; 
            }
            var ticks = item.series.xaxis.ticks;
            var tickLabel = ticks[previousPoint].label;
            /*
             * in general all labels are being processed by flotHelper.getTickFormatter
             * the first value of this regex is always the value within the div-Tag the
             * getTickFormatter produces
             */
            var tickLabelValue = tickLabel.match(/^<div.+>(.*?)<\/div>/)[1];
            
            if(isNaN(tickLabelValue)) {
                x = tickLabelValue;
                content += x + ' = ' + y;
            }
            else {
                content +='x = ' + x 
                +'<br/>' 
                +'y = ' + y;
            }
            flotHelper.showTooltip(pos.pageX, pos.pageY, content, seriesLabel, color);
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
    
    
    //create the tick array format --> [[0,'v0'],[1,'v1'],[1,'v2']]
    tickArray = $.map(tickArray, function(tick, index){
        return [[index, tick]];
    });

    //calculate which ticks should be rendered
    if(noOfWrittenLabels != null) {
        if(noOfWrittenLabels > 0) {
            noTicks = Math.ceil(nrLableTicks / noOfWrittenLabels);
        }
        else if(noOfWrittenLabels <= 0) {
            noTicks = 0; 
        }
    }

    //iterate ticks
    $.each(tickArray, function(index, tick) {
        //calculate if tick should be included in the chart
        var moduloResult = null;
        if(noTicks != null) {
            if(noTicks > 0) {
                moduloResult = index % noTicks;
            }
            else if(noTicks == 0) {
                moduloResult = 0;  
            }
        }
            
        //set tick value to axis.min -1 so that is will not be diaplayed in the chart
        if(!moduloResult == 0 || !moduloResult == null ) {
            tick[0] =  axis.min -1;
        }
        //hack for executing the tickFormatter even if the value of a tick is being set
        if(tick[1]) {
            tick[1] = flotHelper.getTickFormatter(angle, tick[1], axis)
        }
    });
    
    return tickArray;
};