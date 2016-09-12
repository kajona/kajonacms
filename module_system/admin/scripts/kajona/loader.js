
/**
 * Loader to load css files. For js use require
 */

define([], function () {

    return {

        loadCss: function(url){
            var absUrl = KAJONA_WEBPATH + url;

            // check whether css was already loaded
            if (!$("link[href='" + absUrl + "']").length) {
                return;
            }

            // append to head
            $('<link href="' + absUrl + '" type="text/css" rel="stylesheet">').appendTo("head");
        }

    };
});

