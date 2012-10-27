/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var flotHelper = {};

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
        top: y + 5,
        left: x + 5,
        border: '2px solid '+color,
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
};

flotHelper.doToolTip = function(event, pos, item) {
    $('#x').text(pos.x.toFixed(3));
    $('#y').text(pos.y.toFixed(3));


    if (item) {
        if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;

            $('#tooltip').remove();
            var x = item.datapoint[0].toFixed(3),
            y = item.datapoint[1].toFixed(3),
            color = item.series.color,
            conten = '';
 
            if(item.series.xaxis.ticks) {
                var tick = item.series.xaxis.ticks[item.dataIndex].label;
                x = tick;
                content = '<b>'+item.series.label+'</b>'
                +'<br/>' + x + ' = ' + y, color;
            }
            else {
                content = '<b>'+item.series.label+'</b>'
                +'<br/>'
                +'x = ' + x
                +'<br/>'
                +'y = ' + y
            }
            flotHelper.showTooltip(item.pageX, item.pageY, content, color);
        }
    }
    else {
        $('#tooltip').remove();
        previousPoint = null;            
    }
}


