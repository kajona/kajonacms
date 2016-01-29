//   (c) 2007-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

if (typeof KAJONA == "undefined") {
    var KAJONA = {
        util: {
            lang: {}
        },
        portal: {
            lang: {}
        },
        admin: {
            lang: {}
        }
    };
}

/**
 * Cache manager which can get and set key values pairs from the local storage
 *
 * @type {{container: {}, get: Function, set: Function}}
 */
KAJONA.util.cacheManager = {

    container: {},

    /**
     * @param {String} strKey
     * @return {String}
     */
    get: function(strKey){
        if (localStorage) {
            return localStorage.getItem(strKey);
        }

        if (KAJONA.util.cacheManager.container[strKey]) {
            return KAJONA.util.cacheManager.container[strKey];
        }

        return false;
    },

    /**
     * @param {String} strKey
     * @param {String} strValue
     */
    set: function(strKey, strValue){
        if (localStorage) {
            localStorage.setItem(strKey, strValue);
            return;
        }

        KAJONA.util.cacheManager.container[strKey] = strValue;
    }

};

/**
 * Contains the list of lang properties which must be resolved
 *
 * @type {Array}
 */
KAJONA.util.lang.queue = [];

/**
 * Searches inside the container for all data-lang-property attributes and loads the specific property and replaces the
 * html content with the value. If no container element was provided we search in the entire body. I.e.
 * <span data-lang-property="faqs:action_new_faq" data-lang-params="foo,bar"></span>
 *
 * @param {HTMLElement} containerEl
 */
KAJONA.util.lang.initializeProperties = function(containerEl){
    if (!containerEl) {
        containerEl = "body";
    }
    $(containerEl).find("*[data-lang-property]").each(function(){
        var strProperty = $(this).data("lang-property");
        if (strProperty) {
            var arrValues = strProperty.split(":", 2);
            if (arrValues.length == 2) {
                var arrParams = [];
                var strParams = $(this).data("lang-params");
                if (strParams) {
                    arrParams = strParams.split("|");
                }

                var objCallback = function(strText){
                    $(this).html(strText);
                };

                KAJONA.util.lang.queue.push({
                    text: arrValues[1],
                    module: arrValues[0],
                    params: arrParams,
                    callback: objCallback,
                    scope: this
                });
            }
        }
    });

    KAJONA.util.lang.fetchProperties();
};

/**
 * Fetches all properties for the given module and stores them in the local storage. Calls then the callback with the
 * fitting property value as argument. The callback is called directly if the property exists already in the storage.
 * The requests are triggered sequential so that we send per module only one request
 */
KAJONA.util.lang.fetchProperties = function(){
    if (KAJONA.util.lang.queue.length == 0) {
        return;
    }

    var arrData = KAJONA.util.lang.queue[0];
    var strKey = arrData.module + '_' + KAJONA_LANGUAGE + '_' + KAJONA_BROWSER_CACHEBUSTER;
    var objCache = KAJONA.util.cacheManager.get(strKey);

    if(objCache) {
        objCache = jQuery.parseJSON(objCache);
        var strResp = null;
        for (strKey in objCache) {
            if (arrData.text == strKey) {
                strResp = objCache[strKey];
            }
        }
    }

    if (strResp) {
        arrData = KAJONA.util.lang.queue.shift();

        strResp = KAJONA.util.lang.replacePropertyParams(strResp, arrData.params);
        if (typeof arrData.callback === "function") {
            arrData.callback.apply(arrData.scope ? arrData.scope : this, [strResp, arrData.module, arrData.text]);
        }

        KAJONA.util.lang.fetchProperties();
        return;
    }

    $.ajax({
        type: 'POST',
        url: KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=fetchProperty',
        data: {target_module : arrData.module},
        dataType: 'json',
        success: function(objResp) {
            var arrData = KAJONA.util.lang.queue.shift();

            KAJONA.util.cacheManager.set(arrData.module + '_' + KAJONA_LANGUAGE + '_' + KAJONA_BROWSER_CACHEBUSTER, JSON.stringify(objResp));

            var strResp = null;
            for (strKey in objResp) {
                if (arrData.text == strKey) {
                    strResp = objResp[strKey];
                }
            }
            if (strResp !== null) {
                strResp = KAJONA.util.lang.replacePropertyParams(strResp, arrData.params);
                if (typeof arrData.callback === "function") {
                    arrData.callback.apply(arrData.scope ? arrData.scope : this, [strResp, arrData.module, arrData.text]);
                }
            }

            KAJONA.util.lang.fetchProperties();
        }
    });


};

/**
 * Replaces all wildcards i.e. {0} with the value of the array
 *
 * @param {String} strText
 * @param {Array} arrParams
 */
KAJONA.util.lang.replacePropertyParams = function(strText, arrParams){
    for (var i = 0; i < arrParams.length; i++) {
        strText = strText.replace("{" + i + "}", arrParams[i]);
    }
    return strText;
};
