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

test();
