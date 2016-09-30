
define(['jquery', 'ajax', 'moment'], function ($, ajax, moment) {

    var changelog = {};

    /**
     * Method to compare and highlite changes of two version properties table
     */
    changelog.compareTable = function () {
        var strType = this.selectedColumn;
        var propsLeft = this.getTableProperties(strType);
        var propsRight = this.getTableProperties(this.getInverseColumn(strType));
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
        $('#date_' + this.getInverseColumn(strType)).css("background-color", "");
        this.selectedColumn = strType;
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
            $('#date_' + strType).html("<a href='#' onclick='KAJONA.admin.changelog.selectColumn(\"" + strType + "\");return false;' style='display:block;'>" + data.date + "</a>");
            for (var prop in props) {
                $('#property_' + prop + '_' + strType).html(props[prop]);
            }

            $('#date_' + strType + ' a').qtip({
                content: this.lang.tooltipColumn,
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

        this.now = moment(this.now).add(1, 'years').toDate();
        this.yearAgo = moment(this.yearAgo).add(1, 'years').toDate();
        this.loadChartData();
    };

    /**
     * Loads the chart for the previous year
     */
    changelog.loadPrevYear = function () {
        $('#changelogTimeline').fadeOut();

        this.now = moment(this.now).subtract(1, 'years').toDate();
        this.yearAgo = moment(this.yearAgo).subtract(1, 'years').toDate();
        this.loadChartData();
    };

    /**
     * Loads the chart
     */
    changelog.loadChartData = function () {
        var now = moment(this.now).format("YYYYMMDD235959");
        var yearAgo = moment(this.yearAgo).format("YYYYMMDD235959");
        var me = this;

        ajax.genericAjaxCall("system", "changelogChartData", "&systemid=" + this.systemId + "&now=" + now + "&yearAgo=" + yearAgo, function(data, status, jqXHR) {
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

            var heatmap = calendarHeatmap()
                .data(chartData)
                .selector('#changelogTimeline')
                .months(this.lang.months)
                .days(this.lang.days)
                .width(700)
                .padding(16)
                .tooltipEnabled(true)
                .tooltipUnit(this.lang.tooltipUnit)
                .tooltipUnitPlural(this.lang.tooltipUnitPlural)
                .tooltipDateFormat("DD.MM.YYYY")
                .tooltipHtml(this.lang.tooltipHtml)
                .legendEnabled(false)
                .toggleDays(false)
                .colorRange(['#eeeeee', '#6cb121'])
                .onClick(function (data) {
                    var date = moment(data.date).format("YYYYMMDD235959");
                    this.loadDate(me.systemId, date, this.selectedColumn, this.compareTable);
                });
            heatmap(me.now, me.yearAgo);  // render the chart

            $('#changelogTimeline').fadeIn();
        });
    };

    return changelog;

});


