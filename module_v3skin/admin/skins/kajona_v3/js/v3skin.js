//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2012 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 4832 2012-07-31 10:20:38Z sidler $



/**
 * Calendar functions
 */
KAJONA.admin.calendar = {};
KAJONA.admin.calendar.showCalendar = function(strCalendarId, strCalendarContainerId, objButton) {
    KAJONA.util.fold(strCalendarContainerId, function() {
        //positioning the calendar container
        var btnRegion = YAHOO.util.Region.getRegion(objButton);
        YAHOO.util.Dom.setStyle(strCalendarContainerId, "left", btnRegion.left+"px");

        //show nice loading animation while loading the calendar files
        YAHOO.util.Dom.addClass(strCalendarContainerId, "loadingContainer");


        KAJONA.admin.loader.loadFile([
            "/core/module_v3skin/admin/skins/kajona_v3/js/yui/calendar/calendar-min.js",
            "/core/module_v3skin/admin/skins/kajona_v3/js/yui/calendar/assets/calendar.css"
        ], function() {
            KAJONA.admin.calendar.initCalendar(strCalendarId, strCalendarContainerId);
            YAHOO.util.Dom.removeClass(strCalendarContainerId, "loadingContainer");
        });
    });
};

KAJONA.admin.calendar.initCalendar = function(strCalendarId, strCalendarContainerId) {
    var calendar = new YAHOO.widget.Calendar(strCalendarContainerId);
    calendar.cfg.setProperty("WEEKDAYS_SHORT", KAJONA.admin.lang.toolsetCalendarWeekday);
    calendar.cfg.setProperty("MONTHS_LONG", KAJONA.admin.lang.toolsetCalendarMonth);
    calendar.cfg.setProperty("START_WEEKDAY", 1);

    var handleSelect = function(type, args, obj) {
        var dates = args[0];
        var date = dates[0];
        var year = date[0], month = (date[1] < 10 ? '0'+date[1]:date[1]), day = (date[2] < 10 ? '0'+date[2]:date[2]);
        //write to fields
        document.getElementById(strCalendarId+"_day").value = day;
        document.getElementById(strCalendarId+"_month").value = month;
        document.getElementById(strCalendarId+"_year").value = year;

        //disabled because of JS error: this.config is null
        //calendar.destroy();
        KAJONA.util.fold(strCalendarContainerId);
    };

    //check for values in date form
    var formDate = [document.getElementById(strCalendarId+"_day").value, document.getElementById(strCalendarId+"_month").value, document.getElementById(strCalendarId+"_year").value];
    if (formDate[0] > 0 && formDate[1] > 0 && formDate[2] > 0) {
        calendar.select(formDate[1]+'/'+formDate[0]+'/'+formDate[2]);

        var selectedDates = calendar.getSelectedDates();
        if (selectedDates.length > 0) {
            var firstDate = selectedDates[0];
            calendar.cfg.setProperty("pagedate", (firstDate.getMonth()+1) + "/" + firstDate.getFullYear());
        }
    }

    calendar.selectEvent.subscribe(handleSelect, calendar, true);
    calendar.render();
};