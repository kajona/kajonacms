
// based on https://github.com/DKirwan/calendar-heatmap


define(['jquery', 'moment', 'd3'], function ($, moment, d3) {

    var calendarHeatmap = function() {
        // defaults
        var width = 750;
        var height = 110;
        var padding = 32;
        var legendWidth = 150;
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var days = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        var selector = 'body';
        var SQUARE_LENGTH = 11;
        var SQUARE_PADDING = 2;
        var MONTH_LABEL_PADDING = 6;
        var data = [];
        var colorRange = ['#D8E6E7', '#218380'];
        var tooltipEnabled = true;
        var tooltipHtml = '<span><strong>%count% %unit%</strong> on %date%</span>';
        var tooltipUnit = 'contribution';
        var tooltipUnitPlural = 'contributions';
        var tooltipDateFormat = 'ddd, MMM Do YYYY';
        var legendEnabled = true;
        var toggleDays = true;
        var onClick = null;

        // setters and getters
        chart.data = function (value) {
            if (!arguments.length) {
                return data;
            }
            data = value;
            return chart;
        };

        chart.selector = function (value) {
            if (!arguments.length) {
                return selector;
            }
            selector = value;
            return chart;
        };

        chart.colorRange = function (value) {
            if (!arguments.length) {
                return colorRange;
            }
            colorRange = value;
            return chart;
        };

        chart.width = function (value) {
            if (!arguments.length) {
                return width;
            }
            width = value;
            return chart;
        };

        chart.height = function (value) {
            if (!arguments.length) {
                return height;
            }
            height = value;
            return chart;
        };

        chart.padding = function (value) {
            if (!arguments.length) {
                return padding;
            }
            padding = value;
            return chart;
        };

        chart.months = function (value) {
            if (!arguments.length) {
                return months;
            }
            months = value;
            return chart;
        };

        chart.days = function (value) {
            if (!arguments.length) {
                return days;
            }
            days = value;
            return chart;
        };

        chart.tooltipEnabled = function (value) {
            if (!arguments.length) {
                return tooltipEnabled;
            }
            tooltipEnabled = value;
            return chart;
        };

        chart.tooltipHtml = function (value) {
            if (!arguments.length) {
                return tooltipHtml;
            }
            tooltipHtml = value;
            return chart;
        };

        chart.tooltipUnit = function (value) {
            if (!arguments.length) {
                return tooltipUnit;
            }
            tooltipUnit = value;
            return chart;
        };

        chart.tooltipUnitPlural = function (value) {
            if (!arguments.length) {
                return tooltipUnitPlural;
            }
            tooltipUnitPlural = value;
            return chart;
        };

        chart.tooltipDateFormat = function (value) {
            if (!arguments.length) {
                return tooltipDateFormat;
            }
            tooltipDateFormat = value;
            return chart;
        };

        chart.legendEnabled = function (value) {
            if (!arguments.length) {
                return legendEnabled;
            }
            legendEnabled = value;
            return chart;
        };

        chart.toggleDays = function (value) {
            if (!arguments.length) {
                return toggleDays;
            }
            toggleDays = value;
            return chart;
        };

        chart.onClick = function (value) {
            if (!arguments.length) {
                return onClick();
            }
            onClick = value;
            return chart;
        };

        function chart(nowDate, yearAgoDate) {
            var now = nowDate || moment().endOf('day').toDate();
            var yearAgo = yearAgoDate || moment().startOf('day').subtract(1, 'year').toDate();

            d3.select(chart.selector()).selectAll('svg.calendar-heatmap').remove(); // remove the existing chart, if it exists
            d3.select('body').selectAll('div.day-cell-tooltip').remove(); // remove existing tooltips

            var dateRange = d3.time.days(yearAgo, now); // generates an array of date objects within the specified range
            var monthRange = d3.time.months(moment(yearAgo).startOf('month').toDate(), now); // it ignores the first month if the 1st date is after the start of the month
            var firstDate = moment(dateRange[0]);
            var max = d3.max(chart.data(), function (d) {
                return d.count;
            }); // max data value

            // color range
            var color = d3.scale.linear()
                .range(chart.colorRange())
                .domain([0, max]);

            var tooltip;
            var dayRects;

            drawChart();

            function drawChart() {
                var svg = d3.select(chart.selector())
                    .append('svg')
                    .attr('width', width)
                    .attr('class', 'calendar-heatmap')
                    .attr('height', height)
                    .style('padding', padding + 'px');

                dayRects = svg.selectAll('.day-cell')
                    .data(dateRange);  //  array of days for the last yr

                dayRects.enter().append('rect')
                    .attr('class', 'day-cell')
                    .attr('width', SQUARE_LENGTH)
                    .attr('height', SQUARE_LENGTH)
                    .attr('fill', 'gray')
                    .attr('x', function (d, i) {
                        var cellDate = moment(d);
                        var result = cellDate.week() - firstDate.week() + (firstDate.weeksInYear() * (cellDate.weekYear() - firstDate.weekYear()));
                        return result * (SQUARE_LENGTH + SQUARE_PADDING);
                    })
                    .attr('y', function (d, i) {
                        return MONTH_LABEL_PADDING + d.getDay() * (SQUARE_LENGTH + SQUARE_PADDING);
                    });

                if (typeof onClick === 'function') {
                    dayRects.on('click', function (d) {
                        var count = countForDate(d);
                        onClick({date: d, count: count});
                    });
                }

                if (chart.tooltipEnabled()) {
                    dayRects.on('mouseover', function (d, i) {
                        tooltip = d3.select(chart.selector())
                            .append('div')
                            .attr('class', 'day-cell-tooltip')
                            .html(tooltipHTMLForDate(d))
                            .style('left', function () {
                                return Math.floor(i / 7) * SQUARE_LENGTH + 'px';
                            })
                            .style('top', function () {
                                return d.getDay() * (SQUARE_LENGTH + SQUARE_PADDING) + MONTH_LABEL_PADDING * 3 + 'px';
                            });
                    })
                        .on('mouseout', function (d, i) {
                            tooltip.remove();
                        });
                }

                if (chart.legendEnabled()) {
                    var colorRange = [color(0)];
                    for (var i = 3; i > 0; i--) {
                        colorRange.push(color(max / i));
                    }

                    var legendGroup = svg.append('g');
                    legendGroup.selectAll('.calendar-heatmap-legend')
                        .data(colorRange)
                        .enter()
                        .append('rect')
                        .attr('class', 'calendar-heatmap-legend')
                        .attr('width', SQUARE_LENGTH)
                        .attr('height', SQUARE_LENGTH)
                        .attr('x', function (d, i) {
                            return (width - legendWidth) + (i + 1) * 13;
                        })
                        .attr('y', height + SQUARE_PADDING)
                        .attr('fill', function (d) {
                            return d;
                        });

                    legendGroup.append('text')
                        .attr('class', 'calendar-heatmap-legend-text')
                        .attr('x', width - legendWidth - 13)
                        .attr('y', height + SQUARE_LENGTH)
                        .text('Less');

                    legendGroup.append('text')
                        .attr('class', 'calendar-heatmap-legend-text')
                        .attr('x', (width - legendWidth + SQUARE_PADDING) + (colorRange.length + 1) * 13)
                        .attr('y', height + SQUARE_LENGTH)
                        .text('More');
                }

                dayRects.exit().remove();
                var monthLabels = svg.selectAll('.month')
                    .data(monthRange)
                    .enter().append('text')
                    .attr('class', 'month-name')
                    .style()
                    .text(function (d) {
                        return months[d.getMonth()];
                    })
                    .attr('x', function (d, i) {
                        var matchIndex = 0;
                        for (var j = 0; j < dateRange.length && d.getTime() > dateRange[j].getTime(); j++) {
                            if (dateRange[j].getDay() === 0) { // sunday
                                matchIndex++;
                            }
                        }
                        if (matchIndex == 0) {
                            return SQUARE_LENGTH * 4 * -1;
                        }
                        return matchIndex * (SQUARE_LENGTH + SQUARE_PADDING);
                    })
                    .attr('y', 0);  // fix these to the top

                days.forEach(function (day, index) {
                    if (toggleDays) {
                        if (index % 2) {
                            svg.append('text')
                                .attr('class', 'day-initial')
                                .attr('transform', 'translate(-8,' + (SQUARE_LENGTH + SQUARE_PADDING) * (index + 1) + ')')
                                .style('text-anchor', 'middle')
                                .attr('dy', '2')
                                .text(day);
                        }
                    } else {
                        svg.append('text')
                            .attr('class', 'day-initial')
                            .attr('transform', 'translate(-8,' + (SQUARE_LENGTH + SQUARE_PADDING) * (index + 1) + ')')
                            .style('text-anchor', 'middle')
                            .attr('dy', '1')
                            .text(day);
                    }
                });
            }

            function tooltipHTMLForDate(d) {
                var dateStr = moment(d).format(tooltipDateFormat);
                var count = countForDate(d);
                var html = tooltipHtml;
                html = html.replace(/\%count\%/g, count);
                html = html.replace(/\%unit\%/g, count === 1 ? tooltipUnit : tooltipUnitPlural);
                html = html.replace(/\%date\%/g, dateStr);
                return html;
            }

            function countForDate(d) {
                var count = 0;
                var match = chart.data().find(function (element, index) {
                    return moment(element.date).isSame(d, 'day');
                });
                if (match) {
                    count = match.count;
                }
                return count;
            }

            var daysOfChart = chart.data().map(function (day) {
                return day.date.toDateString();
            });

            dayRects.filter(function (d) {
                return daysOfChart.indexOf(d.toDateString()) > -1;
            }).attr('fill', function (d, i) {
                return color(chart.data()[i].count);
            });
        }

        return chart;
    };



    // polyfill for Array.find() method
    /* jshint ignore:start */
    if (!Array.prototype.find) {
        Array.prototype.find = function (predicate) {
            if (this === null) {
                throw new TypeError('Array.prototype.find called on null or undefined');
            }
            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }
            var list = Object(this);
            var length = list.length >>> 0;
            var thisArg = arguments[1];
            var value;

            for (var i = 0; i < length; i++) {
                value = list[i];
                if (predicate.call(thisArg, value, i, list)) {
                    return value;
                }
            }
            return undefined;
        };
    }
    /* jshint ignore:end */


    return calendarHeatmap();



});
