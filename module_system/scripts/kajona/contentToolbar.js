

define(['jquery'], function ($) {



    return {

        Entry : {
            strContent : '',
            strIdentifier : '',
            bitActive : false
        },

        registerContentToolbarEntry: function(objEntry) {

            if (objEntry.strContent != "") {


                var $objToolbar = $(".contentToolbar .navbar-nav");

                if($objToolbar.hasClass('hidden')) {
                    $objToolbar.removeClass('hidden');
                }

                var strIdentifier = "";
                var strClass = "";
                if(objEntry.strIdentifier != '') {
                    strIdentifier = ' id="'+objEntry.strIdentifier+'"';
                }

                if(objEntry.bitActive) {
                    strClass += ' active ';
                }

                $objToolbar.append('<li '+strIdentifier+' class="'+strClass+'">'+strContent+'</li>');
            }
        },


        registerContentToolbarEntries: function(arrEntries) {
            if (arrEntries) {
                $(arrEntries).each(function(index, objEntry) {
                    this.registerContentToolbarEntry(objEntry);
                });
            }
        }

    }

});
