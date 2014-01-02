//   (c) 2013-2014 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: jquery.jqplot.custom_helper.js 6181 2013-11-29 15:03:15Z sidler $

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.dashboard = {

    removeWidget : function(strSystemid) {
        KAJONA.admin.ajax.genericAjaxCall('dashboard', 'deleteWidget', strSystemid, function(data, status, jqXHR) {
            if (status == 'success') {

                $("li[data-systemid="+strSystemid+"]").remove();
                KAJONA.admin.statusDisplay.displayXMLMessage(data);
                jsDialog_1.hide();

            } else {
                KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);
            }
        });

        return false;
    },

    init : function() {

        $('.adminwidgetColumn > li').each(function () {
            var systemId = $(this).data('systemid');
            KAJONA.admin.ajax.genericAjaxCall('dashboard', 'getWidgetContent', systemId, function(data, status, jqXHR) {

                content = $("li.dbEntry[data-systemid='"+systemId+"'] .content");

                if (status == 'success') {
                    var $parent = content.parent();
                    content.remove();

                    var $newNode = $("<div></div>").append($.parseJSON(data));
                    $parent.append($newNode);

                    //TODO use jquerys eval?
                    KAJONA.util.evalScript(data);
                    KAJONA.admin.tooltip.initTooltip();

                } else {
                    //KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);
                }
            });
        });

        $("ul.adminwidgetColumn").each(function(index) {

            $(this).sortable({
                items: 'li.dbEntry',
                handle: 'h2',
                forcePlaceholderSize: true,
                cursor: 'move',
                connectWith: '.adminwidgetColumn',
                placeholder: 'dashboardPlaceholder',
                stop: function(event, ui) {
                    //search list for new pos
                    var intPos = 0;
                    $(".dbEntry").each(function(index) {
                        intPos++;
                        if($(this).data("systemid") == ui.item.data("systemid")) {
                            KAJONA.admin.ajax.genericAjaxCall("dashboard", "setDashboardPosition", ui.item.data("systemid") + "&listPos=" + intPos+"&listId="+ui.item.closest('ul').attr('id'), KAJONA.admin.ajax.regularCallback)
                            return false;
                        }
                    });
                },
                delay: KAJONA.util.isTouchDevice() ? 2000 : 0
            }).find("h2").css("cursor", "move");
        });

    }
};
