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
						fullpath : url,
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
 * Portal-specific functions
 * -------------------------------------------------------------------------
 */


/**
 * Loads/Reloads the Kajona captcha image with the given element id
 * 
 * @param {String} strImageId
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
	if (document.getElementById(imgID) == undefined) {
		var objImg=document.createElement("img");
		objImg.setAttribute("id", imgID);
		objImg.setAttribute("src", "image.php?image=kajonaCaptcha&maxWidth="+intWidth+"&reload="+timeCode);
		document.getElementById(containerName).appendChild(objImg);
	} else {
		var objImg = document.getElementById(imgID);
		objImg.src = objImg.src + "&reload="+timeCode;
	}
};

/** 
 * Tooltips
 * 
 * originally based on Bubble Tooltips by Alessandro Fulciniti (http://pro.html.it - http://web-graphics.com)
 */
KAJONA.portal.tooltip = (function() {
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
		tooltip.className = "kajonaTooltip";
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
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 * 
 * Simply use any of the predefined helpers, e.g.:
 * 	   KAJONA.portal.loader.loadAjaxBase(callback, "rating.js");
 * 
 * Or if you want to add your custom YUI components and own files (with absolute path), e.g.:
 *     KAJONA.portal.loader.load(
 *         ["dragdrop", "animation", "container"],
 *         [KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base-min.js",
 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.css",
 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/vanillamin/vanillamin.css"],
 *         callback
 *     );
 *
 */
KAJONA.portal.loader = new KAJONA.util.Loader("/portal/scripts/");

/*
 * extend the loader with predefined helper functions
 */
KAJONA.portal.loader.loadAjaxBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "connection" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.portal.loader.loadAnimationBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "animation" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.portal.loader.loadAutocompleteBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "connection", "datasource", "autocomplete" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

KAJONA.portal.loader.loadCalendarBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "calendar" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};


/* 
 * aliases to stay compatible with old templates
 * will be removed with Kajona 3.4 or 3.5
 */
var kajonaAjaxHelper = KAJONA.portal.loader;
var reloadCaptcha = function() {KAJONA.portal.loadCaptcha();};
var kajonaTooltip = KAJONA.portal.tooltip;
var fold = KAJONA.util.fold;