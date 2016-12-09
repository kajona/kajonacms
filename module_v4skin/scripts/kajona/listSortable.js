//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

define(["jquery", "jquery-ui", "ajax", "statusDisplay", "tooltip", "util"], function($, jqueryui, ajax, statusDisplay, tooltip, util)  {

    /**
     * Creates a new sortable list, fully featured with next / previous page drop-zones
     *
     * @type {{init: sortManager.init}}
     */
    var sortManager = {

        /**
         * Initializes a new sort-manager
         *
         * @param strListId
         * @param strTargetModule
         * @param bitMoveToTree
         */
        init: function(strListId, strTargetModule, bitMoveToTree) {

            var $objListNode = $("#"+strListId);

            var oldPos = null;
            var intCurPage = $objListNode.attr("data-kajona-pagenum");
            var intElementsPerPage = $objListNode.attr("data-kajona-elementsperpage");

            $('#'+strListId+'_prev').sortable({
                placeholder: 'dndPlaceholder',
                over: function (event, ui) {
                    $(ui.placeholder).hide();
                    $(this).removeClass('alert-info').addClass('alert-success');
                },
                out: function (event, ui) {
                    $(this).removeClass('alert-success').addClass('alert-info');
                    $(ui.placeholder).show();
                },
                receive: function (event, ui) {
                    $(ui.placeholder).hide();
                    if (intCurPage > 1) {
                        ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), (intElementsPerPage * (intCurPage - 1)), null, function (data, status, jqXHR) {
                            if (status == 'success') {
                                location.reload();
                            }
                            else {
                                statusDisplay.messageError("<b>Request failed!</b>")
                            }
                        }, strTargetModule);
                    }
                    else {
                        ui.sender.sortable("cancel");
                    }
                }
            });

            $('#'+strListId+'_next').sortable({

                over: function (event, ui) {
                    $(ui.placeholder).hide();
                    $(this).removeClass('alert-info').addClass('alert-success');
                },
                out: function (event, ui) {
                    $(this).removeClass('alert-success').addClass('alert-info');
                    $(ui.placeholder).show();
                },
                receive: function (event, ui) {
                    $(ui.placeholder).hide();
                    var intOnPage = $('#'+strListId+' tbody:has(tr[data-systemid!=""])').length + 1;
                    if (intOnPage >= intElementsPerPage) {
                        ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), (intElementsPerPage * intCurPage + 1), null, function (data, status, jqXHR) {
                            if (status == 'success') {
                                location.reload();
                            }
                            else {
                                statusDisplay.messageError("<b>Request failed!</b>")
                            }
                        }, strTargetModule);
                    }
                    else {
                        ui.sender.sortable("cancel");
                    }
                }
            });

            $objListNode.sortable({
                items: 'tbody:has(tr[data-systemid!=""])',
                handle: 'td.listsorthandle',
                cursor: 'move',
                forcePlaceholderSize: true,
                forceHelperSize: true,
                placeholder: 'dndPlaceholder table',
                connectWith: '.divPageTarget',
                start: function (event, ui) {

                    if ($("#"+strListId).attr("data-kajona-pagenum") > 1)
                        $('#'+strListId+'_prev').css("display", "block");

                    if ($('#'+strListId+' tbody:has(tr[data-systemid!=""])').length >= $("#"+strListId).attr("data-kajona-elementsperpage"))
                        $('#'+strListId+'_next').css("display", "block");

                    oldPos = ui.item.index();

                    //hack the placeholder
                    ui.placeholder.html(ui.helper.html());
                },
                stop: function (event, ui) {
                    if (oldPos != ui.item.index() && !ui.item.parent().is('div')) {
                        var intOffset = 1;
                        //see, if there are nodes not being sortable - would lead to another offset
                        $('#'+strListId+' > tbody').each(function (index) {
                            if ($(this).find('tr').data('systemid') == "" )
                                intOffset--;
                            if ($(this).find('tr').data('systemid') == ui.item.find('tr').data('systemid'))
                                return false;
                        });

                        //calc the page-offset
                        var intCurPage = $("#"+strListId).attr("data-kajona-pagenum");
                        var intElementsPerPage = $("#"+strListId).attr("data-kajona-elementsperpage");

                        debugger;

                        var intPagingOffset = 0;
                        if (intCurPage > 1 && intElementsPerPage > 0)
                            intPagingOffset = (intCurPage * intElementsPerPage) - intElementsPerPage;

                        ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), ui.item.index() + intOffset + intPagingOffset, null, null, strTargetModule);
                    }
                    oldPos = 0;
                    $('div.divPageTarget').css("display", "none");
                },
                delay: util.isTouchDevice() ? 500 : 0
            });

            $('#'+strListId +' > tbody:has(tr[data-systemid!=""][data-deleted=""]) > tr').each(function (index) {
                $(this).find("td.listsorthandle").css('cursor', 'move').append("<i class='fa fa-arrows-v'></i>");
                tooltip.addTooltip($(this).find("td.listsorthandle"), "[lang,commons_sort_vertical,system]");

                if (bitMoveToTree) {
                    $(this).find("td.treedrag").css('cursor', 'move')
                        .addClass("jstree-listdraggable").append("<i class='fa fa-arrows-h' data-systemid='" + $(this).data("systemid") + "'></i>");
                    tooltip.addTooltip($(this).find("td.treedrag"), "[lang,commons_sort_totree,system]");
                }
            });
        }
    };
    return sortManager;
});
