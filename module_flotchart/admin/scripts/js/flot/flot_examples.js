/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function plotCharts() {
    plotPieChart();
    plotPieChartLegend();
    plotBarChart();
    plotStackedBarChart();
    plotLineChart();
    plotLineBarChart();
    
}

function plotPieChart() {
    var dataArray = [2,6,7,3];
    
    //piechart
    var dataPie = dataArray.map(function(date) {return {data:date};})
    var plotOptions = {
        series: {
            pie: { 
                show: true
            }
        }
    }
    $.plot($("#piechart"), dataPie, plotOptions);
}


/**
 * - Pie Chart with legend, 
 * - Legend with hyperlinks
 */
function plotPieChartLegend() {
    var dataArray = [2,6,7,3];

    // piechart with legend
    var dataPie = dataArray.map(function(date, i) {return {label: "val"+(i+1), data: date};})
    var plotOptions = {
        series: {
            pie: { 
                show: true
            }
        },//end series
        legend: {
            show:true,
            labelFormatter: function(label, series) {
            // series is the series object for the label
            return '<a href="http://www.google.de" target="_blank">' + label + '</a>';
            }
        }//end legend
    }
    
    $.plot($("#piechart_legend"), dataPie, plotOptions);
}



function plotBarChart() {
    //barchart
    var bar_data = [[0, 3], [2, 8], [4, 5], [6, 13], [8, 13], [10, 12], [12, 20], [14, 13], [16, 3], [18, 8], [20, 13], [22, 40]];
    var jahresZeiten = ['Jan', 'Feb', 'Mrz', 'Aprl', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    var dataBar = {data: bar_data, bars:{show: true, barWidth:0.5, align: 'center'}}
    var plotOptions = {
       xaxis: {
         ticks: [
           [0, 'Jan'],[2, 'Feb'],[4, 'Mar'],[6, 'Apr'],
           [8, 'May'],[10, 'Jun'],[12, 'Jul'],[14, 'Aug'],
           [16, 'Sep'],[18, 'Oct'],[20, 'Nov'],[22, 'Dez']
         ]
       }   
     }
     
    $.plot($("#barchart"), [dataBar], plotOptions);
}


function plotStackedBarChart() {
    var series1 = [8,-5,7,8,4,12];
    var series2 = [3,-4,6,2,5, 2];
    var ticks_ =  [];
    
    series1 = series1.map(function(date, i){return [i, date]});
    series2 = series2.map(function(date, i){return [i, date]});
    ticks_ = series1.map(function(date, i){return [i, "v"+i]});
    
    var plotOptions = {
        series: { 
            stack:true,
            //lines: { show: false, fill: true, steps: false },
            bars:{show:true, barWidth:0.5, align: 'center'}
        },
        xaxis:{
            ticks:ticks_
        }
        
    };
    $.plot($("#stackedbarchart"), [series1, series2], plotOptions);
}

function plotLineChart() {
    
    var series1 = [8,5,7,8,4,12,10,11,9];
    var series2 = [3,4,6,2,5,2 ,5, 3, 4];
    series1 = series1.map(function(date, i){return [i, date]});
    series2 = series2.map(function(date, i){return [i, date]});
    
    var plotOptions = {
        series: {
            lines: {show:true},
            points: {show:true}
        },
        xaxis: {
            show:true,
            ticks:[[0,"v1"],[1,"v2"], [2,"v3"], [3,"v4"], [4,"v5"], [5,"v6"], [6,"v7"], [7,"v8"], [8,"v9"]]
        },
        grid: {
            hoverable: true, clickable: true
        }
        
    }
    $("#linechart").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2);
            var y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,item.series.label + " of " + x + " = " + y);  
    });
    
    $.plot($("#linechart"), [{data:series1, label:"series1"}, {data:series2, label:"series2"}], plotOptions);
    
    
}

function plotLineBarChart() {
    
    var series1 = [8,5,7,8,4,12,10,11,9];
    var series2 = [3,4,6,2,5,2 ,5, 3, 4];
    var series3 = [[0, 3], [4, 8], [8, 5], [9, 13]];
    series1 = series1.map(function(date, i){return [i, date]});
    series2 = series2.map(function(date, i){return [i, date]});
    
    
    var plotOptions = {
        series: {
            lines: {show:true},
            points: {show:true}
        },
        xaxis: {
            show:true,
            ticks:[[0,"v1"],[1,"v2"], [2,"v3"], [3,"v4"], [4,"v5"], [5,"v6"], [6,"v7"], [7,"v8"], [8,"v9"]]
        },
        grid: {
            hoverable: true, clickable: true
        }
        
    }
    $("#linebarchart").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(2);
            var y = item.datapoint[1].toFixed(2);
            showTooltip(item.pageX, item.pageY,item.series.label + " of " + x + " = " + y);  
    });
    
    $.plot($("#linebarchart"), [{data:series1, label:"series1"}, {data:series2, label:"series2"}, {data: series3, label:"series3", bars: { show: true }}], plotOptions);
    
    
}

function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }


