//   (c) 2013-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt


define(['jquery', 'ajax', 'util'], function ($, ajax, util) {

    var pages = {};


    pages.initBlockSort = function() {

        var oldPos = null;

        $('fieldset.blocks').sortable({
            items: 'fieldset.block:not(.newblock)',
            //handle: 'label',
            cursor: 'move',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            start: function(event, ui) {

                oldPos = ui.item.index();
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index()  ) {
                    ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1);
                }
                oldPos = 0;
            },
            delay: util.isTouchDevice() ? 500 : 0
        });

        $('fieldset.block:not(.newblock)').css('cursor', 'move');
        $('fieldset.block:not(.newblock)  table').css('cursor', 'auto');

    };

    return pages;
});
