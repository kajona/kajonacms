
<div class="chart-navigation pull-left"><a href="#" onclick="KAJONA.admin.changelog.loadPrevYear();return false;"><i class="kj-icon fa fa-arrow-left"></i></a></div>
<div class="chart-navigation pull-right"><a href="#" onclick="KAJONA.admin.changelog.loadNextYear();return false;"><i class="kj-icon fa fa-arrow-right"></i></a></div>
<div id='changelogTimeline' style='text-align:center;'></div>

<script type="text/javascript">
    KAJONA.admin.loader.loadFile([
        '/core/module_system/admin/scripts/moment/moment.min.js',
        '/core/module_system/admin/scripts/d3/d3.min.js',
        '/core/module_system/admin/scripts/d3/calendar-heatmap.js',
        '/core/module_system/admin/scripts/d3/calendar-heatmap.css'], function() {

        KAJONA.admin.changelog.systemId = "%%strSystemId%%";
        KAJONA.admin.changelog.now = moment().endOf('day').toDate();
        KAJONA.admin.changelog.yearAgo = moment().startOf('day').subtract(1, 'year').toDate();
        KAJONA.admin.changelog.selectColumn("right");
        KAJONA.admin.changelog.loadChartData();

        KAJONA.admin.changelog.loadDate("%%strSystemId%%", "%%strLeftDate%%", "left", function(){
            KAJONA.admin.changelog.loadDate("%%strSystemId%%", "%%strRightDate%%", "right", KAJONA.admin.changelog.compareTable);
        });

    });
</script>
<style type="text/css">
    .chart-navigation {
        width:20px;
        height:110px;
        margin-bottom:38px;
    }

    .chart-navigation a {
        display:block;
        height:110px;
        padding-top:45px;
        text-align:center;
        background-color:#f9f9f9;
    }

    .chart-navigation a:hover {
        background-color:#e7e7e7;
    }

    .day-cell {
        cursor: pointer;
    }
</style>
