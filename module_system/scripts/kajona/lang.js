/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * language module to load properties / localized strings from the backend
 * @type {Object}
 * @module lang
 */
define('lang', ['jquery', 'ajax', 'cacheManager'], function ($, ajax, cacheManager) {

    /** @exports lang */
    var lang = {};

    /**
     * Contains the list of lang properties which must be resolved
     *
     * @type {Array}
     */
    lang.queue = [];

    /**
     * Searches inside the container for all data-lang-property attributes and loads the specific property and replaces the
     * html content with the value. If no container element was provided we search in the entire body. I.e.
     * <span data-lang-property="faqs:action_new_faq" data-lang-params="foo,bar"></span>
     *
     * @param {HTMLElement} containerEl
     * @param {function} onReady
     */
    lang.initializeProperties = function(containerEl, onReady){
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

                    lang.queue.push({
                        text: arrValues[1],
                        module: arrValues[0],
                        params: arrParams,
                        callback: objCallback,
                        scope: this
                    });
                }
            }
        });

        lang.fetchProperties(onReady);
    };

    /**
     * Fetches a single property and passes the value to the callback as soon as the entry was loaded from the backend
     *
     * @param module
     * @param key
     * @param callback
     */
    lang.fetchSingleProperty = function(module, key, callback) {
        lang.queue.push({
            text: key,
            module: module,
            params: [],
            callback: callback
        });

        lang.fetchProperties();
    };

    /**
     * Fetches all properties for the given module and stores them in the local storage. Calls then the callback with the
     * fitting property value as argument. The callback is called directly if the property exists already in the storage.
     * The requests are triggered sequential so that we send per module only one request
     *
     * @param {function} onReady
     */
    lang.fetchProperties = function(onReady){
        if (lang.queue.length == 0) {
            if (onReady) {
                onReady.apply(this);
            }
            return;
        }

        var arrData = lang.queue[0];
        var strKey = arrData.module + '_' + KAJONA_LANGUAGE + '_' + KAJONA_BROWSER_CACHEBUSTER;
        var objCache = cacheManager.get(strKey);

        if(objCache) {
            objCache = $.parseJSON(objCache);
            var strResp = null;
            for (var strCacheKey in objCache) {
                if (arrData.text == strCacheKey) {
                    strResp = objCache[strCacheKey];
                }
            }
        }

        if (strResp) {
            arrData = lang.queue.shift();

            strResp = lang.replacePropertyParams(strResp, arrData.params);
            if (typeof arrData.callback === "function") {
                arrData.callback.apply(arrData.scope ? arrData.scope : this, [strResp, arrData.module, arrData.text]);
            }

            lang.fetchProperties(onReady);
            return;
        }

        var me = this;
        $.ajax({
            type: 'POST',
            url: KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=fetchProperty',
            data: {target_module : arrData.module},
            dataType: 'json',
            success: function(objResp) {
                var arrData = me.queue.shift();
                if(arrData === undefined) {
                    me.fetchProperties(onReady);
                    return;
                }

                cacheManager.set(arrData.module + '_' + KAJONA_LANGUAGE + '_' + KAJONA_BROWSER_CACHEBUSTER, JSON.stringify(objResp));

                var strResp = null;
                for (strKey in objResp) {
                    if (arrData.text == strKey) {
                        strResp = objResp[strKey];
                    }
                }
                if (strResp !== null) {
                    strResp = me.replacePropertyParams(strResp, arrData.params);
                    if (typeof arrData.callback === "function") {
                        arrData.callback.apply(arrData.scope ? arrData.scope : this, [strResp, arrData.module, arrData.text]);
                    }
                }

                me.fetchProperties(onReady);
            }
        });


    };

    /**
     * Replaces all wildcards i.e. {0} with the value of the array
     *
     * @param {String} strText
     * @param {Array} arrParams
     */
    lang.replacePropertyParams = function(strText, arrParams){
        for (var i = 0; i < arrParams.length; i++) {
            strText = strText.replace("{" + i + "}", arrParams[i]);
        }
        return strText;
    };

    return lang;

});



