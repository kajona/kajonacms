
/**
 * Loader to load css files. For js use require
 */

define([], function () {

    return {

        loadCss: function(url){
            var link = document.createElement("link");
            link.type = "text/css";
            link.rel = "stylesheet";
            link.href = KAJONA_WEBPATH + url;
            document.getElementsByTagName("head")[0].appendChild(link);
        }

    };
});



