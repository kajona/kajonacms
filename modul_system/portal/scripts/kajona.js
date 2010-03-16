//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

function reloadCaptcha(imageID) {
	timeCode = new Date().getTime();
	codeImg = document.getElementById(imageID);
 	codeImg.src = codeImg.src+"&reload="+timeCode;
}

//--- TOOLTIPS -------------------------------------------------------------------------
//originally based on Bubble Tooltips by Alessandro Fulciniti
//(http://pro.html.it - http://web-graphics.com)
var kajonaTooltip = {
	container : null,
	lastMouseX : 0,
	lastMouseY : 0,
		
	add : function(objElement, strHtmlContent, bitOpacity) {
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
		if (kajonaTooltip.container == null) {
			var h = document.createElement("span");
			h.id = "kajonaTooltipContainer";
			h.setAttribute("id", "kajonaTooltipContainer");
			h.style.position = "absolute";
			h.style.zIndex = 2000;
			document.getElementsByTagName("body")[0].appendChild(h);
			kajonaTooltip.container = h;
		}
		
		objElement.tooltip = tooltip;
		objElement.onmouseover = kajonaTooltip.show;
		objElement.onmouseout = kajonaTooltip.hide;
		objElement.onmousemove = kajonaTooltip.locate;
		objElement.onmouseover(objElement);
	},
	
	show : function(e) {
		kajonaTooltip.hide(e);
		kajonaTooltip.container.appendChild(this.tooltip);
		kajonaTooltip.locate(e);
	},
	
	hide : function(e) {
		try {
			var c = kajonaTooltip.container;
			if (c.childNodes.length > 0) {
				c.removeChild(c.firstChild);
			}
		} catch (e) {}
	},
	
	locate : function(e) {
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
			posx = kajonaTooltip.lastMouseX;
			posy = kajonaTooltip.lastMouseY;
		} else {
			kajonaTooltip.lastMouseX = posx;
			kajonaTooltip.lastMouseY = posy;
		}
		
		c = kajonaTooltip.container;
		var left = (posx - c.offsetWidth);
		if (left - c.offsetWidth < 0) {
			left += c.offsetWidth;
		}
		c.style.top = (posy + 10) + "px";
		c.style.left = left + "px";
	}
};

//--- LITTLE HELPERS ------------------------------------------------------------------------------------
//deprecated, use kajonaAjaxHelper.Loader object instead
function addCss(file) {
	var l=document.createElement("link");
	l.setAttribute("type", "text/css");
	l.setAttribute("rel", "stylesheet");
	l.setAttribute("href", file);
	document.getElementsByTagName("head")[0].appendChild(l);
}

function inArray(needle, haystack) {
    for (var i = 0; i < haystack.length; i++) {
        if (haystack[i] == needle) {
            return true;
        }
    }
    return false;
}

function fold(id, callbackShow) {
	style = document.getElementById(id).style.display;
	if (style=='none') 	{
		document.getElementById(id).style.display='block';
		if (callbackShow != undefined) {
			callbackShow();
		}
    }
    else {
        document.getElementById(id).style.display='none';
    }
}


//--- AJAX-STUFF ------------------------------------------------------------------------
var kajonaAjaxHelper = {

	/*
	 * Loader for dynamically loading additional js and css files after the onDOMReady event
	 * 
	 * Simply use any of the predefined helpers, e.g.:
	 * 	   kajonaAjaxHelper.loadAjaxBase(callback, "rating.js");
	 * 
	 * Or if you want to add your custom YUI components and own files (with absolute path), e.g.:
	 *     kajonaAjaxHelper.Loader.load(
	 *         ["dragdrop", "animation", "container"],
	 *         [KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base-min.js",
	 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/build/photoviewer_base.css",
	 *          KAJONA_WEBPATH+"/portal/scripts/photoviewer/assets/skins/vanillamin/vanillamin.css"],
	 *         callback
	 *     );
	 */
	Loader : {
		yuiBase : KAJONA_WEBPATH + "/portal/scripts/yui/",
		yuiLoaderBeforeDOMReady : null,
		bitBeforeDOMReady : true,
		arrRequestedModules : {},
		arrLoadedModules : {},
		arrCallbacks : [],
		
		createYuiLoader : function () {
			//create instance of YUILoader
			var yuiLoader = new YAHOO.util.YUILoader({
				base : this.yuiBase,
		
				//filter: "DEBUG", //use debug versions
				/* TODO: add cache buster
				filter: { 
					'searchExp': "\\.js", 
					'replaceStr': ".js?123"
					},
				*/
		
				onFailure : function(o) {
					alert("File loading failed: " + YAHOO.lang.dump(o));
				},
								
				onProgress : function(o) {			
					kajonaAjaxHelper.Loader.arrLoadedModules[o.name] = true;
					kajonaAjaxHelper.Loader.checkCallbacks();
				}
			});
			
			return yuiLoader;
		},
		
		checkCallbacks : function() {
			//check if we're ready to call some registered callbacks
			var arrCallbacks = kajonaAjaxHelper.Loader.arrCallbacks;
			for (var i = 0; i < arrCallbacks.length; i++) {
				if (!YAHOO.lang.isUndefined(arrCallbacks[i])) {
					var bitCallback = true;
					for (var j = 0; j < arrCallbacks[i].requiredModules.length; j++) {
						if (!(arrCallbacks[i].requiredModules[j] in this.arrLoadedModules)) {
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
		},
		
		load : function(arrYuiComponents, arrFiles, callback) {
			var arrYuiComponentsToWaitFor = [];
			var arrFilesToWaitFor = [];		
			var arrYuiComponentsToLoad = [];
			var arrFilesToLoad = [];

			//check YUI components, if they are already loaded or requested
			if (YAHOO.lang.isArray(arrYuiComponents)) {
				for (var i = 0; i < arrYuiComponents.length; i++) {
					if (!(arrYuiComponents[i] in this.arrLoadedModules)) {
						arrYuiComponentsToWaitFor.push(arrYuiComponents[i]);
						if (!(arrYuiComponents[i] in this.arrRequestedModules)) {
							arrYuiComponentsToLoad.push(arrYuiComponents[i]);
						}
					}
				}
			}
			
			//check own JS/CSS files, if they are already loaded or requested
			if (YAHOO.lang.isArray(arrFiles)) {
				for (var i = 0; i < arrFiles.length; i++) {
					if (!(arrFiles[i] in this.arrLoadedModules)) {
						arrFilesToWaitFor.push(arrFiles[i]);
						if (!(arrFiles[i] in this.arrRequestedModules)) {
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
				var yuiLoader;
				
				//are there components/files to load which are not already requested?
				if (arrYuiComponentsToLoad.length > 0 || arrFilesToLoad.length > 0) {
					//decide if a new YUILoader instance should be created
					//all files added before the onDOMReady event uses the same instance
					if (this.bitBeforeDOMReady && YAHOO.lang.isNull(this.yuiLoaderBeforeDOMReady)) {
						this.yuiLoaderBeforeDOMReady = this.createYuiLoader();
						yuiLoader = this.yuiLoaderBeforeDOMReady;
						
						//start loading right after DOM is ready
						YAHOO.util.Event.onDOMReady(function () {
							kajonaAjaxHelper.Loader.yuiLoaderBeforeDOMReady.insert();
							kajonaAjaxHelper.Loader.bitBeforeDOMReady = false;
						});
					} else if (this.bitBeforeDOMReady) {
						yuiLoader = this.yuiLoaderBeforeDOMReady;
					} else {
						yuiLoader = this.createYuiLoader();
					}
					
					for (var i = 0; i < arrYuiComponentsToLoad.length; i++) {
						yuiLoader.require(arrYuiComponentsToLoad[i]);
						this.arrRequestedModules[arrYuiComponentsToLoad[i]] = true;
					}
					for (var i = 0; i < arrFilesToLoad.length; i++) {
						yuiLoader.addModule( {
							name : arrFilesToLoad[i],
							type : arrFilesToLoad[i].substr(arrFilesToLoad[i].length-2, 2) == 'js' ? 'js' : 'css',
							skinnable : false,
							fullpath : arrFilesToLoad[i]
						});
	
						yuiLoader.require(arrFilesToLoad[i]);
						this.arrRequestedModules[arrFilesToLoad[i]] = true;
					}
				}
				
				//register the callback to be called later on
				if (YAHOO.lang.isFunction(callback)) {
					this.arrCallbacks.push({
						'callback' : callback,
						'requiredModules' : arrYuiComponentsToWaitFor.concat(arrFilesToWaitFor)
					});
				}
				
				//fire YUILoader if this function is called after the onDOMReady event
				if ((arrYuiComponentsToLoad.length > 0 || arrFilesToLoad.length > 0) && !this.bitBeforeDOMReady) {
					yuiLoader.insert();
				}
			}
		}
	},
			
	/*
	 * For compatibility with Kajona templates pre 3.3.0
	 */
	convertAdditionalFiles : function(additionalFiles) {
		var scriptBase = KAJONA_WEBPATH + "/portal/scripts/";
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
	},
	
	/*
	 * Predefined helper functions
	 */
	loadAjaxBase : function(callback, additionalFiles) {
		this.Loader.load([ "connection" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadAnimationBase : function(callback, additionalFiles) {
		this.Loader.load([ "animation" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadAutocompleteBase : function(callback, additionalFiles) {
		this.Loader.load([ "connection", "datasource", "autocomplete" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadCalendarBase : function(callback, additionalJsFile) {
		this.Loader.load([ "calendar" ], this.convertAdditionalFiles(additionalFiles), callback);
	}

};