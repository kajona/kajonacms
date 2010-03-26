//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA == "undefined") {
	var KAJONA = {
		util: {},
		portal: {},
		admin: {}
	};
}


/* 
 * -------------------------------------------------------------------------
 * Global functions
 * -------------------------------------------------------------------------
 */

/*
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
					yuiLoader.addModule( {
						name : arrFilesToLoad[i],
						type : arrFilesToLoad[i].substr(arrFilesToLoad[i].length-2, 2) == 'js' ? 'js' : 'css',
						skinnable : false,
						fullpath : arrFilesToLoad[i]
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

	/*
	 * For compatibility with Kajona templates pre 3.3.0
	 */
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


/* 
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
	    var bitClose = confirm("Änderungen verwerfen und schließen?");
	    if(bitClose) {
	    	peDialog.hide();
	    }
	},
	
	addNewElements: function (strPlaceholder, strPlaceholderName, arrElements) {
		this.objPlaceholderWithElements[strPlaceholder] = {
			placeholderName: strPlaceholderName,
			elements: arrElements
		};
	},
	
	showNewElementMenu: function (strPlaceholder, objAttach) {
		kajonaAdminTooltip.hide();

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
			}

			for (var i=0; i<arrElements.length; i++) {
				var e = arrElements[i];
				menu.addItem({ text: e.elementName, onclick: {fn: handleClick, obj: e} });
			}
			menu.setItemGroupTitle(arrPlaceholder.placeholderName, 0);
			
			menu.render("menuContainer_"+strPlaceholder);
		} else {
			menu = arrPlaceholder["menu"];
		}
		var buttonRegion = YAHOO.util.Region.getRegion(objAttach);
		menu.cfg.setProperty("x", buttonRegion.right-8);
		menu.cfg.setProperty("y", buttonRegion.top);
		menu.show();
	}

}




function ModalDialog(strDialogId, intDialogType) {
	this.dialog = null;
	this.containerId = strDialogId;

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
		document.getElementById(this.containerId + "_content").innerHTML = "<iframe src=\""+strUrl+"\" width=\"100%\" height=\"450\" frameborder=\"0\" name=\"peIFrame\"></iframe>";
		//center the dialog (later() as workaround to add a minimal delay)
		//YAHOO.lang.later(10, this, function() {this.dialog.center();});
	}

	this.init = function() {
		document.body.style.overflow = "hidden";
		
		this.dialog = new YAHOO.widget.Panel(this.containerId, {
			fixedcenter :true,
			close :false,
			draggable :false,
			zindex :4000,
			modal :true,
			visible :true
		});

		this.dialog.render(document.body);
		this.dialog.show();
		this.dialog.focusLast();
	}

	this.hide = function() {
		document.body.style.overflow = "auto";
		try {
			this.dialog.hide();
		}
		catch (e) {};
	}
}



/*
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
KAJONA.admin.loader.loadPortaleditorBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "menu", "container" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};

/* 
 * aliases to stay compatible with old templates
 * will be removed with Kajona 3.4 or 3.5
 */
var kajonaAdminTooltip = KAJONA.admin.tooltip;