/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var flotHelper = {};

flotHelper.tooltipOffsetX = 5;
flotHelper.tooltipOffsetY = -60;


/**
 * Rotates the ticks on the x-Axis.
 * 
 * @param {int} angle
 * @param {string} val
 * @param {object} axis
 * @returns {String}
 */
flotHelper.getTickFormatter = function(angle, val, axis) {
    var rotation_angle = "rotate("+angle+"deg)";
    var rotation_css = [
        '-webkit-transform:' + rotation_angle,
        '-moz-transform:' + rotation_angle,
        '-o-transform:' + rotation_angle,
        '-ms-transform:' + rotation_angle,
        'transform:' + rotation_angle
    ];
    return "<div style=\""+rotation_css.join(";")+"\">"+val+"</div>";
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
        'box-shadow': '2px 2px 3px #444444',
        padding: '2px',
        'background-color': '#000000',
        'color': '#FFFFFF',
        opacity: 0.8
    }).appendTo("body").show();
};

/**
 * Method for displaying a tooltip for bar and line charts when mouse over 
 * over a series.
 * 
 * @param {type} event
 * @param {object} pos
 * @param {object} item
 * @returns {undefined}
 */
flotHelper.doToolTip = function(event, pos, item) {
    $('#x').text(pos.x);
    $('#y').text(pos.y);

    if (item) {
        //if series and datapoint are the same do nothings
        if(previousPoint === item.dataIndex && previousSeries === item.seriesIndex) {
            //move tooltip if still in the same series
            $('#tooltip').css( {
                top: pos.pageY + flotHelper.tooltipOffsetX,
                left: pos.pageX + flotHelper.tooltipOffsetY
            });
        }
        
        //create new tooltip if series or datapoint changes
        if (previousPoint !== item.dataIndex || previousSeries !== item.seriesIndex) {
            previousPoint = item.dataIndex;
            previousSeries = item.seriesIndex;


            $('#tooltip').remove();
            var x = item.datapoint[0],
            y = item.datapoint[1],
            color = item.series.color,
            seriesLabel = item.series.label,
            content = '';
            
            //get name of the selected series
            if(seriesLabel && seriesLabel.length>1)
            {
                seriesLabel = '<u>'+seriesLabel+'</u><br/>'; 
            }
            
            //check if for x-Axis labels were set
            var ticks = item.series.xaxis.ticks;
            var tickLabelValue = null;
            if(ticks[previousPoint] && ticks[previousPoint].label) {
                var tickLabel = ticks[previousPoint].label;
                /*
                * in general all labels are being processed by flotHelper.getTickFormatter
                * the first value of this regex is always the value within the div-Tag which the
                * getTickFormatter function produces
                */
               tickLabelValue = tickLabel.match(/^<div.+>(.*?)<\/div>/)[1];
            }
            else {
                //take the normal x-Value
                tickLabelValue = item.datapoint[0];
            }
            
            
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


/**
 * Method for displaying a tooltip in a pie chart when mouse over over a series.
 * 
 * @param {type} event
 * @param {object} pos
 * @param {object} item
 * @returns {undefined}
 */
flotHelper.showPieToolTip = function(event, pos, item) {
    if(item) {
        //changed tooltip if series changes
        if (previousSeries !== item.seriesIndex) {
            $('#tooltip').remove();
            previousSeries = item.seriesIndex;
            //create new tooltip
            var percent = parseFloat(item.series.percent);
            seriesLabel = item.series.label;
            if(seriesLabel && seriesLabel.length>1)
            {
                seriesLabel = '<u>'+seriesLabel+'</u><br/>'; 
            }
            
            var content = '<span style=\"\">'+percent.toFixed(2)+'%</span>';
            flotHelper.showTooltip(pos.pageX, pos.pageY, content, seriesLabel, item.series.color);
        } else {
            //move tooltip if still in the same series
            $('#tooltip').css( {
                top: pos.pageY + flotHelper.tooltipOffsetX,
                left: pos.pageX + flotHelper.tooltipOffsetY
            });
        }
    }
    else {
        $('#tooltip').remove(); 
        previousSeries = null;
    }
};

/**
 * Calculates the ticks on the x-Axis.
 * If the amount of ticks is to large for the width of the chart the number 
 * of ticks will be reduced.
 * 
 * @param {int} angle
 * @param {object} axis
 * @param {arrays} tickLabelsArray
 * @param {int} noOfWrittenLabels
 * @returns {unresolved}
 */
flotHelper.getTickArray = function(angle, axis, tickLabelsArray, noOfWrittenLabels) {
    var nrLableTicks = tickLabelsArray.length;
    var noTicks = null;
    
    
    //create the tick array format --> [[0,'v0'],[1,'v1'],[1,'v2']]
    var tickArray = $.map(tickLabelsArray, function(tick, index){
        return [[index, tick]];
    });

    //calculate which ticks should be rendered
    if(noOfWrittenLabels !== null) {
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
        if(noTicks !== null) {
            if(noTicks > 0) {
                moduloResult = index % noTicks;
            }
            else if(noTicks === 0) {
                moduloResult = 0;  
            }
        }
            
        //set tick value to axis.min -1 so that is will not be diaplayed in the chart
        if(moduloResult !== 0 || moduloResult === null ) {
            tick[0] =  axis.min -1;
        }
        //hack for executing the tickFormatter even if the value of a tick is being set
        if(tick[1]) {
            tick[1] = flotHelper.getTickFormatter(angle, tick[1], axis);
        }
    });
    
    return tickArray;
};

/**
 * Formats the text of the legend by setting the font family and color.
 * 
 * @param {string} label
 * @param {object} series
 * @param {string} fontFamily
 * @param {string} fontColor
 * @returns {undefined}
 */
flotHelper.formatLegendLabels = function(label, series, fontFamily, fontColor) {
    var font = fontFamily!==null?'font-family:'+fontFamily+';':"";
    var color = fontColor!==null?'color:'+fontColor+';':"";
    return '<div style="'+font+color+'">' + label + '</a>';

};