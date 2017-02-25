
/**
 * Module to handle the general quickhelp entry
 *
 * @module quickhelp
 */
define('quickhelp', ['jquery', 'bootstrap'], function ($, bootstrap) {

    return /** @alias module:quickhelp */ {

        setQuickhelp : function (strTitle, strText) {
                if(strText.trim() == "" ) {
                    return;
                }
                $('#quickhelp').popover({
                    title: strTitle,
                    content: strText,
                    placement: 'bottom',
                    trigger: 'hover',
                    html: true
                }).css("cursor", "help").show();

        },

        resetQuickhelp : function () {
            $('#quickhelp').hide().popover('destroy');
        }

    }

});
