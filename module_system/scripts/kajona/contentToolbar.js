
/**
 * @module contentToolbar
 * @exports contentToolbar
 */

define(['jquery'], function ($) {

    var $objToolbarContainer = $(".contentToolbar");
    var $objToolbarList = $(".contentToolbar ul");

    return /** @alias module:contentToolbar */ {

        Entry : function (strContent){
            this.strContent = strContent;
            this.strIdentifier = '';
            this.bitActive = false;
        },

        registerContentToolbarEntry: function(objEntry) {

            if (objEntry.strContent != "") {

                if($objToolbarContainer.hasClass('hidden')) {
                    $objToolbarContainer.removeClass('hidden');
                }

                var strIdentifier = "";
                var strClass = "";
                if(objEntry.strIdentifier != '') {
                    strIdentifier = ' id="'+objEntry.strIdentifier+'"';
                }

                if(objEntry.bitActive) {
                    strClass += ' active ';
                }

                $objToolbarList.append('<li '+strIdentifier+' class="'+strClass+'">'+objEntry.strContent+'</li>');
            }
        },

        registerContentToolbarEntries: function(arrEntries) {
            if (arrEntries) {
                $(arrEntries).each(function(index, objEntry) {
                    this.registerContentToolbarEntry(objEntry);
                });
            }
        },

        removeEntry : function(strIdentifier) {
            if($('#'+strIdentifier)) {
                $('#'+strIdentifier).remove();
            }

            if($objToolbarList.children().length == 0) {
                this.resetBar();
            }
        },

        resetBar : function() {
            $objToolbarList.empty();
            $objToolbarContainer.addClass('hidden');
        }

    }

});
