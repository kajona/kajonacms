//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA == "undefined") {
    var KAJONA = {
        util: {
            lang: {},
        },
        portal: {
            lang: {}
        },
        admin: {
            lang: {}
        }
    };
}


/*
 * -------------------------------------------------------------------------
 * Global functions
 * -------------------------------------------------------------------------
 */


/**
 * Checks if the given array contains the given string
 *
 * @param {String} strNeedle
 * @param {String[]} arrHaystack
 */
KAJONA.util.inArray = function (strNeedle, arrHaystack) {
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
KAJONA.util.fold = function (strElementId, objCallbackVisible, objCallbackInvisible) {
    var element = document.getElementById(strElementId);
    if (element.style.display == 'none') 	{
        element.style.display = 'block';
        if ($.isFunction(objCallbackVisible)) {
            objCallbackVisible();
        }
    }
    else {
        element.style.display = 'none';
        if ($.isFunction(objCallbackInvisible)) {
            objCallbackInvisible();
        }
    }
};

KAJONA.util.isTouchDevice = function() {
    return !!('ontouchstart' in window) ? 1 : 0;
};


/*
 * -------------------------------------------------------------------------
 * Portal-specific functions
 * -------------------------------------------------------------------------
 */


/**
 * Loads/Reloads the Kajona captcha image with the given element id
 *
 * @param {String} strCaptchaId
 * @param {Number} intWidth
 */
KAJONA.portal.loadCaptcha = function (strCaptchaId, intWidth) {
    var containerName = "kajonaCaptcha";
    var imgID = "kajonaCaptchaImg";

    if(strCaptchaId != null) {
        containerName += "_"+strCaptchaId;
        imgID += "_"+strCaptchaId;
    } else {
        //fallback for old templates (old function call)
        imgID = "kajonaCaptcha";
    }
    if (!intWidth) {
        var intWidth = 180;
    }

    var timeCode = new Date().getTime();
    if (document.getElementById(imgID) == undefined && document.getElementById(containerName) != null) {
        var objImg=document.createElement("img");
        objImg.setAttribute("id", imgID);
        objImg.setAttribute("src", KAJONA_WEBPATH+"/image.php?image=kajonaCaptcha&maxWidth="+intWidth+"&reload="+timeCode);
        document.getElementById(containerName).appendChild(objImg);
    } else if(document.getElementById(imgID) != undefined) {
        var objImg = document.getElementById(imgID);
        objImg.src = objImg.src + "&reload="+timeCode;
    }
};


/**
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 * Please only use the specific instances KAJONA.portal.loader or KAJONA.admin.loader
 *
 * @see specific instances KAJONA.portal.loader or KAJONA.admin.loader
 * @author sidler@mulchprod.de
 *
 */
KAJONA.util.Loader = function () {

    var arrCallbacks = [];
    var arrFilesLoaded = [];
    var arrFilesInProgress = [];

    function checkCallbacks() {
        //check if we're ready to call some registered callbacks
        for (var i = 0; i < arrCallbacks.length; i++) {
            if (arrCallbacks[i]) {
                var bitCallback = true;
                for (var j = 0; j < arrCallbacks[i].requiredModules.length; j++) {
                    if ($.inArray(arrCallbacks[i].requiredModules[j], arrFilesLoaded) == -1) {
                        bitCallback = false;
                        break;
                    }
                }

                //execute callback and delete it so it won't get called again
                if (bitCallback) {
                    arrCallbacks[i].callback();
                    delete arrCallbacks[i];
                }
            }
        }
    }


    this.loadFile = function(arrInputFiles, objCallback, bitPreventPathAdding) {
        var arrFilesToLoad = [];

        if(!$.isArray(arrInputFiles))
            arrInputFiles = [ arrInputFiles ];

        //add suffixes
        $.each(arrInputFiles, function(index, strOneFile) {
            if($.inArray(strOneFile, arrFilesLoaded) == -1 )
                arrFilesToLoad.push(strOneFile);
        });

        if(arrFilesToLoad.length == 0) {
            //all files already loaded, call callback
            if($.isFunction(objCallback))
                objCallback();
        }
        else {
            //start loader-processing
            var bitCallbackAdded = false;
            $.each(arrFilesToLoad, function(index, strOneFileToLoad) {
                //check what loader to take - js or css
                var fileType = strOneFileToLoad.substr(strOneFileToLoad.length-2, 2) == 'js' ? 'js' : 'css';

                if(!bitCallbackAdded && $.isFunction(objCallback)) {
                    arrCallbacks.push({
                        'callback' : function() { setTimeout( objCallback, 100); },
                        'requiredModules' : arrFilesToLoad
                    });
                    bitCallbackAdded = true;
                }

                if( $.inArray(strOneFileToLoad, arrFilesInProgress) == -1 ) {
                    arrFilesInProgress.push(strOneFileToLoad);
                   // console. debug('in progress '+strOneFileToLoad);

                    //start loading process
                    if(fileType == 'css') {
                        loadCss(createFinalLoadPath(strOneFileToLoad, bitPreventPathAdding), strOneFileToLoad);
                    }

                    if(fileType == 'js') {
                        loadJs(createFinalLoadPath(strOneFileToLoad, bitPreventPathAdding), strOneFileToLoad);
                    }
                }
            });
        }
    };

    function createFinalLoadPath(strPath, bitPreventPathAdding) {

        //see if the path has to be changed according to a phar-extracted content

        if(KAJONA_PHARMAP && !bitPreventPathAdding) {
            var arrMatches = strPath.match(/(core(.*))\/((module_|element_)([a-zA-Z0-9_])*)/i);
            if (arrMatches && KAJONA.util.inArray(arrMatches[3], KAJONA_PHARMAP)) {
                strPath = strPath.replace(arrMatches[1], "files/extract")
            }
        }

        if(!bitPreventPathAdding)
            strPath = KAJONA_WEBPATH + strPath;

        strPath = strPath+"?"+KAJONA_BROWSER_CACHEBUSTER;

        return strPath;
    }


    function loadCss(strPath, strOriginalPath) {

        if (document.createStyleSheet) {
            document.createStyleSheet(strPath);
        }
        else {
            $('<link rel="stylesheet" type="text/css" href="' + strPath + '" />').appendTo('head');
        }

        arrFilesLoaded.push(strOriginalPath);
        checkCallbacks();
    }

    function loadJs(strPath, strOriginalPath) {

       // console. debug('loading '+strOriginalPath);

        //enable caching, cache flushing is done by the cachebuster
        var options =  {
            dataType: "script",
            cache: true,
            url: strPath
        };

        // Use $.ajax() since it is more flexible than $.getScript
        // Return the jqXHR object so we can chain callbacks
        $.ajax(options)
            .done(function(script, textStatus) {
               // console. debug('loaded '+strOriginalPath);
                arrFilesLoaded.push(strOriginalPath);
                checkCallbacks();

            })
            .fail(function(jqxhr, settings, exception) {
               // console. error('loading file '+strPath+' failed: '+exception);
            });
    }

};

KAJONA.portal.loader = new KAJONA.util.Loader();