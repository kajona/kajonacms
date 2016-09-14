
/**
 * Dashboard calendar functions
 */
define(["jquery"], function($){

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