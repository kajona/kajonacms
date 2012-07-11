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
 * Function to evaluate the script-tags in a passed string, e.g. loaded by an ajax-request
 *
 * @param {String} scripts
 * @see http://wiki.ajax-community.de/know-how:nachladen-von-javascript
 **/
KAJONA.util.evalScript = function (scripts) {
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
		if (YAHOO.lang.isFunction(objCallbackVisible)) {
			objCallbackVisible();
		}
    }
    else {
    	element.style.display = 'none';
		if (YAHOO.lang.isFunction(objCallbackInvisible)) {
			objCallbackInvisible();
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
KAJONA.util.foldImage = function (strElementId, strImageId, strImageVisible, strImageHidden) {
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

KAJONA.util.setBrowserFocus = function (strElementId) {
	YAHOO.util.Event.onDOMReady(function() {
		try {
		    focusElement = YAHOO.util.Dom.get(strElementId);
		    if (YAHOO.util.Dom.hasClass(focusElement, "inputWysiwyg")) {
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
KAJONA.util.mover = (function() {
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
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 * Please only use the specific instances KAJONA.portal.loader or KAJONA.admin.loader
 *
 * @param {String} strScriptBase
 * @see specific instances KAJONA.portal.loader or KAJONA.admin.loader
 *
 * @todo: remove param strScriptBase
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
						bitCallback = false;
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
            $.each(arrFilesToLoad, function(index, strOneFileToLoad) {
                arrFilesInProgress.push(strOneFileToLoad);
                //check what loader to take - js or css
                var fileType = strOneFileToLoad.substr(strOneFileToLoad.length-2, 2) == 'js' ? 'js' : 'css';

                if($.isFunction(objCallback)) {
                    arrCallbacks.push({
                        'callback' : objCallback,
                        'requiredModules' : arrFilesToLoad
                    });
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

//				for (var i = 0; i < arrFilesToLoad.length; i++) {
//
//                    this.loadFile()
//
//
//                    //TODO: ugly hack
//                    arrFilesLoaded.push(arrFilesToLoad[i]);
//
//                    var fileType = arrFilesToLoad[i].substr(arrFilesToLoad[i].length-2, 2) == 'js' ? 'js' : 'css';
//
//                    var filter = {
//        				'searchExp': "\\."+fileType,
//        				'replaceStr': "."+fileType+"?"+KAJONA_BROWSER_CACHEBUSTER
//        			};
//                    var url = arrFilesToLoad[i].replace(new RegExp(filter.searchExp, 'g'), filter.replaceStr);
//
//					yuiLoader.addModule( {
//						name : arrFilesToLoad[i],
//						type : fileType,
//						skinnable : false,
//						fullpath : url
//					});
//
//					yuiLoader.require(arrFilesToLoad[i]);
//					arrRequestedModules[arrFilesToLoad[i]] = true;
//				}

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
KAJONA.admin.loader.loadAjaxBase = function(objCallback, arrAdditionalFiles) {
    alert("KAJONA.admin.loader.loadAjaxBase no longer supported!");
};

KAJONA.admin.loader.loadDragNDropBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "connection", "animation", "dragdrop" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.admin.loader.loadAnimationBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "animation" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.admin.loader.loadAutocompleteBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "connection", "datasource", "autocomplete" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.admin.loader.loadCalendarBase = function(objCallback, arrAdditionalFiles) {
	var arrCustomFiles = [
	    KAJONA_WEBPATH + "/core/module_system/admin/scripts/yui/calendar/calendar-min.js",
        KAJONA_WEBPATH + "/core/module_system/admin/scripts/yui/calendar/assets/calendar.css"
	];

	if (!YAHOO.lang.isUndefined(arrAdditionalFiles)) {
		arrCustomFiles.push(this.convertAdditionalFiles(arrAdditionalFiles));
	}

	this.load(null, arrCustomFiles, objCallback);
};

KAJONA.admin.loader.loadDialogBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "resize", "container", "element", "dragdrop" ], arrAdditionalFiles, objCallback);
};

KAJONA.admin.loader.loadTreeviewBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "treeview", "connection" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};


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

/**
 * Folderview functions
 */
KAJONA.admin.folderview = {
	/**
	 * holds a reference to the ModalDialog
	 */
	dialog: undefined,

	/**
	 * holds CKEditors CKEditorFuncNum parameter to read it again in KAJONA.admin.folderview.fillFormFields()
	 * so we don't have to pass through the param with all requests
	 */
	selectCallbackCKEditorFuncNum: 0,

	/**
	 * To be called when the user selects an page/folder/file out of a folderview dialog/popup
	 * Detects if the folderview is embedded in a dialog or popup to find the right context
     *
     * @param {Array} arrTargetsValues
     * @param {function} objCallback
	 */
	selectCallback: function (arrTargetsValues, objCallback) {
		if (window.opener) {
			window.opener.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
		} else if (parent) {
			parent.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
		}

        if ($.isFunction(objCallback)) {
			objCallback();
		}

        this.close();

	},

	/**
	 * fills the form fields with the selected values
	 */
	fillFormFields: function (arrTargetsValues) {
		for (var i in arrTargetsValues) {
	    	if (arrTargetsValues[i][0] == "ckeditor") {
	    		CKEDITOR.tools.callFunction(this.selectCallbackCKEditorFuncNum, arrTargetsValues[i][1]);
	    	} else {
	    		var formField = $("#"+arrTargetsValues[i][0]).get(0);

                if (formField != null) {
                	formField.value = arrTargetsValues[i][1];

                	//fire the onchange event on the form field
                    if (document.createEvent) { //Firefox
                        var evt = document.createEvent("Events");
                        evt.initEvent('change', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                        formField.dispatchEvent(evt);
                    } else if (document.createEventObject) { //IE
                        var evt = document.createEventObject();
                        formField.fireEvent('onchange', evt);
                    }

                }
	    	}
		}
	},

	/**
	 * fills the form fields with the selected values
	 */
	close: function () {
		if (window.opener) {
			window.close();
		} else if (parent) {
			var context = parent.KAJONA.admin.folderview;
			context.dialog.hide();
			context.dialog.setContentRaw("");
		}
	}
};



/**
 * switches the edited language in admin
 */
KAJONA.admin.switchLanguage = function(strLanguageToLoad) {
	var url = window.location.href;
	url = url.replace(/(\?|&)language=([a-z]+)/, "");
	if (url.indexOf('?') == -1) {
		window.location.replace(url + '?language=' + strLanguageToLoad);
	} else {
		window.location.replace(url + '&language=' + strLanguageToLoad);
	}
}

/**
 * little helper function for the system right matrix
 */
KAJONA.admin.checkRightMatrix = function() {
	// mode 1: inheritance
	if (document.getElementById('inherit').checked) {
		// loop over all checkboxes to disable them
		for (var intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if (objCurElement.type == 'checkbox') {
				if (objCurElement.id != 'inherit') {
					objCurElement.disabled = true;
					objCurElement.checked = false;
					var strCurId = "inherit," + objCurElement.id;
					if (document.getElementById(strCurId) != null) {
						if (document.getElementById(strCurId).value == '1') {
							objCurElement.checked = true;
						}
					}
				}
			}
		}
	} else {
		// mode 2: no inheritance, make all checkboxes editable
		for (intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if (objCurElement.type == 'checkbox') {
				if (objCurElement.id != 'inherit') {
					objCurElement.disabled = false;
				}
			}
		}
	}
}

/**
 * General way to display a status message.
 * Therefore, the html-page should provide the following elements as noted as instance-vars:
 * - div,   id: jsStatusBox    				the box to be animated
 * 		 class: jsStatusBoxMessage			class in case of an informal message
 * 		 class: jsStatusBoxError		    class in case of an error message
 * - div,   id: jsStatusBoxContent			the box to place the message-content into
 *
 * Pass a xml-response from a Kajona server to displayXMLMessage() to start the logic
 * or use messageOK() / messageError() passing a regular string
 */
KAJONA.admin.statusDisplay = {
	idOfMessageBox : "jsStatusBox",
	idOfContentBox : "jsStatusBoxContent",
	classOfMessageBox : "jsStatusBoxMessage",
	classOfErrorBox : "jsStatusBoxError",
	timeToFadeOutMessage : 4000,
	timeToFadeOutError : 10000,
	timeToFadeOut : null,
	animObject : null,

	/**
	 * General entrance point. Use this method to pass an xml-response from the kajona server.
	 * Tries to find a message- or an error-tag an invokes the corresponding methods
	 *
	 * @param {String} message
	 */
	displayXMLMessage : function(message) {
		//decide, whether to show an error or a message, message only in debug mode
		if(message.indexOf("<message>") != -1 && KAJONA_DEBUG > 0 && message.indexOf("<error>") == -1) {
			var intStart = message.indexOf("<message>")+9;
			var responseText = message.substr(intStart, message.indexOf("</message>")-intStart);
			this.messageOK(responseText);
		}

		if(message.indexOf("<error>") != -1) {
			var intStart = message.indexOf("<error>")+7;
			var responseText = message.substr(intStart, message.indexOf("</error>")-intStart);
			this.messageError(responseText);
		}
	},

	/**
	 * Creates a informal message box containg the passed content
	 *
	 * @param {String} strMessage
	 */
    messageOK : function(strMessage) {
		YAHOO.util.Dom.removeClass(this.idOfMessageBox, this.classOfMessageBox)
		YAHOO.util.Dom.removeClass(this.idOfMessageBox, this.classOfErrorBox)
		YAHOO.util.Dom.addClass(this.idOfMessageBox, this.classOfMessageBox);
		this.timeToFadeOut = this.timeToFadeOutMessage;
		this.startFadeIn(strMessage);
    },

	/**
	 * Creates an error message box containg the passed content
	 *
	 * @param {String} strMessage
	 */
    messageError : function(strMessage) {
		YAHOO.util.Dom.removeClass(this.idOfMessageBox, this.classOfMessageBox)
		YAHOO.util.Dom.removeClass(this.idOfMessageBox, this.classOfErrorBox)
		YAHOO.util.Dom.addClass(this.idOfMessageBox, this.classOfErrorBox);
		this.timeToFadeOut = this.timeToFadeOutError;
		this.startFadeIn(strMessage);
    },

	startFadeIn : function(strMessage) {
		//currently animated?
		if(this.animObject != null && this.animObject.isAnimated()) {
			this.animObject.stop(true);
			this.animObject.onComplete.unsubscribeAll();
		}
		var statusBox = YAHOO.util.Dom.get(this.idOfMessageBox);
		var contentBox = YAHOO.util.Dom.get(this.idOfContentBox);
		contentBox.innerHTML = strMessage;
		YAHOO.util.Dom.setStyle(statusBox, "display", "");
		YAHOO.util.Dom.setStyle(statusBox, "opacity", 0.0);

		//place the element at the top of the page
		var screenWidth = YAHOO.util.Dom.getViewportWidth();
		var divWidth = statusBox.offsetWidth;
		var newX = screenWidth/2 - divWidth/2;
		var newY = YAHOO.util.Dom.getDocumentScrollTop() -2;
		YAHOO.util.Dom.setXY(statusBox, new Array(newX, newY));

		//start fade-in handler
    	KAJONA.admin.loader.loadAnimationBase(function() {
    		KAJONA.admin.statusDisplay.fadeIn();
		});
	},

	fadeIn : function () {
		this.animObject = new YAHOO.util.Anim(this.idOfMessageBox, {opacity: {to: 0.8}}, 1, YAHOO.util.Easing.easeOut);
		this.animObject.onComplete.subscribe(function() {window.setTimeout("KAJONA.admin.statusDisplay.startFadeOut()", this.timeToFadeOut);});
		this.animObject.animate();
	},

	startFadeOut : function() {
		var statusBox = YAHOO.util.Dom.get(this.idOfMessageBox);

		//get the current pos
		var attributes = {
	        points: {by: [0, (YAHOO.util.Dom.getY(statusBox)+statusBox.offsetHeight)*-1-5]}
	    };
	    this.animObject = new YAHOO.util.Motion(statusBox, attributes, 0.5);
	    this.animObject.onComplete.subscribe(function() {YAHOO.util.Dom.setStyle(this.idOfMessageBox, "display", "none");});
		this.animObject.animate();
	}
};


/**
 * Object to show a modal dialog
 */
KAJONA.admin.ModalDialog = function(strDialogId, intDialogType, bitDragging, bitResizing) {
	this.dialog;
	this.containerId = strDialogId;
	this.iframeId;
    this.iframeURL;

	this.setTitle = function(strTitle) {
		document.getElementById(this.containerId + "_title").innerHTML = strTitle;
	};

	this.setContent = function(strQuestion, strConfirmButton, strLinkHref) {
		if (intDialogType == 1) {
			document.getElementById(this.containerId + "_content").innerHTML = strQuestion;
			var confirmButton = document.getElementById(this.containerId
					+ "_confirmButton");
			confirmButton.value = strConfirmButton;
			confirmButton.onclick = function() {
				window.location = strLinkHref;
				return false;
			};
		}
	};

	this.setContentRaw = function(strContent) {
		document.getElementById(this.containerId + "_content").innerHTML = strContent;
		//center the dialog (later() as workaround to add a minimal delay)
		YAHOO.lang.later(10, this, function() {this.dialog.center();});
	};

	this.setContentIFrame = function(strUrl) {
		this.iframeId = this.containerId+"_iframe";
        this.iframeURL = strUrl;
        //commented, now called below
		//document.getElementById(this.containerId + "_content").innerHTML = "<iframe src=\""+strUrl+"\" width=\"100%\" height=\"100%\" frameborder=\"0\" name=\""+this.iframeId+"\" id=\""+this.iframeId+"\"></iframe>";
	};

	this.init = function(intWidth, intHeight) {
		this.dialog = new YAHOO.widget.Panel(this.containerId, {
			fixedcenter: true,
			close: false,
			draggable: false,
			dragOnly: true,
			underlay: "none",
			constraintoviewport: true,
			zindex: 4000,
			modal: true,
			visible: true,
            width: intWidth,
            height: intHeight
		});


		document.getElementById(this.containerId).style.display = "block";

		this.dialog.render(document.body);
		this.dialog.show();
		this.dialog.focusLast();

        //FIXME: jsr / js-dev please review: moved from setContentIFrame right to here. otherwise the iframe is loaded twice
        if(this.iframeURL != null) {
            document.getElementById(this.containerId + "_content").innerHTML = "<iframe src=\""+this.iframeURL+"\" width=\"100%\" height=\"100%\" frameborder=\"0\" name=\""+this.iframeId+"\" id=\""+this.iframeId+"\"></iframe>";
            this.iframeURL = null;
        }

		//TODO: dynamically loading of dragdrop/resize files
		if (bitDragging) {
			this.enableDragging();
		}
		if (bitResizing) {
			this.enableResizing();
		}
	};

	this.hide = function() {
		try {
			this.dialog.hide();
		}
		catch (e) {};
	};

	this.enableDragging = function() {
		this.dialog.cfg.setProperty("draggable", true);

		this.dialog.dragEvent.subscribe(function(o, event) {
			//hide iframe while dragging, if available
			if (!YAHOO.lang.isUndefined(this.iframeId)) {
				if (event[0] == "startDrag") {
					YAHOO.util.Dom.setStyle(this.iframeId, "visibility", "hidden");
				} else if (event[0] == "endDrag") {
					YAHOO.util.Dom.setStyle(this.iframeId, "visibility", "visible");
				}
			}
        }, this, true);
	};

	this.enableResizing = function() {
		var resize = new YAHOO.util.Resize(this.containerId, {
            handles: ["br"],
            autoRatio: false,
            minWidth: 400,
            minHeight: 300,
            status: false
        });

        resize.on("startResize", function(args) {
		    if (this.dialog.cfg.getProperty("constraintoviewport")) {
                var D = YAHOO.util.Dom;

                var clientRegion = D.getClientRegion();
                var elRegion = D.getRegion(this.element);

                resize.set("maxWidth", clientRegion.right - elRegion.left - YAHOO.widget.Overlay.VIEWPORT_OFFSET);
                resize.set("maxHeight", clientRegion.bottom - elRegion.top - YAHOO.widget.Overlay.VIEWPORT_OFFSET);
            } else {
                resize.set("maxWidth", null);
                resize.set("maxHeight", null);
        	}

			//hide iframe while resizing, if available
			if (!YAHOO.lang.isUndefined(this.iframeId)) {
				YAHOO.util.Dom.setStyle(this.containerId+"_iframe", "visibility", "hidden");
			}
        }, this, true);

        resize.on("resize", function(args) {
            var panelHeight = args.height;
            this.dialog.cfg.setProperty("height", panelHeight + "px");
        }, this, true);

        resize.on("endResize", function(args) {
			//show iframe after resize, if available
			if (!YAHOO.lang.isUndefined(this.iframeId)) {
				YAHOO.util.Dom.setStyle(this.containerId+"_iframe", "visibility", "visible");
			}
        }, this, true);
	};
};


/**
 * Functions to execute system tasks
 */
KAJONA.admin.systemtask = {
    executeTask : function(strTaskname, strAdditionalParam, bitNoContentReset) {
        if(bitNoContentReset == null || bitNoContentReset == undefined) {

            if(document.getElementById('taskParamForm') != null) {
                document.getElementById('taskParamForm').style.display = "none";
            }

            jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE);
            jsDialog_0.setContentRaw(kajonaSystemtaskDialogContent);
            document.getElementById(jsDialog_0.containerId).style.width = "550px";
            document.getElementById('systemtaskCancelButton').onclick = this.cancelExecution;
            jsDialog_0.init();
        }

        KAJONA.admin.ajax.genericAjaxCall("system", "executeSystemTask", "&task="+strTaskname+strAdditionalParam, function(data, status, jqXHR) {
            if(status == 'success') {
                var strResponseText = data;

                //parse the response and check if it's valid
                if(strResponseText.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(strResponseText);
                }
                else if(strResponseText.indexOf("<statusinfo>") == -1) {
                	KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />"+strResponseText);
                }
                else {
                    var intStart = strResponseText.indexOf("<statusinfo>")+12;
                    var strStatusInfo = strResponseText.substr(intStart, strResponseText.indexOf("</statusinfo>")-intStart);

                    //parse text to decide if a reload is necessary
                    var strReload = "";
                    if(strResponseText.indexOf("<reloadurl>") != -1) {
                        intStart = strResponseText.indexOf("<reloadurl>")+11;
                        strReload = strResponseText.substr(intStart, strResponseText.indexOf("</reloadurl>")-intStart);
                    }

                    //show status info
                    document.getElementById('systemtaskStatusDiv').innerHTML = strStatusInfo;
                    //center the dialog again (later() as workaround to add a minimal delay)
                    YAHOO.lang.later(10, this, function() {jsDialog_0.dialog.center();});

                    if(strReload == "") {
                    	jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE_DONE);
                    	document.getElementById('systemtaskLoadingDiv').style.display = "none";
                    	document.getElementById('systemtaskCancelButton').value = KAJONA_SYSTEMTASK_CLOSE;
                    }
                    else {
                    	KAJONA.admin.systemtask.executeTask(strTaskname, strReload, true);
                    }
                }
            }

            else {
                jsDialog_0.hide();
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />"+data);
            }
        });
    },

    cancelExecution : function() {
        if(YAHOO.util.Connect.isCallInProgress(KAJONA.admin.ajax.systemTaskCall)) {
           YAHOO.util.Connect.abort(KAJONA.admin.ajax.systemTaskCall, null, false);
        }
        jsDialog_0.hide();
    },

    setName : function(strName) {
    	document.getElementById('systemtaskNameDiv').innerHTML = strName;
    }
};

/**
 * AJAX functions for connecting to the server
 */
KAJONA.admin.ajax = {

    getDataObjectFromString: function(strData, bitFirstIsSystemid) {
        //strip other params, backwards compatibility
        var arrElements = strData.split("&");
        var data = { };

        if(bitFirstIsSystemid)
            data["systemid"] = arrElements[0];

        //first one is the systemid
        if(arrElements.length > 1) {
            $.each(arrElements, function(index, strValue) {
                if(!bitFirstIsSystemid || index > 0) {
                    var arrSingleParams = strValue.split("=");
                    data[arrSingleParams[0]] = arrSingleParams[1];
                }
            });
        }
        return data;
    },

    regularCallback: function(data, status, jqXHR) {
		if(status == 'success') {
			KAJONA.admin.statusDisplay.displayXMLMessage(data)
		}
		else {
			KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
		}
	},


	genericAjaxCall : function(module, action, systemid, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
        var data = this.getDataObjectFromString(systemid, true);

        $.ajax({
            type: 'POST',
            url: postTarget,
            data: data,
            success: objCallback,
            dataType: 'text'
        });

	},

    setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList, objCallback, strTargetModule) {
        if(strTargetModule == null || strTargetModule == "")
            strTargetModule = "system";

        if(typeof objCallback == 'undefined' || objCallback == null)
            objCallback = KAJONA.admin.ajax.regularCallback;


        KAJONA.admin.ajax.genericAjaxCall(strTargetModule, "setAbsolutePosition", systemIdToMove + "&listPos=" + intNewPos, objCallback);
	},

	setDashboardPos : function(systemIdToMove, intNewPos, strIdOfList) {
        KAJONA.admin.ajax.genericAjaxCall("dashboard", "setDashboardPosition", systemIdToMove + "&listPos=" + intNewPos+"&listId="+strIdOfList, KAJONA.admin.ajax.regularCallback);
	},

	setSystemStatus : function(strSystemIdToSet, bitReload) {
        var objCallback = function(data, status, jqXHR) {
            if(status == 'success') {
				KAJONA.admin.statusDisplay.displayXMLMessage(data);

                if(bitReload !== null && bitReload === true)
                    location.reload();

				if (data.indexOf('<error>') == -1 && data.indexOf('<html>') == -1) {
					var image = document.getElementById('statusImage_' + strSystemIdToSet);
					var link = document.getElementById('statusLink_' + strSystemIdToSet);

					if (image.src.indexOf('icon_enabled.gif') != -1) {
						image.src = strInActiveImageSrc;
						image.setAttribute('alt', strInActiveText);
						link.setAttribute('title', strInActiveText);
					} else {
						image.src = strActiveImageSrc;
						image.setAttribute('alt', strActiveText);
						link.setAttribute('title', strActiveText);
					}

					KAJONA.admin.tooltip.add(link);
				}
        	}
            else{
        		KAJONA.admin.statusDisplay.messageError(data);
        	}
        };

        KAJONA.admin.ajax.genericAjaxCall("system", "setStatus", strSystemIdToSet, objCallback);
	},

    loadPagesTreeViewNodes : function (node, fnLoadComplete)  {
        var nodeSystemid = node.systemid;
        KAJONA.admin.ajax.genericAjaxCall("pages", "getChildNodes", nodeSystemid, function(data, status, jqXHR) {
            if(status == 'success') {
                //check if answer contains an error
                if(data.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(data);
                    fnLoadComplete();
                }
                else {
                    //success, start transforming the childs to tree-view nodes
                    //TODO: use xml parser instead of string-parsing
                    //process nodes
                    var intStart = data.indexOf("<entries>")+9;
                    var strEntries = data.substr(intStart, data.indexOf("</entries>")-intStart);

                    while(strEntries.indexOf("<folder>") != -1 || strEntries.indexOf("<page>") != -1 ) {

                        if(strEntries.substr(0, 8) == "<folder>") {
                            var intFolderStart = strEntries.indexOf("<folder>")+8;
                            var intFolderEnd = strEntries.indexOf("</folder>")-intFolderStart;
                            var strSingleFolder = strEntries.substr(intFolderStart, intFolderEnd);

                            var intTemp = strSingleFolder.indexOf("<name>")+6;
                            var strName = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</name>")-intTemp);

                            intTemp = strSingleFolder.indexOf("<systemid>")+10;
                            var strSystemid = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</systemid>")-intTemp);

                            intTemp = strSingleFolder.indexOf("<link>")+6;
                            var strLink = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</link>")-intTemp);

                            intTemp = strSingleFolder.indexOf("<isleaf>")+8;
                            var strLeaf = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</isleaf>")-intTemp);

                            strName = strName.replace(/&amp;/g, '&', strName);

                            var tempNode = new YAHOO.widget.TextNode( {label:strName, href:strLink}, node);
                            tempNode.systemid = strSystemid;
                            tempNode.labelStyle = "treeView-foldernode";
                            tempNode.isLeaf = strLeaf == "true";

                            strEntries = strEntries.substr(strEntries.indexOf("</folder>")+9);
                        }
                        else if(strEntries.substr(0, 6) == "<page>") {
                            var intPageStart = strEntries.indexOf("<page>")+6;
                            var intPageEnd = strEntries.indexOf("</page>")-intPageStart;
                            var strSinglePage = strEntries.substr(intPageStart, intPageEnd);

                            intTemp = strSinglePage.indexOf("<name>")+6;
                            strName = strSinglePage.substr(intTemp, strSinglePage.indexOf("</name>")-intTemp);

                            intTemp = strSinglePage.indexOf("<systemid>")+10;
                            strSystemid = strSinglePage.substr(intTemp, strSinglePage.indexOf("</systemid>")-intTemp);

                            intTemp = strSinglePage.indexOf("<link>")+6;
                            strLink = strSinglePage.substr(intTemp, strSinglePage.indexOf("</link>")-intTemp);

                            intTemp = strSinglePage.indexOf("<isleaf>")+8;
                            var strLeaf = strSinglePage.substr(intTemp, strSinglePage.indexOf("</isleaf>")-intTemp);

                            intTemp = strSinglePage.indexOf("<type>")+6;
                            var intType = strSinglePage.substr(intTemp, strSinglePage.indexOf("</type>")-intTemp);

                            strName = strName.replace(/&amp;/g, '&', strName);

                            tempNode = new YAHOO.widget.TextNode({label:strName, href:strLink}, node);
                            tempNode.systemid = strSystemid;
                            tempNode.isLeaf = strLeaf == "true";
                            tempNode.labelStyle = intType == 0 ? "treeView-pagenode" : "treeView-pagealiasnode";

                            strEntries = strEntries.substr(strEntries.indexOf("</page>")+7);

                        }

                    }

                    fnLoadComplete();
                    KAJONA.admin.treeview.checkInitialTreeViewToggling();
                }
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            }
        });
    },

    loadNavigationTreeViewNodes : function (node, fnLoadComplete)  {
        var nodeSystemid = node.systemid;
        KAJONA.admin.ajax.genericAjaxCall("navigation", "getChildNodes", nodeSystemid, function(data, status, jqXHR) {
            if(status == 'success') {
                //check if answer contains an error
                if(data.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(data);
                    fnLoadComplete();
                }
                else {
                    //success, start transforming the childs to tree-view nodes
                    //TODO: use xml parser instead of string-parsing
                    //process nodes
                    var strPoints = data;

                    while(strPoints.indexOf("<point>") != -1 ) {
                        var intFolderStart = strPoints.indexOf("<point>")+7;
                        var intFolderEnd = strPoints.indexOf("</point>")-intFolderStart;
                        var strSingleFolder = strPoints.substr(intFolderStart, intFolderEnd);

                        var intTemp = strSingleFolder.indexOf("<name>")+6;
                        var strName = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</name>")-intTemp);

                        intTemp = strSingleFolder.indexOf("<systemid>")+10;
                        var strSystemid = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</systemid>")-intTemp);

                        intTemp = strSingleFolder.indexOf("<link>")+6;
                        var strLink = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</link>")-intTemp);

                        intTemp = strSingleFolder.indexOf("<isleaf>")+8;
                        var strLeaf = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</isleaf>")-intTemp);

                        strName = strName.replace(/&amp;/g, '&', strName);

                        var tempNode = new YAHOO.widget.TextNode( {label:strName, href:strLink}, node);
                        tempNode.systemid = strSystemid;
                        tempNode.labelStyle = "treeView-navigationnode";
                        tempNode.isLeaf = strLeaf == "true";

                        strPoints = strPoints.substr(strPoints.indexOf("</point>")+8);
                    }

                    fnLoadComplete();
                    KAJONA.admin.treeview.checkInitialTreeViewToggling();
                }
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            }
        });
    }

};

/**
 * Treeview functions
 */
KAJONA.admin.treeview = {};
KAJONA.admin.treeview.checkInitialTreeViewToggling = function() {
    if(arrTreeViewExpanders.length > 0) {
        var strValue = arrTreeViewExpanders.shift();
        var objNode = tree.getNodeByProperty("systemid", strValue);
        if(objNode != null) {
            objNode.expand();
        }
    }
};


/**
 * Calendar functions
 */
KAJONA.admin.calendar = {};
KAJONA.admin.calendar.showCalendar = function(strCalendarId, strCalendarContainerId, objButton) {
	KAJONA.util.fold(strCalendarContainerId, function() {
		//positioning the calendar container
		var btnRegion = YAHOO.util.Region.getRegion(objButton);
		YAHOO.util.Dom.setStyle(strCalendarContainerId, "left", btnRegion.left+"px");

		//show nice loading animation while loading the calendar files
		YAHOO.util.Dom.addClass(strCalendarContainerId, "loadingContainer");

		KAJONA.admin.loader.loadCalendarBase(function() {
	    	KAJONA.admin.calendar.initCalendar(strCalendarId, strCalendarContainerId);
	    	YAHOO.util.Dom.removeClass(strCalendarContainerId, "loadingContainer");
	    });
	});
};

KAJONA.admin.calendar.initCalendar = function(strCalendarId, strCalendarContainerId) {
	var calendar = new YAHOO.widget.Calendar(strCalendarContainerId);
	calendar.cfg.setProperty("WEEKDAYS_SHORT", KAJONA.admin.lang.toolsetCalendarWeekday);
	calendar.cfg.setProperty("MONTHS_LONG", KAJONA.admin.lang.toolsetCalendarMonth);
	calendar.cfg.setProperty("START_WEEKDAY", 1);

	var handleSelect = function(type, args, obj) {
		var dates = args[0];
		var date = dates[0];
		var year = date[0], month = (date[1] < 10 ? '0'+date[1]:date[1]), day = (date[2] < 10 ? '0'+date[2]:date[2]);
		//write to fields
		document.getElementById(strCalendarId+"_day").value = day;
		document.getElementById(strCalendarId+"_month").value = month;
		document.getElementById(strCalendarId+"_year").value = year;

		//disabled because of JS error: this.config is null
		//calendar.destroy();
		KAJONA.util.fold(strCalendarContainerId);
	};

	//check for values in date form
	var formDate = [document.getElementById(strCalendarId+"_day").value, document.getElementById(strCalendarId+"_month").value, document.getElementById(strCalendarId+"_year").value];
	if (formDate[0] > 0 && formDate[1] > 0 && formDate[2] > 0) {
		calendar.select(formDate[1]+'/'+formDate[0]+'/'+formDate[2]);

		var selectedDates = calendar.getSelectedDates();
		if (selectedDates.length > 0) {
			var firstDate = selectedDates[0];
			calendar.cfg.setProperty("pagedate", (firstDate.getMonth()+1) + "/" + firstDate.getFullYear());
		}
	}

	calendar.selectEvent.subscribe(handleSelect, calendar, true);
	calendar.render();
};


/**
 * Form manangement
 */
KAJONA.admin.forms = {};
KAJONA.admin.forms.renderMandatoryFields = function(arrFields) {

    for(var i=0; i<arrFields.length; i++) {
        var arrElement = arrFields[i];
        if(arrElement.length == 2) {
            if(arrElement[1] == 'date') {
                YAHOO.util.Dom.addClass(arrElement[0]+"_day", "mandatoryFormElement");
                YAHOO.util.Dom.addClass(arrElement[0]+"_month", "mandatoryFormElement");
                YAHOO.util.Dom.addClass(arrElement[0]+"_year", "mandatoryFormElement");
            }
            else
                YAHOO.util.Dom.addClass(arrElement[0], "mandatoryFormElement");
        }
    }
};

/**
 * Dashboard calendar functions
 */
KAJONA.admin.dashboardCalendar = {};
KAJONA.admin.dashboardCalendar.eventMouseOver = function(strSourceId) {
    if(strSourceId == "")
        return;

    var sourceArray = eval("kj_cal_"+strSourceId);
    if(typeof sourceArray != undefined) {
        for(var i=0; i< sourceArray.length; i++) {
            YAHOO.util.Dom.addClass("event_"+sourceArray[i], "mouseOver");
        }
    }
};

KAJONA.admin.dashboardCalendar.eventMouseOut = function(strSourceId) {
    if(strSourceId == "")
        return;

    var sourceArray = eval("kj_cal_"+strSourceId);
    if(typeof sourceArray != undefined) {
        for(var i=0; i< sourceArray.length; i++) {
            YAHOO.util.Dom.removeClass("event_"+sourceArray[i], "mouseOver");
        }
    }
};

/**
 * Context menus
 */
KAJONA.admin.contextMenu = {
    menus: {},

    addElements: function (strIdentifier, arrElements) {
		this.menus[strIdentifier] = {
			elements: arrElements
		};
	},

	showElementMenu: function (strIdentifier, objAttach) {
        KAJONA.admin.tooltip.hide();

		var arrEntry = this.menus[strIdentifier];
		var arrElements = arrEntry["elements"];
		var menu;

		if (YAHOO.lang.isUndefined(arrEntry["menu"])) {
			arrEntry["menu"] = menu = new YAHOO.widget.Menu("menu_"+strIdentifier, {
				shadow: false,
				lazyLoad: true
			});

			var handleClick = function (strType, arrArgs, objElement) {
				eval(objElement.elementAction);
			};

			for (var i=0; i<arrElements.length; i++) {
				var e = arrElements[i];
                if(typeof e != 'undefined')
                    menu.addItem({ text: e.elementName, onclick: {fn: handleClick, obj: e} });
			}
			menu.render("menuContainer_"+strIdentifier);
		} else {
			menu = arrEntry["menu"];
		}
		var buttonRegion = YAHOO.util.Region.getRegion(objAttach);
		menu.cfg.setProperty("x", buttonRegion.left);
		menu.cfg.setProperty("y", buttonRegion.top);
		menu.show();
	}
};


KAJONA.admin.openPrintView = function(strUrlToLoad) {
    var intWidth = YAHOO.util.Dom.getViewportWidth() * 0.8;
    var intHeight = YAHOO.util.Dom.getViewportHeight() * 0.9;

    if(strUrlToLoad == null)
        strUrlToLoad = location.href;

    strUrlToLoad = strUrlToLoad.replace(/#/g, '')+"&printView=1";

    if(strUrlToLoad.indexOf('html&')) {
        strUrlToLoad = strUrlToLoad.replace(/html&/g, 'html?');
    }

    KAJONA.admin.folderview.dialog.setContentIFrame(strUrlToLoad);

    KAJONA.admin.folderview.dialog.init(intWidth+"px", intHeight+"px"); return false;
};


