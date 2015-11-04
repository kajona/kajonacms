//   (c) 2013-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.pages = {

    initBlockSort : function() {


        var oldPos = null;

        $('fieldset.blocks').sortable({
            //items: 'fieldset.block:not(.newblock)',
            items: 'fieldset.block:has([data-systemid!=""])',
            //handle: 'label',
            cursor: 'move',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            //placeholder: 'group_move_placeholder',
            //connectWith: '.divPageTarget',
            start: function(event, ui) {

                oldPos = ui.item.index();
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index()  ) {
                    console.debug('shifting '+ui.item.data('systemid')+' to '+(ui.item.index()+1));
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1);
                }
                oldPos = 0;
            },
            delay: KAJONA.util.isTouchDevice() ? 500 : 0
        });

        $('fieldset.block:not(.newblock)').css('cursor', 'move');
        $('fieldset.block:not(.newblock)  table').css('cursor', 'auto');

        //$('#%%listid%% > tbody:has(tr[data-systemid!=""]) > tr').each(function(index) {
        //    $(this).find("td.listsorthandle").css('cursor', 'move').append("<i class='fa fa-arrows-v'></i>");
        //    KAJONA.admin.tooltip.addTooltip($(this).find("td.listsorthandle"), "[lang,commons_sort_vertical,system]");
        //
        //    if(bitMoveToTree) {
        //        $(this).find("td.treedrag").css('cursor', 'move').addClass("jstree-draggable").append("<i class='fa fa-arrows-h' data-systemid='"+$(this).closest("tr").data("systemid")+"'></i>");
        //        KAJONA.admin.tooltip.addTooltip($(this).find("td.treedrag"), "[lang,commons_sort_totree,system]");
        //    }
        //});

    }
};
