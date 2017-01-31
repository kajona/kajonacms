
define([], function(){

    return {
        change : function (strLanguageToLoad) {
            var url = window.location.href;
            url = url.replace(/(\?|&)language=([a-z]+)/, "");
            if (url.indexOf('?') == -1) {
                window.location.replace(url + '?language=' + strLanguageToLoad);
            } else {
                window.location.replace(url + '&language=' + strLanguageToLoad);
            }
        }
    }

});