//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2012 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

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


/*
 * -------------------------------------------------------------------------
 * Global functions
 * -------------------------------------------------------------------------
 */

/**
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 * Please only use the specific instances KAJONA.portal.loader or KAJONA.admin.loader
 *
 * @param {String} strScriptBase
 * @see specific instances KAJONA.portal.loader or KAJONA.admin.loader
 */
KAJONA.util.Loader = function (strScriptBase) {

    //todo: delete
    var scriptBase = KAJONA_WEBPATH + strScriptBase;

    //todo: delete
    var yuiBase = scriptBase + "yui/";
    //todo: delete
    var arrRequestedModules = {};
    //todo: delete
    var arrLoadedModules = {};


    var arrCallbacks = [];
    var arrFilesLoaded = [];
    var arrFilesInProgress = [];

    //todo: delete
    function createYuiLoader() {
        //create instance of YUILoader
        var yuiLoader = new YAHOO.util.YUILoader({
            base : yuiBase,

            //filter: "DEBUG", //use debug versions
            //add the cachebuster
            filter: {
                'searchExp': "\\.js$",
                'replaceStr': ".js?"+KAJONA_BROWSER_CACHEBUSTER
            },

            onFailure : function(o) {
                alert("File loading failed: " + YAHOO.lang.dump(o));
            },

            onProgress : function(o) {
                arrLoadedModules[o.name] = true;

                arrFilesLoaded.push(o.name);
                checkCallbacks();
            }
        });

        return yuiLoader;
    }

    function checkCallbacks() {
        //check if we're ready to call some registered callbacks
        for (var i = 0; i < arrCallbacks.length; i++) {
            if (arrCallbacks[i]) {
                var bitCallback = true;
                for (var j = 0; j < arrCallbacks[i].requiredModules.length; j++) {
                    if ($.inArray(arrCallbacks[i].requiredModules[j], arrFilesLoaded) == -1) {
                        console.log('requirement '+arrCallbacks[i].requiredModules[j]+' not given, no callback');
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
            if($.inArray(strOneFile, arrFilesLoaded) == -1 && $.inArray(strOneFile, arrFilesInProgress) == -1)
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
                arrFilesInProgress.push(strOneFileToLoad);
                //check what loader to take - js or css
                var fileType = strOneFileToLoad.substr(strOneFileToLoad.length-2, 2) == 'js' ? 'js' : 'css';

                if(!bitCallbackAdded && $.isFunction(objCallback)) {
                    arrCallbacks.push({
                        'callback' : objCallback,
                        'requiredModules' : arrFilesToLoad
                    });
                    bitCallbackAdded = true;
                }

                //start loading process
                if(fileType == 'css') {
                    loadCss(createFinalLoadPath(strOneFileToLoad, bitPreventPathAdding), strOneFileToLoad);
                }

                if(fileType == 'js') {
                    loadJs(createFinalLoadPath(strOneFileToLoad, bitPreventPathAdding), strOneFileToLoad);
                }
            });
        }
    };

    function createFinalLoadPath(strPath, bitPreventPathAdding) {

        if(!bitPreventPathAdding)
            strPath = KAJONA_WEBPATH + strPath;

        var fileType = strPath.substr(strPath.length-2, 2) == 'js' ? 'js' : 'css';

        var filter = {
            'searchExp': "\\."+fileType,
            'replaceStr': "."+fileType+"?"+KAJONA_BROWSER_CACHEBUSTER
        };
        strPath = strPath.replace(new RegExp(filter.searchExp, 'g'), filter.replaceStr);

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

    /**
     * @deprecated
     * @param arrYuiComponents
     * @param arrFiles
     * @param callback
     */
    this.load = function(arrYuiComponents, arrFiles, callback) {


        //TODO: convert loads to hardcoded lists of dependencies


        //todo: delete
        var arrYuiComponentsToWaitFor = [];
        var arrFilesToWaitFor = [];
        var arrYuiComponentsToLoad = [];
        var arrFilesToLoad = [];

        //check YUI components, if they are already loaded or requested
        if (YAHOO.lang.isArray(arrYuiComponents)) {
            for (var i = 0; i < arrYuiComponents.length; i++) {
                if (!(arrYuiComponents[i] in arrLoadedModules)) {
                    arrYuiComponentsToWaitFor.push(arrYuiComponents[i]);
                    if (!(arrYuiComponents[i] in arrRequestedModules)) {
                        arrYuiComponentsToLoad.push(arrYuiComponents[i]);
                    }
                }
            }
        }

        //check own JS/CSS files, if they are already loaded or requested
        if (YAHOO.lang.isArray(arrFiles)) {
            for (var i = 0; i < arrFiles.length; i++) {
                if (!(arrFiles[i] in arrLoadedModules)) {
                    arrFilesToWaitFor.push(arrFiles[i]);
                    if (!(arrFiles[i] in arrRequestedModules)) {
                        arrFilesToLoad.push(arrFiles[i]);
                    }
                }
            }
        }

        //if all modules are already loaded, execute the callback
        if (arrYuiComponentsToWaitFor.length == 0 && arrFilesToWaitFor.length == 0) {
            if (YAHOO.lang.isFunction(callback)) {
                callback();
            }
        } else {
            //register the callback to be called later on
            if (YAHOO.lang.isFunction(callback)) {
                arrCallbacks.push({
                    'callback' : callback,
                    'requiredModules' : arrYuiComponentsToWaitFor.concat(arrFilesToWaitFor)
                });
            }

            //are there components/files to load which are not already requested?
            if (arrYuiComponentsToLoad.length > 0 || arrFilesToLoad.length > 0) {
                var yuiLoader = createYuiLoader();

                for (var i = 0; i < arrYuiComponentsToLoad.length; i++) {
                    yuiLoader.require(arrYuiComponentsToLoad[i]);
                    arrRequestedModules[arrYuiComponentsToLoad[i]] = true;

                }

                if(arrFilesToLoad.length > 0) {
                    this.loadFile(arrFilesToLoad, null, true);
                }

                //fire YUILoader after the onDOMReady event
                YAHOO.util.Event.onDOMReady(function () {
                    yuiLoader.insert();
                });
            }
        }
    };

    //for compatibility with Kajona templates pre 3.3.0
    //todo: delete
    this.convertAdditionalFiles = function(additionalFiles) {
        if (YAHOO.lang.isString(additionalFiles)) {
            //convert to array and add webpath
            return new Array(scriptBase + additionalFiles);
        } else if (YAHOO.lang.isArray(additionalFiles)) {
            //add webpath
            for (var i = 0; i < additionalFiles.length; i++) {
                additionalFiles[i] = scriptBase + additionalFiles[i];
            }
            return additionalFiles;
        } else {
            return null;
        }
    }
};


/*
 * -------------------------------------------------------------------------
 * Admin-specific functions
 * -------------------------------------------------------------------------
 */


/**
 * Tooltips
 *
 * originally based on Bubble Tooltips by Alessandro Fulciniti (http://pro.html.it - http://web-graphics.com)
 */
KAJONA.admin.tooltip = (function() {
	var container;
	var lastMouseX = 0;
	var lastMouseY = 0;

	function locate(e) {
		var posx = 0, posy = 0, c;
		if (e == null) {
			e = window.event;
		}
		if (e.pageX || e.pageY) {
			posx = e.pageX;
			posy = e.pageY;
		} else if (e.clientX || e.clientY) {
			if (document.documentElement.scrollTop) {
				posx = e.clientX + document.documentElement.scrollLeft;
				posy = e.clientY + document.documentElement.scrollTop;
			} else {
				posx = e.clientX + document.body.scrollLeft;
				posy = e.clientY + document.body.scrollTop;
			}
		}

		//save current x and y pos (needed to show tooltip at right position if it's added by onclick)
		if (posx == 0 && posy == 0) {
			posx = lastMouseX;
			posy = lastMouseY;
		} else {
			lastMouseX = posx;
			lastMouseY = posy;
		}

		c = container;
		var left = (posx - c.offsetWidth);
		if (left - c.offsetWidth < 0) {
			left += c.offsetWidth;
		}
		c.style.top = (posy + 10) + "px";
		c.style.left = left + "px";
	}

	function add(objElement, strHtmlContent, bitOpacity) {
		var tooltip;

		if (strHtmlContent == null || strHtmlContent.length == 0) {
			try {
				strHtmlContent = objElement.getAttribute("title");
			} catch (e) {}
		}
		if (strHtmlContent == null || strHtmlContent.length == 0) {
			return;
		}

		//try to remove title
		try {
			objElement.removeAttribute("title");
		} catch (e) {}

		tooltip = document.createElement("span");
		tooltip.className = "kajonaAdminTooltip";
		tooltip.style.display = "block";
		tooltip.innerHTML = strHtmlContent;

		if (bitOpacity != false) {
			tooltip.style.filter = "alpha(opacity:85)";
			tooltip.style.KHTMLOpacity = "0.85";
			tooltip.style.MozOpacity = "0.85";
			tooltip.style.opacity = "0.85";
		}

		//create tooltip container and save reference
		if (container == null) {
			var h = document.createElement("span");
			h.id = "kajonaTooltipContainer";
			h.setAttribute("id", "kajonaTooltipContainer");
			h.style.position = "absolute";
			h.style.zIndex = "2000";
			document.getElementsByTagName("body")[0].appendChild(h);
			container = h;
		}

		objElement.tooltip = tooltip;
		objElement.onmouseover = show;
		objElement.onmouseout = hide;
		objElement.onmousemove = locate;
		objElement.onmouseover(objElement);
	}

	function show(objEvent) {
		hide();
		container.appendChild(this.tooltip);
		locate(objEvent);
	}

	function hide() {
		try {
			var c = container;
			if (c.childNodes.length > 0) {
				c.removeChild(c.firstChild);
			}
		} catch (e) {}
	}

	//public variables and methods
	return {
		add : add,
		show : show,
		hide : hide
	}
}());



/*
 * -------------------------------------------------------------------------
 * Portaleditor-specific functions
 * -------------------------------------------------------------------------
 */


KAJONA.admin.portaleditor = {
	objPlaceholderWithElements: {},

	showActions: function (elementSysId) {
	    var divElement = document.getElementById('container_'+elementSysId);
	    divElement.className="peContainerHover";
	    var menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuHover";
	},

	hideActions: function (elementSysId) {
		var divElement = document.getElementById('container_'+elementSysId);
		divElement.className="peContainerOut";
		var menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuOut";
	},

	switchEnabled: function (bitStatus) {
	    var strStatus = bitStatus == true ? 'true' : 'false';
		var url = window.location.href;
		var anchorPos = url.indexOf('#');
		if (anchorPos != -1) {
	    	url = url.substring(0, anchorPos);
		}

	    url = url.replace('&pe=false', '');
	    url = url.replace('&pe=true', '');
	    url = url.replace('?pe=false', '');
	    url = url.replace('?pe=true', '');

	    if(url.indexOf('?') == -1) {
	        window.location.replace(url+'?pe='+strStatus);
	    } else {
	        window.location.replace(url+'&pe='+strStatus);
	    }
	},

	openDialog: function (strUrl) {
		peDialog.setContentIFrame(strUrl);
		peDialog.init();
	},

	closeDialog: function () {
	    var bitClose = confirm(KAJONA.admin.lang["pe_dialog_close_warning"]);
	    if(bitClose) {
	    	peDialog.hide();

	    	//remove iframe
	    	peDialog.setContentRaw("");
	    }
	},

	addNewElements: function (strPlaceholder, strPlaceholderName, arrElements) {
		this.objPlaceholderWithElements[strPlaceholder] = {
			placeholderName: strPlaceholderName,
			elements: arrElements
		};
	},

	showNewElementMenu: function (strPlaceholder, objAttach) {
		KAJONA.admin.tooltip.hide();

		var arrPlaceholder = this.objPlaceholderWithElements[strPlaceholder];
		var arrElements = arrPlaceholder["elements"];
		var menu;

		if (YAHOO.lang.isUndefined(arrPlaceholder["menu"])) {
			arrPlaceholder["menu"] = menu = new YAHOO.widget.Menu("menu_"+strPlaceholder, {
				shadow: false,
				lazyLoad: true
			});

			var handleClick = function (strType, arrArgs, objElement) {
				KAJONA.admin.portaleditor.openDialog(objElement.elementHref);
			};

			for (var i=0; i<arrElements.length; i++) {
				var e = arrElements[i];
                if(typeof e != 'undefined')
                    menu.addItem({ text: e.elementName, onclick: {fn: handleClick, obj: e} });
			}
			menu.setItemGroupTitle(arrPlaceholder.placeholderName, 0);

			menu.render("menuContainer_"+strPlaceholder);
		} else {
			menu = arrPlaceholder["menu"];
		}
		var buttonRegion = YAHOO.util.Region.getRegion(objAttach);
		menu.cfg.setProperty("x", buttonRegion.left);
		menu.cfg.setProperty("y", buttonRegion.top);
		menu.show();
	}
};

KAJONA.admin.portaleditor.RTE = {};
KAJONA.admin.portaleditor.RTE.modifiedFields = {};

KAJONA.admin.portaleditor.RTE.savePage = function () {

    console.group('savePage');

    $.each(KAJONA.admin.portaleditor.RTE.modifiedFields, function (key, value) {
        var keySplitted = key.split('#');

        var data = {
            systemid:keySplitted[0],
            property:keySplitted[1],
            value:value
        };

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=updateObjectProperty', data, function () {
            console.warn('server response');
            console.log(this.responseText);
        });
    });
    console.groupEnd('savePage');
    $('#savePageLink > img').attr('src', $('#savePageLink > img').attr('src').replace(".gif", "Disabled.gif"));
    KAJONA.admin.portaleditor.RTE.modifiedFields = {};
};


KAJONA.admin.portaleditor.RTE.pasteHandler = function (event) {
    //disable resizing handles in FF
    document.execCommand("enableObjectResizing", false, false);

    var editable = $(event.currentTarget);

    //find the current cursor-position before creating the paste-container, used lateron
    var sel = rangy.getSelection();
    //var range = rangy.createRange();

    var offset = editable.offset();
    $('body').append('<div id="pasteContainer" contentEditable="true" style="position:absolute; clip:rect(0px, 0px, 0px, 0px); width: 1px; height: 1px; top: ' + offset.top + 'px; left: ' + offset.left + 'px;"></div>');
    var pasteContainer = $('#pasteContainer');

    var keySplitted = editable.attr('data-kajona-editable').split('#');
    var isPlaintext = (keySplitted[2] && keySplitted[2] == 'plain') ? true : false;
    if (isPlaintext) {
        var htmlCleanConfig = {
            allowedTags:['']
        };
    } else {
        var htmlCleanConfig = {
            allowedTags:['br', 'p', 'ul', 'ol', 'li']
        };
    }

    editable.blur();
    pasteContainer.focus();

    window.setTimeout(function () {
        event.stopPropagation();

        var content = pasteContainer.html();
        var cleanContent = $.htmlClean.trim($.htmlClean(content, htmlCleanConfig));
        console.warn('paste val: ', content, cleanContent);
        pasteContainer.html('');
        pasteContainer.remove();

        //enable resizing handles in FF again
        document.execCommand("enableObjectResizing", false, true);
        editable.focus();

        //update the old selection
        var strOldHtml = sel.anchorNode.data;
        var strNewHtml = strOldHtml.substr(0, sel.anchorOffset) + cleanContent + strOldHtml.substring(sel.focusOffset);
        sel.anchorNode.data = strNewHtml;
        //set the cursor to the end of the selection
        sel.collapse(sel.anchorNode, sel.anchorOffset + cleanContent.length);
    }, 10);
};


KAJONA.admin.portaleditor.RTE.init = function () {
    console.log("RTE editor init");
    //loop over all editables
    $('*[data-kajona-editable]').each(function () {
        var editable = $(this);
        var keySplitted = editable.attr('data-kajona-editable').split('#');
        var isPlaintext = (keySplitted[2] && keySplitted[2] == 'plain') ? true : false;

        //attach paste handler
        editable.bind('paste', KAJONA.admin.portaleditor.RTE.pasteHandler);

        //prevent enter key when editable is a plaintext field
        if (isPlaintext) {
            editable.keypress(function (event) {
                if (event.which == 13) {
                    return false;
                }
            });
        }

        //always disable drag&drop
        editable.bind('drop drag', function () {
            return false;
        });


        //generate hallo editor config
        var halloConfig = {
            plugins:{
                halloreundo:{}
            },
            modified:function (event, obj) {
                var attr = $(this).attr('data-kajona-editable');

                $('#savePageLink > img').attr('src', $('#savePageLink > img').attr('src').replace("Disabled", ""));
                KAJONA.admin.portaleditor.RTE.modifiedFields[attr] = obj.content;
                //console.log('modified field', attr, obj.content);
            }
        };

        if (!isPlaintext) {
            halloConfig.plugins = {
                halloformat:{},
                hallolists:{},
                halloreundo:{},
                hallolink:{}

            };
        }

        //finally init hallo editor
        editable.hallo(halloConfig);
    });
};





/**
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 *
 * Simply use any of the predefined helpers, e.g.:
 * 	   KAJONA.admin.loader.loadAjaxBase(callback, "rating.js");
 *
 * Or if you want to add your custom YUI components and own files (with absolute path), e.g.:
 *     KAJONA.admin.loader.load(
 *         ["dragdrop", "animation", "container"],
 *         [KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base-min.js",
 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.css",
 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/vanillamin/vanillamin.css"],
 *         callback
 *     );
 *
 */
KAJONA.admin.loader = new KAJONA.util.Loader("/core/module_system/admin/scripts/");

/*
 * extend the loader with predefined helper functions
 */
KAJONA.admin.loader.loadPortaleditorBase = function(objCallback, arrAdditionalFiles) {
	//manually load resize js since the module would load uneeded css
	this.load([ "menu", "container", "element", "dragdrop" ], [KAJONA_WEBPATH+"/core/module_system/admin/scripts/yui/resize/resize-min.js"], objCallback);
};