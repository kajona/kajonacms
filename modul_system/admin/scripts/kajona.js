//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2011 by Kajona, www.kajona.de
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
	catch(e) {	alert(e)
	}
}


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
 */
KAJONA.util.Loader = function (strScriptBase) {
	var scriptBase = KAJONA_WEBPATH + strScriptBase;
	var yuiBase = scriptBase + "yui/";
	var arrRequestedModules = {};
	var arrLoadedModules = {};
	var arrCallbacks = [];
	
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
				checkCallbacks();
			}
		});
		
		return yuiLoader;
	}
	
	function checkCallbacks() {
		//check if we're ready to call some registered callbacks
		for (var i = 0; i < arrCallbacks.length; i++) {
			if (!YAHOO.lang.isUndefined(arrCallbacks[i])) {
				var bitCallback = true;
				for (var j = 0; j < arrCallbacks[i].requiredModules.length; j++) {
					if (!(arrCallbacks[i].requiredModules[j] in arrLoadedModules)) {
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
	
	this.load = function(arrYuiComponents, arrFiles, callback) {
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
				for (var i = 0; i < arrFilesToLoad.length; i++) {
                    var fileType = arrFilesToLoad[i].substr(arrFilesToLoad[i].length-2, 2) == 'js' ? 'js' : 'css';
                    
                    var filter = { 
        				'searchExp': "\\."+fileType, 
        				'replaceStr': "."+fileType+"?"+KAJONA_BROWSER_CACHEBUSTER
        			};
                    var url = arrFilesToLoad[i].replace(new RegExp(filter.searchExp, 'g'), filter.replaceStr);

					yuiLoader.addModule( { 
						name : arrFilesToLoad[i],
						type : fileType,
						skinnable : false,
						fullpath : url
					});

					yuiLoader.require(arrFilesToLoad[i]);
					arrRequestedModules[arrFilesToLoad[i]] = true;
				}
				
				//fire YUILoader after the onDOMReady event
				YAHOO.util.Event.onDOMReady(function () {
					yuiLoader.insert();
				});
			}
		}
	}

	//for compatibility with Kajona templates pre 3.3.0
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
KAJONA.admin.loader = new KAJONA.util.Loader("/admin/scripts/");

/*
 * extend the loader with predefined helper functions
 */
KAJONA.admin.loader.loadAjaxBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "connection" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
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
	    KAJONA_WEBPATH + "/admin/scripts/yui/calendar/calendar-min.js",
        KAJONA_WEBPATH + "/admin/scripts/yui/calendar/assets/calendar.css"
	];
    
	if (!YAHOO.lang.isUndefined(arrAdditionalFiles)) {
		arrCustomFiles.push(this.convertAdditionalFiles(arrAdditionalFiles));
	}
    
	this.load(null, arrCustomFiles, objCallback);
};

KAJONA.admin.loader.loadUploaderBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "uploader", "swf" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.admin.loader.loadImagecropperBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "imagecropper" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.admin.loader.loadDialogBase = function(objCallback, arrAdditionalFiles) {
	var arrCustomFiles = [
	    KAJONA_WEBPATH + "/admin/scripts/yui/container/container-min.js",
	    KAJONA_WEBPATH+"/admin/scripts/yui/resize/resize-min.js"
	];
	if (!YAHOO.lang.isUndefined(arrAdditionalFiles)) {
		arrCustomFiles.push(this.convertAdditionalFiles(arrAdditionalFiles));
	}
	this.load([ "container", "element", "dragdrop" ], arrCustomFiles, objCallback);
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
	 */
	selectCallback: function (arrTargetsValues) {
		if (window.opener) {
			window.opener.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
		} else if (parent) {
			parent.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
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
	    		var formField = YAHOO.util.Dom.get(arrTargetsValues[i][0]);
	    		
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
		if(message.indexOf("<message>") != -1 && KAJONA_DEBUG > 0) {
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
	}
	
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
	}

	this.setContentRaw = function(strContent) {
		document.getElementById(this.containerId + "_content").innerHTML = strContent;
		//center the dialog (later() as workaround to add a minimal delay)
		YAHOO.lang.later(10, this, function() {this.dialog.center();});
	}
	
	this.setContentIFrame = function(strUrl) {
		this.iframeId = this.containerId+"_iframe";
        this.iframeURL = strUrl;
        //commented, now called below
		//document.getElementById(this.containerId + "_content").innerHTML = "<iframe src=\""+strUrl+"\" width=\"100%\" height=\"100%\" frameborder=\"0\" name=\""+this.iframeId+"\" id=\""+this.iframeId+"\"></iframe>";
	}

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
	}
	
	this.hide = function() {
		try {
			this.dialog.hide();
		}
		catch (e) {};
	}
	
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
	}
	
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
	}
}


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
        
        KAJONA.admin.ajax.executeSystemtask(strTaskname, strAdditionalParam, {
            success : function(o) {
                var strResponseText = o.responseText;
                
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
            },
            
            failure : function(o) {
                jsDialog_0.hide();
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />"+o.responseText);
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
	posConn: null,
	pagesConn: null,
	dashboardConn: null,
	statusConn: null,
	cropConn: null,
	rotateConn: null,
	genericCall: null,
    systemTaskCall: null,
    
    regularCallback: {
		success : function(o) {
			KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText)
		},
		failure : function(o) {
			KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
		}
	},

    executeSystemtask : function(strTaskname, strAdditionalParam, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=executeSystemTask&task='+strTaskname;
		var postBody = strAdditionalParam;

		if (KAJONA.admin.ajax.systemTaskCall == null
				|| !YAHOO.util.Connect
						.isCallInProgress(KAJONA.admin.ajax.systemTaskCall)) {
			KAJONA.admin.ajax.systemTaskCall = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, objCallback, postBody);
		}
	},

	genericAjaxCall : function(module, action, systemid, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
		var postBody = 'systemid=' + systemid;
	
        KAJONA.admin.ajax.genericCall = YAHOO.util.Connect.asyncRequest(
                'POST', postTarget, objCallback, postBody);
	},

	setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setAbsolutePosition';
		var postBody = 'systemid=' + systemIdToMove + '&listPos=' + intNewPos;

        if(typeof objCallback == 'undefined' || objCallback == null)
            objCallback = KAJONA.admin.ajax.regularCallback;

		if (KAJONA.admin.ajax.posConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(KAJONA.admin.ajax.posConn)) {
			KAJONA.admin.ajax.posConn = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, objCallback, postBody);
		}
	},

	setDashboardPos : function(systemIdToMove, intNewPos, strIdOfList) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=dashboard&action=setDashboardPosition';
		var postBody = 'systemid=' + systemIdToMove + '&listPos=' + intNewPos
				+ '&listId=' + strIdOfList;

		if (KAJONA.admin.ajax.dashboardConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(KAJONA.admin.ajax.dashboardConn)) {
			KAJONA.admin.ajax.dashboardConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, KAJONA.admin.ajax.regularCallback, postBody);
		}
	},

	setSystemStatus : function(strSystemIdToSet) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setStatus';
		var postBody = 'systemid=' + strSystemIdToSet;
		
        var objCallback = {
            success: function(o) { 
				KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
			
				if (o.responseText.indexOf('<error>') == -1 && o.responseText.indexOf('<html>') == -1) {
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
        	},
        	
            failure: function(o) { 
        		KAJONA.admin.statusDisplay.messageError(o.responseText);
        	}
        };

		if (KAJONA.admin.ajax.statusConn == null || !YAHOO.util.Connect.isCallInProgress(KAJONA.admin.ajax.statusConn)) {
			KAJONA.admin.ajax.statusConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, objCallback, postBody);
		}
	},

	saveImageCropping : function(intX, intY, intWidth, intHeight, strRepoId,
			strFolder, strFile, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=filemanager&action=saveCropping';
		var postBody = 'systemid=' + strRepoId + '&folder=' + strFolder
				+ '&file=' + strFile + '&intX=' + intX + '&intY=' + intY
				+ '&intWidth=' + intWidth + '&intHeight=' + intHeight + '';

		if (KAJONA.admin.ajax.cropConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(KAJONA.admin.ajax.cropConn)) {
			KAJONA.admin.ajax.cropConn = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, objCallback, postBody);
		}
	},

	saveImageRotating : function(intAngle, strRepoId, strFolder, strFile,
			objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=filemanager&action=rotate';
		var postBody = 'systemid=' + strRepoId + '&folder=' + strFolder
				+ '&file=' + strFile + '&angle=' + intAngle + '';

		if (KAJONA.admin.ajax.rotateConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(KAJONA.admin.ajax.rotateConn)) {
			KAJONA.admin.ajax.rotateConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, objCallback, postBody);
		}
	},

    deleteFile : function (strFmRepoId, strFolder, strFile, strSourceModule, strSourceModuleAction, strSourceSystemId) {
        KAJONA.admin.ajax.genericAjaxCall("filemanager", "deleteFile", strFmRepoId+"&folder="+strFolder+"&file="+strFile, {
                success : function(o) {
                    KAJONA.admin.ajax.genericAjaxCall(strSourceModule, strSourceModuleAction, strSourceSystemId, {
							success : function(o) {
								location.reload();
							},
							failure : function(o) {
								KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
							}
						}
						);
                },
                failure : function(o) {
                    KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    deleteFolder : function (strFmRepoId, strFolder, strSourceModule, strSourceModuleAction) {
        KAJONA.admin.ajax.genericAjaxCall("filemanager", "deleteFolder", strFmRepoId+"&folder="+strFolder, {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        KAJONA.admin.ajax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
                                success : function(o) {
                                    location.reload();
                                },
                                failure : function(o) {
                                    KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                                }
                            }
                        );
                    }
                },
                failure : function(o) {
                    KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    createFolder : function (strFmRepoId, strFolder, strSourceModule, strSourceModuleAction, strSourceSystemId) {
        KAJONA.admin.ajax.genericAjaxCall("filemanager", "createFolder", strFmRepoId+"&folder="+strFolder, {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        if(strSourceModule != "" && strSourceModuleAction != "") {
                            KAJONA.admin.ajax.genericAjaxCall(strSourceModule, strSourceModuleAction, strSourceSystemId, {
                                    success : function(o) {
                                        location.reload();
                                    },
                                    failure : function(o) {
                                        KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                                    }
                                }
                            );
                        }
                        else {
                            location.reload();
                        }
                    }
                },
                failure : function(o) {
                    KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    renameFile : function (strFmRepoId, strNewFilename, strOldFilename, strFolder, strSourceModule, strSourceModuleAction) {
        KAJONA.admin.ajax.genericAjaxCall("filemanager", "renameFile", strFmRepoId+"&folder="+strFolder+"&oldFilename="+strOldFilename+"&newFilename="+strNewFilename  , {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        if(strSourceModule != "" && strSourceModuleAction != "") {
                            KAJONA.admin.ajax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
                                    success : function(o) {
                                        location.reload();
                                    },
                                    failure : function(o) {
                                        KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                                    }
                                }
                            );
                        }
                        else {
                            location.reload();
                        }
                    }
                },
                failure : function(o) {
                    KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    loadPagesTreeViewNodes : function (node, fnLoadComplete)  {
        var nodeSystemid = node.systemid;
        KAJONA.admin.ajax.genericAjaxCall("pages", "getChildNodes", nodeSystemid, {
            success : function(o) {
                //check if answer contains an error
                if(o.responseText.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                    o.argument.fnLoadComplete();
                }
                else {
                    //success, start transforming the childs to tree-view nodes
                    //TODO: use xml parser instead of string-parsing
                    //process nodes
                    var intStart = o.responseText.indexOf("<entries>")+9;
                    var strEntries = o.responseText.substr(intStart, o.responseText.indexOf("</entries>")-intStart);

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

                            tempNode = new YAHOO.widget.TextNode({label:strName, href:strLink}, node);
                            tempNode.systemid = strSystemid;
                            tempNode.isLeaf = strLeaf == "true";
                            tempNode.labelStyle = intType == 0 ? "treeView-pagenode" : "treeView-pagealiasnode";

                            strEntries = strEntries.substr(strEntries.indexOf("</page>")+7);

                        }

                    }

                    o.argument.fnLoadComplete();
                    KAJONA.admin.treeview.checkInitialTreeViewToggling();
                }
            },
            failure : function(o) {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
            },
            argument: {
                "node": node,
                "fnLoadComplete": fnLoadComplete
            },

            timeout: 7000
        });
    },

    loadNavigationTreeViewNodes : function (node, fnLoadComplete)  {
        var nodeSystemid = node.systemid;
        KAJONA.admin.ajax.genericAjaxCall("navigation", "getChildNodes", nodeSystemid, {
            success : function(o) {
                //check if answer contains an error
                if(o.responseText.indexOf("<error>") != -1) {
                    KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                    o.argument.fnLoadComplete();
                }
                else {
                    //success, start transforming the childs to tree-view nodes
                    //TODO: use xml parser instead of string-parsing
                    //process nodes
                    var strPoints = o.responseText;

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

                        var tempNode = new YAHOO.widget.TextNode( {label:strName, href:strLink}, node);
                        tempNode.systemid = strSystemid;
                        tempNode.labelStyle = "treeView-navigationnode";
                        tempNode.isLeaf = strLeaf == "true";

                        strPoints = strPoints.substr(strPoints.indexOf("</point>")+8);
                    }

                    o.argument.fnLoadComplete();
                    KAJONA.admin.treeview.checkInitialTreeViewToggling();
                }
            },
            failure : function(o) {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
            },
            argument: {
                "node": node,
                "fnLoadComplete": fnLoadComplete
            },

            timeout: 7000
        });
    }

};

// --- FILEMANAGER ----------------------------------------------------------------------
KAJONA.admin.filemanager = {
	createFolder : function(strInputId, strRepoId, strRepoFolder, strSourceModule, strSourceAction, strSourceSystemid) {
	    var strNewFoldername = document.getElementById(strInputId).value;
	    if(strNewFoldername != "") {
	        KAJONA.admin.ajax.createFolder(strRepoId, strRepoFolder+"/"+strNewFoldername, strSourceModule, strSourceAction, strSourceSystemid);
	    }
	},
	
	renameFile : function(strInputId, strRepoId, strRepoFolder, strOldName, strSourceModule, strSourceAction) {
	    var strNewFilename = document.getElementById(strInputId).value;
	    if(strNewFilename != "") {
	        KAJONA.admin.ajax.renameFile(strRepoId, strNewFilename, strOldName, strRepoFolder, strSourceModule, strSourceAction);
	    }
	}	
};

KAJONA.admin.filemanager.Uploader = function(config) {
	var self = this;

	this.config = config;
	this.uploader;
	this.fileList;
	this.fileCount = 0;
	this.fileCountUploaded = 0;
	this.fileTotalSize = 0;
	this.listElementSample;
	
	this.init = function() {
		//check if Flash Player is available in needed version, otherwise abort and show fallback upload
		if (!YAHOO.util.SWFDetect.isFlashVersionAtLeast(9.045)) {
			try {
				document.getElementById('kajonaUploadFallbackContainer').style.display = 'block';
			} catch (e) {}
			
			document.getElementById('kajonaUploadButtonsContainer').style.display = 'none';
			return;
		}
		
		this.uploader = new YAHOO.widget.Uploader(self.config['overlayContainerId']);
		this.uploader.addListener('contentReady', self.handleContentReady);
		this.uploader.addListener('fileSelect', self.onFileSelect)
		this.uploader.addListener('uploadStart', self.onUploadStart);
		this.uploader.addListener('uploadProgress', self.onUploadProgress);
		this.uploader.addListener('uploadComplete', self.onUploadComplete);
		this.uploader.addListener('uploadCompleteData', self.onUploadResponse);
		this.uploader.addListener('uploadError', self.onUploadError);

		YAHOO.util.Event
				.onDOMReady( function() {
					KAJONA.admin.tooltip.hide();
					document.getElementById('kajonaUploadButtonsContainer').onmouseover = function() {};

					var uiLayer = YAHOO.util.Dom
							.getRegion(self.config['selectLinkId']);
					var overlay = YAHOO.util.Dom
							.get(self.config['overlayContainerId']);
					YAHOO.util.Dom.setStyle(overlay, 'width', uiLayer.right
							- uiLayer.left + "px");
					YAHOO.util.Dom.setStyle(overlay, 'height', uiLayer.bottom
							- uiLayer.top + "px");
				});
	}

	this.handleContentReady = function() {
		self.uploader.setAllowLogging(false);
		self.uploader.setAllowMultipleFiles(self.config['multipleFiles']);
		self.uploader.setSimUploadLimit(2);

		self.uploader.setFileFilters(new Array( {
			description : self.config['allowedFileTypesDescription']+" (max. "+self.bytesToString(self.config['maxFileSize'])+")",
			extensions : self.config['allowedFileTypes']
		}));

		//load sample file row for file list
		listElementSample = document.getElementById('kajonaUploadFileSample')
				.cloneNode(true);
	}

	this.onFileSelect = function(event) {
		self.fileList = event.fileList;

		jsDialog_0.setContentRaw(document.getElementById('kajonaUploadDialog').innerHTML);
		document.getElementById('kajonaUploadDialog').innerHTML = '';
		
		self.createFileList();
		
		jsDialog_0.init();
		YAHOO.util.Dom.setStyle(YAHOO.util.Dom.get('kajonaUploadDialog'),
				'display', "block");
	}

	this.createFileList = function() {
		var htmlList = document.getElementById('kajonaUploadFiles');
		var bitFileError = false;

		//count files (self.fileList.length doesn't work here)
		for (var i in self.fileList) {
			self.fileCount++;
		}
		
		//sort file list, otherwise the upload will start with the last file in the list
		var sortedFileList = new Array();
		var tempFileCount = 0;
		for (var i in self.fileList) {
			var entry = self.fileList[i];
			var entryId = self.fileCount - tempFileCount;
			sortedFileList[entryId] = entry;
			tempFileCount++;
		}
		
		//create table row for each file
		for (var i in sortedFileList) {
			var entry = sortedFileList[i];

			//check if file is already in list
			if (document.getElementById('kajonaUploadFile_' + entry['id']) == null) {
				var listElement = listElementSample.cloneNode(true);
				listElement.setAttribute('id', 'kajonaUploadFile_' + entry['id']);

				var filename = YAHOO.util.Dom.getElementsByClassName(
						'filename', 'div', listElement)[0];

				filename.innerHTML = entry['name'].substring(0, 30) + (entry['name'].length > 30 ? "...":"") + " ("+self.bytesToString(entry['size'])+")";
				
				//check if file size exceeds upload limit
				if (entry['size'] > self.config['maxFileSize']) {
					listElement.className = "error";
					bitFileError = true;
				}
				
				self.fileTotalSize += entry['size'];
				
				htmlList.appendChild(listElement);
			}
		}
		
		document.getElementById("kajonaUploadFilesTotal").innerHTML = self.fileCount;
		document.getElementById("kajonaUploadFilesTotalSize").innerHTML = self.bytesToString(self.fileTotalSize);

		//disable upload and show error if some files can't be uploaded
		if (bitFileError) {
			document.getElementById(self.config['uploadLinkId']).style.visibility = "hidden";
			document.getElementById("kajonaUploadError").style.display = "block";
		} else {
			document.getElementById(self.config['uploadLinkId']).onclick = function() {
				this.style.visibility = "hidden";
				self.upload();
				return false;
			};			
		}
		
		document.getElementById(self.config['cancelLinkId']).onclick = function() {
			YAHOO.util.Event.removeListener(window, 'beforeunload');
			
			self.uploader.cancel();
			location.reload();
			return false;
		};
	}

	this.upload = function() {
		if (self.fileList != null) {
			self.uploader.uploadAll(self.config['uploadUrl'], "POST",
					self.config['uploadUrlParams'],
					self.config['uploadInputName']);
			
			//show nice progress cursor
			document.getElementsByTagName("body")[0].style.cursor = "progress";
			
            //show confirm box if upload is still running when existing the page
            YAHOO.util.Event.addListener(window, 'beforeunload', this.showWarningNotComplete);
		}
	}

	this.onUploadProgress = function(event) {
		var row = document.getElementById('kajonaUploadFile_' + event['id']);
		row.className = "active";
		var progress = Math.round(100 * (event["bytesLoaded"] / event["bytesTotal"]));
		YAHOO.util.Dom.getElementsByClassName('progress', 'div', row)[0].innerHTML = progress+"%";
		YAHOO.util.Dom.getElementsByClassName('progressBar', 'div', row)[0].innerHTML = "<div style='width:" + progress + "%;'></div>";
	}

	this.onUploadComplete = function(event) {
		var row = document.getElementById('kajonaUploadFile_' + event['id']);
		YAHOO.util.Dom.getElementsByClassName('progress', 'div', row)[0].innerHTML = "100%";
		YAHOO.util.Dom.getElementsByClassName('progressBar', 'div', row)[0].innerHTML = "<div style='width:100%;'></div>";

		self.fileCountUploaded++;

		//reload page if all files are uploaded
		if (self.fileCount == self.fileCountUploaded) {
			self.onUploadCompleteAll();
		}
	}

	this.onUploadCompleteAll = function() {
		YAHOO.util.Event.removeListener(window, 'beforeunload');
		
		//check if callback method is available
        try {
            kajonaUploaderCallback();
        }
        catch (e) {
            location.reload();
        }
	}

	this.onUploadStart = function(event) {
		row = document.getElementById('kajonaUploadFile_' + event['id']);
		row.className = "active";
	}

	this.onUploadError = function(event) {
		YAHOO.util.Event.removeListener(window, 'beforeunload');
		alert('An error occurred while uploading file "'+self.fileList[event['id']]['name']+'". Please try again.');
		location.reload();
	}

	this.onUploadResponse = function(event) {
		if (event['data'].indexOf('<error>') != -1) {
			var intStart = event['data'].indexOf("<error>")+7;
			var responseText = event['data'].substr(intStart, event['data'].indexOf("</error>")-intStart);
			
			document.getElementById('kajonaUploadFile_' + event['id']).className = "error";
			alert('Error on file '+self.fileList[event['id']]['name']+':\n'+responseText);
		}
	}

	this.bytesToString = function(intBytes) {
		if (intBytes == 0) {
			return "0 B"
		}
		
		var entities = [ "B", "KB", "MB", "GB" ];
		var entity = Math.floor(Math.log(intBytes) / Math.log(1024));
		return (intBytes / Math.pow(1024, Math.floor(entity))).toFixed(2) + " "
				+ entities[entity];
	}

	this.showWarningNotComplete = function(event) {
    	event.returnValue = self.config['warningNotComplete'];
	}
}


//--- image-editor ----------------------------------------------------------------------
KAJONA.admin.filemanager.imageEditor = {
    cropArea : null,
    fm_cropObj : null,
    fm_image_isScaled : true,

    showRealSize : function () {
        document.getElementById('fm_filemanagerPic').src = fm_image_rawurl + "&x="
            + (new Date()).getMilliseconds();
        
        this.fm_image_isScaled = false;

        this.hideCropping();
    },

    showPreview : function () {
        document.getElementById('fm_filemanagerPic').src = fm_image_scaledurl.replace("__width__", fm_image_scaledMaxWidth).replace("__height__", fm_image_scaledMaxHeight)
            + "&x=" + (new Date()).getMilliseconds();
        this.fm_image_isScaled = true;

        this.hideCropping();
    },

    showCropping : function () {
        // init the cropping
        if (this.fm_cropObj == null) {
        	this.fm_cropObj = new YAHOO.widget.ImageCropper('fm_filemanagerPic', {
                status :true
            });
            document.getElementById("accept_icon").src = document
                    .getElementById("accept_icon").src.replace(
                    "icon_crop_acceptDisabled.gif", "icon_crop_accept.gif");

            YAHOO.util.Event.addListener("fm_filemanagerPic_wrap", 'dblclick', function (event) {
            	KAJONA.admin.filemanager.imageEditor.saveCropping();
            });
            
            //show confirm box when existing the page without saving the cropping
            YAHOO.util.Event.addListener(window, 'beforeunload', function (event) {
            	event.returnValue = fm_warning_unsavedHint;
            });
        } else {
        	this.hideCropping();
        }
    },
    
    hideCropping : function () {
        if (this.fm_cropObj != null) {
        	YAHOO.util.Event.removeListener(window, 'beforeunload');
        	
        	this.fm_cropObj.destroy();
        	this.fm_cropObj = null;
            document.getElementById("accept_icon").src = document
                    .getElementById("accept_icon").src.replace(
                    "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
        }
    },
   
    saveCropping : function () {   	
        if (this.fm_cropObj != null) {
        	YAHOO.util.Event.removeListener(window, 'beforeunload');
        	
            init_fm_crop_save_warning_dialog();
        }
    },
    
    saveCroppingToBackend : function () {  	
        jsDialog_1.hide();
        init_fm_screenlock_dialog();
        this.cropArea = this.fm_cropObj.getCropCoords();
        if (fm_image_isScaled) {
            // recalculate the "real" crop-coordinates
            var intScaledWidth = document.getElementById('fm_filemanagerPic').width;
            var intScaledHeight = document.getElementById('fm_filemanagerPic').height;
            var intOriginalWidth = document.getElementById('fm_int_realwidth').value;
            var intOriginalHeigth = document.getElementById('fm_int_realheight').value;

            this.cropArea.left = Math.floor(this.cropArea.left * (intOriginalWidth / intScaledWidth));
            this.cropArea.top = Math.floor(this.cropArea.top * (intOriginalHeigth / intScaledHeight));
            this.cropArea.width = Math.floor(this.cropArea.width * (intOriginalWidth / intScaledWidth));
            this.cropArea.height = Math.floor(this.cropArea.height * (intOriginalHeigth / intScaledHeight));
        }
        
        var callback = {
            success : function(o) {
        		var iE = KAJONA.admin.filemanager.imageEditor;
                KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);
                iE.fm_cropObj.destroy();
                iE.fm_cropObj = null;
                document.getElementById("accept_icon").src = document
                        .getElementById("accept_icon").src.replace(
                        "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
                document.getElementById('fm_image_dimensions').innerHTML = iE.cropArea.width
                        + ' x ' + iE.cropArea.height;
                document.getElementById('fm_image_size').innerHTML = 'n.a.';
                document.getElementById('fm_int_realwidth').value = iE.cropArea.width;
                document.getElementById('fm_int_realheight').value = iE.cropArea.height;

                if (this.fm_image_isScaled) {
                	iE.showPreview();
                } else {
                	iE.showRealSize();
                }

                iE.cropArea = null;

                hide_fm_screenlock_dialog();
            },
            failure : function(o) {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>"
                        + o.responseText);
                hide_fm_screenlock_dialog();
            }
        };
        
        KAJONA.admin.ajax.saveImageCropping(this.cropArea.left, this.cropArea.top,
        		this.cropArea.width, this.cropArea.height, fm_repo_id, fm_folder, fm_file, callback);
    },

    rotate : function (intAngle) {   	
        init_fm_screenlock_dialog();
        
        var callback = {
            success : function(o) {
        		var iE = KAJONA.admin.filemanager.imageEditor;
                KAJONA.admin.statusDisplay.displayXMLMessage(o.responseText);

                if (iE.fm_cropObj != null) {
                	iE.fm_cropObj.destroy();
                	iE.fm_cropObj = null;
                    document.getElementById("accept_icon").src = document
                            .getElementById("accept_icon").src.replace(
                            "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
                }

                //switch width and height
                var intScaledMaxWidthOld = fm_image_scaledMaxWidth;
                fm_image_scaledMaxWidth = fm_image_scaledMaxHeight;
                fm_image_scaledMaxHeight = intScaledMaxWidthOld;

                if (iE.fm_image_isScaled) {
                	iE.showPreview();
                } else {
                	iE.showRealSize();
                }

                // update size-info & hidden elements
                var intWidthOld = document.getElementById('fm_int_realwidth').value;
                var intHeightOld = document.getElementById('fm_int_realheight').value;
                document.getElementById('fm_int_realwidth').value = intHeightOld;
                document.getElementById('fm_int_realheight').value = intWidthOld;
                document.getElementById('fm_image_dimensions').innerHTML = intHeightOld
                        + ' x ' + intWidthOld;

                hide_fm_screenlock_dialog();
            },
            failure : function(o) {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>"
                        + o.responseText);
                hide_fm_screenlock_dialog();
            }
        };
        
        KAJONA.admin.ajax.saveImageRotating(intAngle, fm_repo_id, fm_folder, fm_file, callback);
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
 * Tags-handling
 */
KAJONA.admin.tags = {};
KAJONA.admin.tags.saveTag = function(strTagname, strSystemid, strAttribute) {
    KAJONA.admin.ajax.genericAjaxCall("tags", "saveTag", strSystemid+"&tagname="+strTagname+"&attribute="+strAttribute, {
        success : function(o) {
            KAJONA.admin.tags.reloadTagList(strSystemid, strAttribute);
            document.getElementById('tagname').value='';
        },
        failure : function(o) {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
        }
    });
};

KAJONA.admin.tags.reloadTagList = function(strSystemid, strAttribute) {

    YAHOO.util.Dom.addClass("tagsWrapper_"+strSystemid, "loadingContainer");

    KAJONA.admin.ajax.genericAjaxCall("tags", "tagList", strSystemid+"&attribute="+strAttribute, {
        success : function(o) {
            var intStart = o.responseText.indexOf("<tags>")+6;
            var strContent = o.responseText.substr(intStart, o.responseText.indexOf("</tags>")-intStart);
            YAHOO.util.Dom.removeClass("tagsWrapper_"+strSystemid, "loadingContainer");
            document.getElementById("tagsWrapper_"+strSystemid).innerHTML = strContent;
        },
        failure : function(o) {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
            YAHOO.util.Dom.removeClass("tagsWrapper_"+strSystemid, "loadingContainer");
        }
    });
};

KAJONA.admin.tags.removeTag = function(strTagId, strTargetSystemid, strAttribute) {
    KAJONA.admin.ajax.genericAjaxCall("tags", "removeTag", strTagId+"&targetid="+strTargetSystemid+"&attribute="+strAttribute, {
        success : function(o) {
            KAJONA.admin.tags.reloadTagList(strTargetSystemid, strAttribute);
            document.getElementById('tagname').value='';
        },
        failure : function(o) {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
        }
    });
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
}

KAJONA.admin.dashboardCalendar.eventMouseOut = function(strSourceId) {
    if(strSourceId == "")
        return;
    
    var sourceArray = eval("kj_cal_"+strSourceId);
    if(typeof sourceArray != undefined) {
        for(var i=0; i< sourceArray.length; i++) {
            YAHOO.util.Dom.removeClass("event_"+sourceArray[i], "mouseOver");
        }
    }
}

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
			}

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


KAJONA.admin.openPrintView = function() {
    var intWidth = YAHOO.util.Dom.getViewportWidth() * 0.8;
    var intHeight = YAHOO.util.Dom.getViewportHeight() * 0.9;
    
    KAJONA.admin.folderview.dialog.setContentIFrame(location.href.replace(/#/g, '')+"&printView=1"); 
    //KAJONA.admin.folderview.dialog.setTitle("TBD"); 
    KAJONA.admin.folderview.dialog.init(intWidth+"px", intHeight+"px"); return false;
};


