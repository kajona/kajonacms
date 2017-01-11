//   (c) 2013-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

define(["jquery", "moment", "fullcalendar", "dashboard", "tooltip", "workingIndicator", "loader"], function ($, moment, fullcalendar, dashboard, tooltip, workingIndicator, loader) {


    return {

        init: function () {

            var strLang = KAJONA_LANGUAGE ? KAJONA_LANGUAGE : 'en';

            require(["fullcalendar_lang_" + strLang], function () {


                loader.loadFile(['/core/module_dashboard/scripts/fullcalendar/fullcalendar.min.css']);


                $('#dashboard-calendar').fullCalendar({
                    header: {
                        left: 'prev,next',
                        center: 'title',
                        right: ''
                    },
                    editable: false,
                    theme: false,
                    lang: KAJONA_LANGUAGE,
                    eventLimit: true,
                    events: KAJONA_WEBPATH + '/xml.php?admin=1&module=dashboard&action=getCalendarEvents',
                    eventRender: function (event, el) {
                        tooltip.addTooltip(el, event.tooltip);
                        if (event.icon) {
                            el.find("span.fc-title").prepend(event.icon);
                        }
                    },
                    loading: function (isLoading) {
                        if (isLoading) {
                            workingIndicator.start();
                        } else {
                            workingIndicator.stop();
                        }
                    }
                });
                $('.fc-button-group').removeClass().addClass('btn-group');
                $('.fc-button').removeClass().addClass('btn btn-default');


            });
        }
    };

});


