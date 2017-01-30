
/**
 * A module to handle the global toolbar. The toolbar is made out of Entry instances, new entries may be added, old ones removed.
 * The toolbar takes care of the general visibility, empty bars will be hidden.
 *
 * @module contentToolbar
 */
define(['jquery'], function ($) {

    var $objToolbarContainer = $(".contentToolbar");
    var $objToolbarList = $(".contentToolbar ul");

    return /** @alias module:contentToolbar */ {

        /**
         * The object representing a single toolbar entry
         * @param strContent
         * @param strIdentifier
         * @constructor
         */
        Entry : function (strContent, strIdentifier){
            this.strContent = strContent;
            this.strIdentifier = strIdentifier;
            this.bitActive = false;
        },

        /**
         * Adds a new entry to the toolbar
         *
         * @param objEntry {Entry}
         */
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

        /**
         * Adds a list of entries
         * @param arrEntries {Entry[]}
         */
        registerContentToolbarEntries: function(arrEntries) {
            if (arrEntries) {
                $(arrEntries).each(function(index, objEntry) {
                    this.registerContentToolbarEntry(objEntry);
                });
            }
        },

        /**
         * Removes a sinvle entry
         * @param strIdentifier
         */
        removeEntry : function(strIdentifier) {
            if($('#'+strIdentifier)) {
                $('#'+strIdentifier).remove();
            }

            if($objToolbarList.children().length == 0) {
                this.resetBar();
            }
        },

        /**
         * Resets the whole bar and hides it
         */
        resetBar : function() {
            $objToolbarList.empty();
            $objToolbarContainer.addClass('hidden');
        }

    }

});
