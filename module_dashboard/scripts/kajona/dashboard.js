//   (c) 2013-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

define(["jquery", "jquery-ui", "ajax", "statusDisplay", "tooltip", "util"], function($, jqueryui, ajax, statusDisplay, tooltip, util){

    var dashboard = {};

    dashboard = {

        removeWidget : function(strSystemid) {
            ajax.genericAjaxCall('dashboard', 'deleteWidget', strSystemid, function(data, status, jqXHR) {
                if (status == 'success') {

                    $("div[data-systemid="+strSystemid+"]").remove();
                    statusDisplay.displayXMLMessage(data);
                    jsDialog_1.hide();

                } else {
                    statusDisplay.messageError('<b>Request failed!</b><br />' + data);
                }
            });
        },

        init : function() {

            $('.adminwidgetColumn > div.dbEntry').each(function () {
                var systemId = $(this).data('systemid');
                ajax.genericAjaxCall('dashboard', 'getWidgetContent', systemId, function(data, status, jqXHR) {

                    content = $("div.dbEntry[data-systemid='"+systemId+"'] .content");

                    if (status == 'success') {
                        var $parent = content.parent();
                        content.remove();

                        var $newNode = $("<div class='content'></div>").append($.parseJSON(data));
                        $parent.append($newNode);

                        //TODO use jquerys eval?
                        util.evalScript(data);
                        tooltip.initTooltip();

                    } else {
                        //statusDisplay.messageError('<b>Request failed!</b><br />' + data);
                    }
                });
            });

            $("div.adminwidgetColumn").each(function(index) {

                $(this).sortable({
                    items: 'div.dbEntry',
                    handle: 'h2',
                    forcePlaceholderSize: true,
                    cursor: 'move',
                    connectWith: '.adminwidgetColumn',
                    placeholder: 'dndPlaceholder',
                    stop: function(event, ui) {
                        ui.item.removeClass("sortActive");
                        //search list for new pos
                        var intPos = 0;
                        $(".dbEntry").each(function(index) {
                            intPos++;
                            if($(this).data("systemid") == ui.item.data("systemid")) {
                                ajax.genericAjaxCall("dashboard", "setDashboardPosition", ui.item.data("systemid") + "&listPos=" + intPos+"&listId="+ui.item.closest('div.adminwidgetColumn').attr('id'), ajax.regularCallback);
                                return false;
                            }
                        });
                    },
                    delay: util.isTouchDevice() ? 500 : 0,
                    start: function(event, ui) {
                        ui.item.addClass("sortActive");
                    }
                }).find("h2").css("cursor", "move");
            });

        }
    };

    dashboard.todo = {

        selectedCategory: "",

        loadCategory: function(category, search){
            if (search == '') {
                $('#listfilter_search').val('');
            }
            this.selectedCategory = category;
            $('#todo-table').html('<div class="loadingContainer"></div>');
            ajax.genericAjaxCall('dashboard', 'todoCategory', '&category=' + category + '&search=' + search, function(data) {
                $('#todo-table').html(data);
                tooltip.initTooltip();
            });
        },

        formSearch: function(){
            this.loadCategory(this.selectedCategory, $('#listfilter_search').val());
        }

    };



    return dashboard;

});


