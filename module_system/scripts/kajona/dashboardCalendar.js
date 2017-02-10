/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Dashboard calendar functions
 *
 * @module dashboardCalendar
 */
define("dashboardCalendar", ["jquery"], function($){

    /** @exports dashboardCalendar */
    var dashboardCalendar = {};
    dashboardCalendar.eventMouseOver = function(strSourceId) {
        if(strSourceId == "")
            return;

        var sourceArray = eval("kj_cal_"+strSourceId);
        if(typeof sourceArray != undefined) {
            for(var i=0; i< sourceArray.length; i++) {
                $("#event_"+sourceArray[i]).addClass("mouseOver");
            }
        }
    };

    dashboardCalendar.eventMouseOut = function(strSourceId) {
        if(strSourceId == "")
            return;

        var sourceArray = eval("kj_cal_"+strSourceId);
        if(typeof sourceArray != undefined) {
            for(var i=0; i< sourceArray.length; i++) {
                $("#event_"+sourceArray[i]).removeClass("mouseOver");
            }
        }
    };

    return dashboardCalendar;

});