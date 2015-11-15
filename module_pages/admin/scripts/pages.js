//   (c) 2013-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.pages = {

    initBlockSort : function() {


        var oldPos = null;

        $('fieldset.blocks').sortable({
            items: 'fieldset.block:not(.newblock)',
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
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1);
                }
                oldPos = 0;
            },
            delay: KAJONA.util.isTouchDevice() ? 500 : 0
        });

        $('fieldset.block:not(.newblock)').css('cursor', 'move');
        $('fieldset.block:not(.newblock)  table').css('cursor', 'auto');



    }
};
