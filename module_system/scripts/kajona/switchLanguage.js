/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * @module switchLanguage
 */
define('switchLanguage', [], function(){

    return /** @alias module:switchLanguage */ {
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