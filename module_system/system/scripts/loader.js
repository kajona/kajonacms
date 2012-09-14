//   (c) 2007-2012 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 5047 2012-09-14 10:06:53Z sidler $


if (typeof KAJONA == "undefined") {
    var KAJONA = {
        util: {},
        portal: {
            lang: {}
        },
        admin: {
            lang: {}
        }
    };
}

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
                        //console.log('requirement '+arrCallbacks[i].requiredModules[j]+' not given, no callback');
                        bitCallback = false;
                        break;
                    }
                }

                //execute callback and delete it so it won't get called again
                if (bitCallback) {
                    console.log('requirements all given, triggering callback. loaded: '+arrCallbacks[i].requiredModules);
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
            //console.log("skipped loading files, all already loaded");
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

        if(!bitPreventPathAdding)
            strPath = KAJONA_WEBPATH + strPath;

        strPath = strPath+"?"+KAJONA_BROWSER_CACHEBUSTER;

        return strPath;
    }


    function loadCss(strPath, strOriginalPath) {
        //console.log("loading css: "+strPath);

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
        //console.log("loading js: "+strPath);

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
                arrFilesLoaded.push(strOriginalPath);
                checkCallbacks();

            })
            .fail(function(jqxhr, settings, exception) {
                console.warn('loading file '+strPath+' failed: '+exception);
            });
    }

};