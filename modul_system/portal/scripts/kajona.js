//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
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

//deprecated, use YAHOO.util.Event.onDOMReady instead, if YUI loaded
function addLoadEvent(func) {
	var oldonload = window.onload;
    if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if(oldonload) {
				oldonload();
			}
			func();
		};
	}
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

	// Loader object for dynamically loading additional js and css files
	Loader : function() {
		var additionalFileCounter = 0;
		this.jsBase = KAJONA_WEBPATH + "/portal/scripts/";

		this.yuiBase = this.jsBase + "yui/";

		var loader = new YAHOO.util.YUILoader( {
			base :this.yuiBase,

			// filter: "DEBUG", //use debug versions

			onFailure : function(o) {
				alert("error: " + YAHOO.lang.dump(o));
			}
		});

		this.addYUIComponents = function(componentList) {
			loader.require(componentList);
		}

		this.addFile = function(file, type) {
			loader.addModule( {
				name :"additionalFile" + additionalFileCounter,
				type :type,
				skinnable :false,
				fullpath :file
			});

			loader.require("additionalFile" + additionalFileCounter);
			additionalFileCounter++;
		},

		this.addJavascriptFile = function(filename) {
			this.addFile(this.jsBase+filename, "js");
		},

		this.addCssFile = function(fileWithPath) {
			this.addFile(fileWithPath, "css");
		},

		this.load = function(callback) {
			if (callback == null) {
				callback = function() {
				};
			}

			loader.onSuccess = callback;
			loader.insert();
		}
	},

	loadAjaxBase : function(callback, additionalJsFile) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "connection" ]);
		
		if (additionalJsFile != null) {
			l.addJavascriptFile(additionalJsFile);
		}

		l.load(callback);
	},

	loadAnimationBase : function(callback, additionalJsFile) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "animation" ]);
		
		if (additionalJsFile != null) {
			l.addJavascriptFile(additionalJsFile);
		}
		
		l.load(callback);
	},

	loadAutocompleteBase : function(callback, additionalJsFile) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "connection", "datasource", "autocomplete" ]);
		
		if (additionalJsFile != null) {
			l.addJavascriptFile(additionalJsFile);
		}
		
		l.load(callback);
	},

	loadCalendarBase : function(callback, additionalJsFile) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "calendar" ]);
		
		if (additionalJsFile != null) {
			l.addJavascriptFile(additionalJsFile);
		}
		
		l.load(callback);
	}

};