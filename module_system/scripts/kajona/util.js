
define(['jquery'], function ($) {

    var util = {};

    /**
     * Function to get the element from the current opener.
     *
     * @param strElementId
     * @returns {*}
     */
    util.getElementFromOpener = function(strElementId) {
        if (window.opener) {
            return $('#' + strElementId, window.opener.document);
        } else if (parent){
            return $('#' + strElementId, parent.document);
        }
        else {
            return $('#' + strElementId);
        }
    };

    /**
     * Function to evaluate the script-tags in a passed string, e.g. loaded by an ajax-request
     *
     * @param {String} scripts
     * @see http://wiki.ajax-community.de/know-how:nachladen-von-javascript
     **/
    util.evalScript = function (scripts) {
        try {
            if(scripts != '')	{
                var script = "";
                scripts = scripts.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function() {
                    if (scripts !== null)
                        script += arguments[1] + '\n';
                    return '';
                });
                if(script)
                    (window.execScript) ? window.execScript(script) : window.setTimeout(script, 0);
            }
            return false;
        }
        catch(e) {
            alert(e);
        }
    };

    util.isTouchDevice = function() {
        return !!('ontouchstart' in window) ? 1 : 0;
    };


    /**
     * Checks if the given array contains the given string
     *
     * @param {String} strNeedle
     * @param {String[]} arrHaystack
     */
    util.inArray = function (strNeedle, arrHaystack) {
        for (var i = 0; i < arrHaystack.length; i++) {
            if (arrHaystack[i] == strNeedle) {
                return true;
            }
        }
        return false;
    };

    /**
     * Used to show/hide an html element
     *
     * @param {String} strElementId
     * @param {Function} objCallbackVisible
     * @param {Function} objCallbackInvisible
     */
    util.fold = function (strElementId, objCallbackVisible, objCallbackInvisible) {
        var $element = $('#'+strElementId);
        if ($element.hasClass("folderHidden")) 	{
            $element.removeClass("folderHidden");
            $element.addClass("folderVisible");
            if ($.isFunction(objCallbackVisible)) {
                objCallbackVisible(strElementId);
            }
        }
        else {
            $element.removeClass("folderVisible");
            $element.addClass("folderHidden");
            if ($.isFunction(objCallbackInvisible)) {
                objCallbackInvisible(strElementId);
            }
        }
    };

    /**
     * Used to show/hide an html element and switch an image (e.g. a button)
     *
     * @param {String} strElementId
     * @param {String} strImageId
     * @param {String} strImageVisible
     * @param {String} strImageHidden
     */
    util.foldImage = function (strElementId, strImageId, strImageVisible, strImageHidden) {
        var element = document.getElementById(strElementId);
        var image = document.getElementById(strImageId);
        if (element.style.display == 'none') 	{
            element.style.display = 'block';
            image.src = strImageVisible;
        }
        else {
            element.style.display = 'none';
            image.src = strImageHidden;
        }
    };

    util.setBrowserFocus = function (strElementId) {
        $(function() {
            try {
                focusElement = $("#"+strElementId);
                if (focusElement.hasClass("inputWysiwyg")) {
                    CKEDITOR.config.startupFocus = true;
                } else {
                    focusElement.focus();
                }
            } catch (e) {}
        });
    };

    /**
     * some functions to track the mouse position and move an element
     * @deprecated will be removed with Kajona 3.4 or 3.5, use YUI Panel instead
     */
    util.mover = (function() {
        var currentMouseXPos;
        var currentMouseYPos;
        var objToMove = null;
        var objDiffX = 0;
        var objDiffY = 0;

        function checkMousePosition(e) {
            if (document.all) {
                currentMouseXPos = event.clientX + document.body.scrollLeft;
                currentMouseYPos = event.clientY + document.body.scrollTop;
            } else {
                currentMouseXPos = e.pageX;
                currentMouseYPos = e.pageY;
            }

            if (objToMove != null) {
                objToMove.style.left = currentMouseXPos - objDiffX + "px";
                objToMove.style.top = currentMouseYPos - objDiffY + "px";
            }
        }

        function setMousePressed(obj) {
            objToMove = obj;
            objDiffX = currentMouseXPos - objToMove.offsetLeft;
            objDiffY = currentMouseYPos - objToMove.offsetTop;
        }

        function unsetMousePressed() {
            objToMove = null;
        }


        //public variables and methods
        return {
            checkMousePosition : checkMousePosition,
            setMousePressed : setMousePressed,
            unsetMousePressed : unsetMousePressed
        }
    }());

    /**
     * Converts a string into an integer representation of the string regarding thousands and decimal separator.
     * e.g. "1.000.000" => "1000000"
     * e.g. "7.000" => "7000"
     *
     * @param objValue
     * @param strStyleThousand
     * @param strStyleDecimal
     * @returns {string}
     */
    util.convertValueToInt = function(objValue, strStyleThousand, strStyleDecimal) {
        var strValue = objValue+"";

        var strRegExpThousand = new RegExp("\\"+strStyleThousand, 'g')
        strValue = strValue.replace(strRegExpThousand, "");//remove first thousand separator

        return parseInt(strValue);
    };

    /**
     * Converts a string into a float representation of the string regarding thousands and decimal separator.
     * e.g. "1.000.000,23" => "1000000.23"
     *
     * @param objValue
     * @param strStyleThousand
     * @param strStyleDecimal
     * @returns {string}
     */
    util.convertValueToFloat = function(objValue, strStyleThousand, strStyleDecimal) {
        var strValue = objValue+"";

        var strRegExpThousand = new RegExp("\\"+strStyleThousand, 'g')
        var strRegExpDecimal = new RegExp("\\"+strStyleDecimal, 'g')
        var strRegExpComma = new RegExp("\\,", 'g')

        strValue = strValue.replace(strRegExpThousand, "");//remove first thousand separator
        strValue = strValue.replace(strRegExpComma, ".");//replace decimal with decimal point for db
        strValue = strValue.replace(strRegExpDecimal, ".");//replace decimal with decimal point for db

        return parseFloat(strValue);
    };

    /**
     * Formats a number into a formatted string
     * @see http://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-money-in-javascript
     *
     * .format(12345678.9, 2, 3, '.', ',');  // "12.345.678,90"
     * .format(123456.789, 4, 4, ' ', ':');  // "12 3456:7890"
     * .format(12345678.9, 0, 3, '-');       // "12-345-679"
     *
     * @param floatValue mixed: number to be formatted
     * @param intDecimalLength integer: length of decimal
     * @param intLengthWholePart integer: length of whole part
     * @param strDelimiterSections mixed: sections delimiter
     * @param strDelimiterDecimal mixed: decimal delimiter
     */
    util.formatNumber = function(floatValue, intDecimalLength, intLengthWholePart, strDelimiterSections, strDelimiterDecimal) {
        var re = '\\d(?=(\\d{' + (intLengthWholePart || 3) + '})+' + (intDecimalLength > 0 ? '\\D' : '$') + ')',
            num = floatValue.toFixed(Math.max(0, ~~intDecimalLength));

        return (strDelimiterDecimal ? num.replace('.', strDelimiterDecimal) : num).replace(new RegExp(re, 'g'), '$&' + (strDelimiterSections || ','));
    };

    /**
     * Formats a kajona date format to a specific javascript format string
     *
     * @param {string} format
     * @param {string} type
     */
    util.transformDateFormat = function(format, type) {
        if (type == 'bootstrap-datepicker') {
            return format.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yyyy');
        } else if (type == 'momentjs') {
            return format.replace('d', 'DD').replace('m', 'MM').replace('Y', 'YYYY');
        } else {
            return format;
        }
    };

    /**
     * Extracts an query parameter from the location query string
     *
     * @param {string} name
     * @returns string
     */
    util.getQueryParameter = function(name) {
        var pos = location.search.indexOf("&" + name + "=");
        if(pos != -1) {
            var endPos = location.search.indexOf("&", pos + 1);
            if(endPos == -1) {
                return location.search.substr(pos + name.length + 2);
            }
            else {
                return location.search.substr(pos + name.length + 2, endPos - (pos + name.length + 2));
            }
        }
        return null;
    };

    /**
     * switches the edited language in admin
     */
    util.switchLanguage = function(strLanguageToLoad) {
        var url = window.location.href;
        url = url.replace(/(\?|&)language=([a-z]+)/, "");
        if (url.indexOf('?') == -1) {
            window.location.replace(url + '?language=' + strLanguageToLoad);
        } else {
            window.location.replace(url + '&language=' + strLanguageToLoad);
        }
    };


    /**
     * decodes html entites, call it just like
     * util decodeHTMLEntities(strText)
     *
     * Taken from stackoverflow
     * @see http://stackoverflow.com/a/9609450
     * @see http://stackoverflow.com/questions/5796718/html-entity-decode/9609450#9609450
     *
     */
    util.decodeHtmlEntities = (function() {
        // this prevents any overhead from creating the object each time
        var element = document.createElement('div');

        function decodeHTMLEntities (strText) {
            if(strText && typeof strText === 'string') {
                // strip script/html tags
                strText = strText.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                strText = strText.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                element.innerHTML = strText;
                strText = element.textContent;
                element.textContent = '';
            }

            return strText;
        }

        return decodeHTMLEntities;
    })();

    return util;

});
