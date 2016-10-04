/**
 * Created with JetBrains PhpStorm.
 * User: smy
 * Date: 10.10.13
 * Time: 10:31
 * To change this template use File | Settings | File Templates.
 */


function test() {
    $(document).ready(function () {
        var data = [[1, 1], [2, 2], [3, 3]];

        $.jqplot.postDrawHooks.push( function ()
        {
            $( ".jqplot-overlayCanvas-canvas" ).css( 'z-index', '0' ); //send overlay canvas to back
            $( ".jqplot-series-canvas" ).css( 'z-index', '1' ); //send series canvas to front
        } );

        $.jqplot( 'ChartDIV', [data],
            {
                series: [{ showMarker: true}],
                seriesDefaults: { showMarker: true, pointLabels: { show: true} },
                highlighter: {
                    sizeAdjust: 10,
                    show: true,
                    tooltipLocation: 'n',
                    useAxesFormatters: true
                },

                tickOptions: {
                    formatString: '%d'
                },
                canvasOverlay: {
                    show: true,
                    objects: [
                        {
                            horizontalLine:
                            {
                                y: 1,
                                color: 'rgba(255, 0, 0,0.45)'
                            }
                        }
                    ]
                },
                axes: {

                }
            } );




    });
}

function test2() {
    $(document).ready(function () {
        //var data = [[2, 4, 6, 3], [5, 1, 3, 4], [4, 7, 1, 2]];
        var data = [
                    [[2,"v1"], [4,"v2"], [6,"v3"], [3,"v4"]],
                    [[5,"v1"], [1,"v2"], [3, "v3"], [4, "v4"]],
                    [[4,"v1"], [7,"v2"], [1, "v3"], [2, "v4"]]
                ];

        $.jqplot('ChartDIV', data,
            {
                "seriesColors": ["#8bbc21", "#2f7ed8", "#f28f43", "#1aadce", "#77a1e5", "#0d233a", "#c42525", "#a6c96a", "#910000"],
                "axesDefaults": {
                    "labelOptions": {"fontFamily": "open sans"},
                    "tickOptions": {"fontFamily": "open sans"}
                },
                "seriesDefaults": {"useNegativeColors": false, "rendererOptions": {"animation": {"show": true, "speed": 1000}, "barDirection": "horizontal"}, "renderer": $.jqplot.BarRenderer},
                "axes": {
                    "xaxis": {"label": "My new X-Axis", "tickOptions": {"showGridline": false}},
                    "yaxis": {"renderer": $.jqplot.CategoryAxisRenderer, "label": "My new Y-Axis",
                         "tickOptions": {"showGridline": true}}
                },
                "series": [{
                    "renderer": $.jqplot.BarRenderer,
                    "rendererOptions": {"fillToZero": true, "highlightMouseOver": false, "shadow": false},
                    "label": "serie 9",
                    "pointLabels": {"show": true, "hideZeros": true}
                }, {
                    "renderer": $.jqplot.BarRenderer,
                    "rendererOptions": {"fillToZero": true, "highlightMouseOver": false, "shadow": false},
                    "label": "serie 10",
                    "pointLabels": {"show": true, "hideZeros": true}
                }, {
                    "renderer": $.jqplot.BarRenderer,
                    "rendererOptions": {"fillToZero": true, "highlightMouseOver": false, "shadow": false},
                    "label": "serie 11",
                    "pointLabels": {"show": true, "hideZeros": true}
                }],
                "fontFamily": "open sans"
            }
        );
    });
}

//test();
test2();
