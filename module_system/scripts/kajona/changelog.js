/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * @module changelog
 */
define('changelog', ['jquery', 'ajax', 'moment', 'd3', 'calendar-heatmap'], function ($, ajax, moment, d3, calHeatmap) {

    /** @exports changelog */
    var changelog = {};

    /**
     * Method to compare and highlite changes of two version properties table
     */
    changelog.compareTable = function () {
        var strType = changelog.selectedColumn;
        var propsLeft = changelog.getTableProperties(strType);
        var propsRight = changelog.getTableProperties(changelog.getInverseColumn(strType));
        for (var key in propsLeft) {
            if (propsLeft[key] !== "" || propsRight[key] !== "") {
                if (propsLeft[key] !== propsRight[key]) {
                    $('#property_' + key + '_' + strType).parent().parent().css('background-color', '#CEC');
                } else {
                    $('#property_' + key + '_' + strType).parent().parent().css('background-color', '');
                }
            } else {
                $('#property_' + key + '_' + strType).parent().parent().css('background-color', '');
            }
        }
    };

    changelog.systemId = null;
    changelog.now = null;
    changelog.yearAgo = null;
    changelog.selectedColumn = null;
    changelog.lang = {};

    /**
     * Selects the column which should change if a user clicks on the chart
     *
     * @param {string} strType
     */
    changelog.selectColumn = function(strType){
        $('#date_' + strType).css("background-color", "#ccc");
        $('#date_' + changelog.getInverseColumn(strType)).css("background-color", "");
        changelog.selectedColumn = strType;
    };

    /**
     * Returns the opposite column of the provided type
     *
     * @param strType
     * @returns {string}
     */
    changelog.getInverseColumn = function(strType){
        return strType == "left" ? "right" : "left";
    };

    /**
     * Returns an object containing all version properties from either the left or right table
     *
     * @param {string} type
     * @returns {object}
     */
    changelog.getTableProperties = function (type) {
        var props = {};
        $('.changelog_property_' + type).each(function(){
            props[$(this).data('name')] = "" + $(this).html();
        });
        return props;
    };

    /**
     * Loads the version properties for a specific date and inserts the values either in the left or right table
     *
     * @param {string} strSystemId
     * @param {string} strDate
     * @param {string} strType
     * @param {function} objCallback
     */
    changelog.loadDate = function (strSystemId, strDate, strType, objCallback) {
        $('#date_' + strType).html("");
        $('.changelog_property_' + strType).html("");
        ajax.genericAjaxCall("system", "changelogPropertiesForDate", "&systemid="+strSystemId+"&date="+strDate, function(data, status, jqXHR) {
            data = JSON.parse(data);
            var props = data.properties;
            $('#date_' + strType).html("<a href='#' onclick='require(\"changelog\").selectColumn(\"" + strType + "\");return false;' style='display:block;'>" + data.date + "</a>");
            for (var prop in props) {
                $('#property_' + prop + '_' + strType).html(props[prop]);
            }

            $('#date_' + strType + ' a').qtip({
                content: changelog.lang.tooltipColumn,
                position: {
                    at: 'top center',
                    my: 'bottom center'
                },
                style: {
                    classes: 'qtip-bootstrap'
                }
            });

            if (typeof objCallback === "function") {
                objCallback.apply();
            }
        });
    };

    /**
     * Loads the chart for the next year
     */
    changelog.loadNextYear = function () {
        $('#changelogTimeline').fadeOut();

        changelog.now = moment(changelog.now).add(1, 'years').toDate();
        changelog.yearAgo = moment(changelog.yearAgo).add(1, 'years').toDate();
        changelog.loadChartData();
    };

    /**
     * Loads the chart for the previous year
     */
    changelog.loadPrevYear = function () {
        $('#changelogTimeline').fadeOut();

        changelog.now = moment(changelog.now).subtract(1, 'years').toDate();
        changelog.yearAgo = moment(changelog.yearAgo).subtract(1, 'years').toDate();
        changelog.loadChartData();
    };

    /**
     * Loads the chart
     */
    changelog.loadChartData = function () {
        var now = moment(changelog.now).format("YYYYMMDD235959");
        var yearAgo = moment(changelog.yearAgo).format("YYYYMMDD235959");
        var me = this;

        ajax.genericAjaxCall("system", "changelogChartData", "&systemid=" + changelog.systemId + "&now=" + now + "&yearAgo=" + yearAgo, function(data, status, jqXHR) {
            data = JSON.parse(data);
            var chartData = d3.time.days(me.yearAgo, me.now).map(function (dateElement) {
                var count = 0;
                if (data.hasOwnProperty(moment(dateElement).format("YYYYMMDD"))) {
                    count = data[moment(dateElement).format("YYYYMMDD")];
                }
                return {
                    date: dateElement,
                    count: count
                };
            });



            var heatmap = calHeatmap
                .data(chartData)
                .selector('#changelogTimeline')
                .months(changelog.lang.months)
                .days(changelog.lang.days)
                .width(700)
                .padding(16)
                .tooltipEnabled(true)
                .tooltipUnit(changelog.lang.tooltipUnit)
                .tooltipUnitPlural(changelog.lang.tooltipUnitPlural)
                .tooltipDateFormat("DD.MM.YYYY")
                .tooltipHtml(changelog.lang.tooltipHtml)
                .legendEnabled(false)
                .toggleDays(false)
                .colorRange(['#eeeeee', '#6cb121'])
                .onClick(function (data) {
                    var date = moment(data.date).format("YYYYMMDD235959");
                    changelog.loadDate(me.systemId, date, changelog.selectedColumn, changelog.compareTable);
                });
            heatmap(me.now, me.yearAgo);  // render the chart

            $('#changelogTimeline').fadeIn();
        });
    };

    return changelog;

});


