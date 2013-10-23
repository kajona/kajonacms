/**
 * Created with JetBrains PhpStorm.
 * User: smy
 * Date: 10.10.13
 * Time: 10:31
 * To change this template use File | Settings | File Templates.
 */



function highlighter_tooltipContentEditor(str, seriesIndex, pointIndex, plot) {
//    var series = plot.series[seriesIndex];
//    var seriesLabel = series["label"];
//
//    return plot.data[seriesIndex][pointIndex].join("=");
    return str;
}

function test2() {
    $(document).ready(function () {
        $.jqplot('chart_f2a8bff525e503a582a3', [
            [8.112, 1, 2, 4],
            [1, 2, 3, 4],
            [4, 7, 1, 2],
            [4, 3, 2, 1],
            [-5, 3, -2, 1]
        ], {"title": {"text": "My First Line Chart", "rendererOptions": {"textColor": "#FF0000", "fontFamily": "Verdana"}},
            "highlighter": {"show": false, "bringSeriesToFront": false, "showMarker": false}, "legend": {"renderer": $.jqplot.EnhancedLegendRenderer, "rowSpacing": "0px", "show": true, "rendererOptions": {"textColor": "#FF0000", "fontFamily": "Verdana"}},
            "grid": {"background": "#F0F0F0"},
            "axesDefaults": {"tickRenderer": $.jqplot.CanvasAxisTickRenderer, "labelRenderer": $.jqplot.CanvasAxisLabelRenderer, "labelOptions": {"textColor": "#FF0000", "fontFamily": "Verdana"}, "tickOptions": {"textColor": "#FF0000", "fontFamily": "Verdana"}},
            "axes": {"xaxis": {"renderer": $.jqplot.CategoryAxisRenderer, "label": "XXX", "ticks": ["v1", "v2", "v3", "v4"], "tickOptions": {"angle": -20}}, "yaxis": {"label": "YYY"}}, "series": [
                {"renderer": $.jqplot.LineRenderer, "rendererOptions": {"highlightMouseOver": true}, "pointLabels": {"show": false}},
                {"renderer": $.jqplot.LineRenderer, "rendererOptions": {"highlightMouseOver": true}, "pointLabels": {"show": false}},
                {"renderer": $.jqplot.LineRenderer, "rendererOptions": {"highlightMouseOver": true}, "pointLabels": {"show": false}},
                {"renderer": $.jqplot.LineRenderer, "rendererOptions": {"highlightMouseOver": true}, "pointLabels": {"show": false}},
                {"renderer": $.jqplot.LineRenderer, "rendererOptions": {"highlightMouseOver": true}, "pointLabels": {"show": false}}
            ], "textColor": "#FF0000", "fontFamily": "Verdana"});


        KAJONA.admin.jqplotHelper.setLabelsInvisible('chart_f2a8bff525e503a582a3', 1);

        $('#chart_f2a8bff525e503a582a3').bind('jqplotDataHighlight',
            function (ev, seriesIndex, pointIndex, data) {
                console.debug(data);
                //KAJONA.admin.jqplotHelper.highlightTooltip(ev, seriesIndex, pointIndex, data);
            }
        );
        $('#chart_f2a8bff525e503a582a3').bind('jqplotDataUnhighlight',
            function (ev) {
                KAJONA.admin.jqplotHelper.unHighlightTooltip(ev)
            }
        );
    });


}

//KAJONA.admin.jqplotHelper.showTooltip(100, 100, "content", "label", "#000000");

function plotLineChart() {
    $.jqplot('chartdiv_line',
        [
            [
                [2, 2],
                [3, 5.12],
                [5, 13.1],
                [7, 33.6],
                [9, 85.9],
                [11, 219.9]
            ]
        ], {
            title: 'Homofürst',
            highlighter: {
                show: true,
                //sizeAdjust: 7.5,
                tooltipAxes: 'both'
            },
            cursor: {
                show: false
            },
            axes: {
                xaxis: {
                    label: 'Angle (radians)'

                },
                yaxis: {
                    label: 'Cosine',
                    labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                    angle: 100
                }
            }
        }
    );
}

function plotLineChart2() {
    $(document).ready(function () {
        var cosPoints = [];
        for (var i = 0; i < 30; i += 0.1) {
            cosPoints.push([i, i]);
        }
        var plot1 = $.jqplot('chartdiv_line2', [cosPoints], {
            series: [
                {showMarker: false}
            ],
            axes: {
                xaxis: {
                    //numberTicks : 30,
                    //min:0,
                    //max:35

                }
            }
        });
    });
}

function plotLineChart3() {
    $.jqplot('chartdiv_line3',
        [
            [
                ['5.8', 7],
                ['6.8', 9],
                ['7.8', 15],
                ['8.8', 12],
                [' 9.8', 3],
                ['10.8', 6],
                ['11.8', 18]
            ]
        ], {
            title: 'Homofürst 3',
            axesDefaults: {
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                    angle: 0,
                    fontSize: '10pt'
                }
            },
            highlighter: {
                show: true
            },
            cursor: {
                show: false
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    numberTicks: 7
                }
            }
        }
    );
}


function plotBarChart() {
    $.jqplot('chartdiv_bar', [
        [
            [1, 2],
            [3, 5.12],
            [5, 13.1],
            [7, 33.6],
            [9, 85.9],
            [11, 219.9]
        ]
    ]);
}

function plotStackedBarChart() {
    $.jqplot('chartdiv_stackedbar',
        [
            //[[0,3670],[1,5034],[2,2685],[3,2794],[4,-790],[5,3800],[6,3290],[7,1300],[8,-1347],[9,1555],[10,4029],[11,1191]],
            //[[0,642],[1,749],[2,-121],[3,35],[4,-135.3],[5,-48],[6,489],[7,149],[8,11],[9,-112.5],[10,510],[11,349.5]]
            //[[0,0],[1,0],[2,0],[3,0]],
            [
                [1, 5],
                [2, 5],
                [3, 5],
                [4, 5]
            ],
            [
                [1, 6],
                [2, 6],
                [3, 7],
                [4, 8]
            ],
            [
                [1, -2],
                [2, 6],
                [3, 7],
                [4, 8]
            ],
            [
                [1, -5],
                [2, 6],
                [3, 7],
                [4, 8]
            ]
        ]
        , {
            // Tell the plot to stack the bars.
            stackSeries: true,
            series: [
                { label: 'USD' },
                { label: 'Other' }
            ],
            seriesDefaults: {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    highlightMouseDown: true,
                    barWidth: null,
                    fillToZero: true
                },
                pointLabels: { show: true, stackedValue: true }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: [1, 2, 3, 4],
                    showTicks: false
                },
                yaxis: {
                    min: -25,
                    tickOptions: {
                        formatString: "$%'d"
                    }
                },
                y2axis: {
                    autoscale: true,
                    min: 0
                }
            },
            legend: {
                show: true,
                location: 'e',
                placement: 'outside'
            },
            grid: {
                drawGridlines: true
            }
        });



}


function plotPieChart() {


}

function plotTest() {

//    $(document).ready(function () {
//        $.jqplot('chart_05e1d90525cecb819555', [
//            [
//                ["03\/2013", 54],
//                ["04\/2013", 77],
//                ["05\/2013", 12],
//                ["06\/2013", 38],
//                ["07\/2013", 58],
//                ["08\/2013", 37],
//                ["09\/2013", 53],
//                ["10\/2013", -20]
//            ]
//        ], {"grid": {"background": "#ffffff"},
//            "axesDefaults": {"tickRenderer": $.jqplot.CanvasAxisTickRenderer, "labelRenderer": $.jqplot.CanvasAxisLabelRenderer},
//            "axes": {"xaxis": {"renderer": $.jqplot.CategoryAxisRenderer}, "yaxis": {"label": "In Mio. EUR"}},
//            "series": [
//            {"renderer": $.jqplot.BarRenderer, "stackSeries": false  , "label": "Businessplaneffekt",rendererOptions: {
//                fillToZero: true
//            }, pointLabels:{show:true, ypadding: -5}}
//        ]});
//    });
//
//
//    $(document).ready(function () {
//        $.jqplot('chart_e13d2dc525cfc8787902', [
//            [
//                ["11\/2012", 115.8],
//                ["12\/2012", 112.9],
//                ["01\/2013", 111.7],
//                ["02\/2013", 109.9],
//                ["03\/2013", 108.7],
//                ["04\/2013", 107.4],
//                ["05\/2013", 106.8],
//                ["06\/2013", 106.1],
//                ["07\/2013", 105.4],
//                ["08\/2013", 105],
//                ["09\/2013", 104.7],
//                ["10\/2013", 102.3],
//                ["Ist (mit FX Effekt)", 106.3]
//            ],
//            [
//                ["11\/2012", 117],
//                ["12\/2012", 116],
//                ["01\/2013", 115],
//                ["02\/2013", 114],
//                ["03\/2013", 113],
//                ["04\/2013", 112],
//                ["05\/2013", 111],
//                ["06\/2013", 110],
//                ["07\/2013", 109],
//                ["08\/2013", 109],
//                ["09\/2013", 107],
//                ["10\/2013", 103],
//                ["Ist (mit FX Effekt)", null]
//            ]
//        ], {"grid": {"background": "#ffffff"},
//            "axesDefaults": {"tickRenderer": $.jqplot.CanvasAxisTickRenderer, "labelRenderer": $.jqplot.CanvasAxisLabelRenderer},
//            "axes": {"xaxis": {"renderer": $.jqplot.CategoryAxisRenderer}, "yaxis": {"label": "In Mrd. EUR"}},
//            "series": [
//            {"renderer": $.jqplot.BarRenderer, "pointLabels": {"show": true}, "rendererOptions": {"fillToZero": true}, "stackSeries": false, "label": "Ist"},
//            {"renderer": $.jqplot.BarRenderer, "pointLabels": {"show": true}, "rendererOptions": {"fillToZero": true}, "stackSeries": false, "label": "Plan"}
//        ]});
//    });

//    $('#chart_e13d2dc525cfc8787902').bind('jqplotDataHighlight', function (ev, sIndex, pIndex, data) {
//        alert("ydsf");
//        var chart_top = $('#chart').offset().top,
//            y = plot1.axes.yaxis.u2p(data[1]); // convert y axis units to pixels
//        $('#tooltip').css({ top: chart_top + y });
//    });




    $(document).ready(function() {
        var ins = [2, 2, 3, 5];
        var outs = [2, 4, 3, 5];
        var swaps = [2, 2, 6, 5];
        var passes = [2, 4, 6, 5];
        var data = [ins, outs, swaps, passes, [3, 3, 3, 3]];
        var series = [
            {
                label: 'IN',
                pointLabels: {
                    labels: [2, 2, 3, 5]
                }},
            {
                label: 'OUT',
                pointLabels: {
                    labels: [2, 4, 3, 5]
                }},
            {
                label: 'SWAP',
                pointLabels: {
                    labels: [2, 2, 6, 5]
                }},
            {
                label: 'PASS',
                pointLabels: {
                    labels: [2, 4, 6, 5]
                }},
            {
                label: 'INVISIBLE',
                pointLabels: {
                    labels: ['∑ 8', '∑ 12', '∑ 18', '∑ 20']
                },
                show: false,
                shadowAngle: 90,
                rendererOptions: {
                    shadowDepth: 25,
                    shadowOffset: 2.5,
                    shadowAlpha: 0.01
                }}
        ];
        var ticks = ['Oi', 'Bike', 'Car', 'Truck'];
        var plot = $.jqplot('chart', data, {
            stackSeries: true,
            seriesDefaults: {
                renderer: $.jqplot.BarRenderer,
                rendererOptions: {
                    barMargin: 20
                }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks
                },
                yaxis: {
                    padMin: 0,
                    autoscale: true,
                    tickOptions: {
                        formatString: '%d'
                    }
                }
            },
            legend: {
                show: true,
                location: 'ne',
                placement: 'outside'
            },
            series: series,
            title: "Oi Oi Title"
        });
        //color used for tooltip title
        var color = 'rgb(50%,50%,100%)';
        //start span of a tooltip's title
        var spanStart = '<span style="font-size:14px;font-weight:bold;color:' + color + ';">';
        $('#chart').bind('jqplotHighlighterHighlight', function(ev, seriesIndex, pointIndex, data) {console.debug("adasdadsdas")});
        $('#chart').bind('jqplotDataHighlight', function(ev, seriesIndex, pointIndex, data) {
            console.debug('afsddfs');
//            debugger;
            var chart_left = $('#chart').offset().left;
            var chart_top = $('#chart').offset().top;
            var x = ev.pageX;
            var y = ev.pageY;
            var left = x;
            var top = y;
            var chartTooltipHTML = spanStart;
            if (plot.axes.xaxis.u2p && plot.axes.yaxis.u2p) { // pierenderer do not have u2p
                left = chart_left + plot.axes.xaxis.u2p(data[0]); // convert x axis units to pixels on grid
                top = chart_top + plot.axes.yaxis.u2p(data[1]); // convert y axis units to pixels on grid
            }
            //console.log("plot.series[0].barDirection = "+plot.series[0].barDirection);
            if (plot.series[0].barDirection === "vertical") left -= plot.series[0].barWidth / 2;
            else if (plot.series[0].barDirection === "horizontal") top -= plot.series[0].barWidth / 2;
            //for stacked chart
            top = chart_top;
            var sum = 0;
            for (var i = 0; i < seriesIndex + 1; i++)
                sum += plot.series[i].data[pointIndex][1];
            top += plot.axes.yaxis.u2p(sum); //(data[1]);
            var seriesName = plot.series[seriesIndex].label;
            console.log("seriesName = " + seriesName + "   seriesIndex = " + seriesIndex + "   pointIndex= " + pointIndex + "  plot.series[seriesIndex].data=" + plot.series[seriesIndex].data[pointIndex]);

            chartTooltipHTML += 'My custom tooltip: </span>' + '<br/><b>Count:</b> ' + data[1] //data[1] has count of movements
                + '<br/><b>Movement type:</b> ' + seriesName;

            // chartTooltipHTML += "Default tooltip</span><br/><b>Data array is:</b> " + data;
            var ct = $('#chartTooltip');
            ct.css({
                left: left,
                top: top
            }).html(chartTooltipHTML).show();
            if (plot.series[0].barDirection === "vertical") {
                var totalH = 100;//ct.height() + ct.padding().top + ct.padding().bottom + ct.border().top + ct.border().bottom;
                ct.css({
                    top: top - totalH
                });
            }
        });
        // Bind a function to the unhighlight event to clean up after highlighting.
        $('#chart').bind('jqplotDataUnhighlight', function(ev, seriesIndex, pointIndex, data) {
            $('#chartTooltip').empty().hide();
        });
    });



}

//
//plotLineChart();
//plotLineChart2();
//plotLineChart3();
//plotBarChart();
//plotStackedBarChart();
//plotPieChart();
//plotTest();
//test2();
