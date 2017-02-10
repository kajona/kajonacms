/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * @module workingIndicator
 */
define(['jquery'], function ($) {

    var intWorkingCount = 0;

    var wi = {
        start: function(){
            if(intWorkingCount == 0) {
                $('#status-indicator').addClass("active");
            }
            intWorkingCount++;
        },
        stop: function(){
            intWorkingCount--;

            if(intWorkingCount == 0) {
                $('#status-indicator').removeClass("active");
            }
        },

        // BC method
        getInstance: function(){
            return wi;
        }
    };

    return wi;
});
