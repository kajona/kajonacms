//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

function reloadCaptcha(imageID) {
	timeCode = new Date().getTime();
	codeImg = document.getElementById(imageID);
 	codeImg.src = codeImg.src+"&reload="+timeCode;
}

// --- TOOLTIPS -------------------------------------------------------------------------
// based on Bubble Tooltips by Alessandro Fulciniti
// (http://pro.html.it - http://web-graphics.com)
function enableTooltips(className) {
	var links, i, h;
	if (!document.getElementById || !document.getElementsByTagName) {
		return;
	}
	h = document.createElement("span");
	h.id = "btc";
	h.setAttribute("id", "btc");
	h.style.position = "absolute";
	h.style.zIndex = 2000;
	document.getElementsByTagName("body")[0].appendChild(h);
	links = document.getElementsByTagName("a");
	for (i = 0; i < links.length; i++) {
		if (className == null || className.length == 0) {
			Prepare(links[i]);
		} else {
			if (links[i].className == className) {
				Prepare(links[i])
			}
		}
	}
}

function Prepare(el) {
	var tooltip, t, s;
	t = el.getAttribute("title");
	if (t == null || t.length == 0) {
		return;
	}
	el.removeAttribute("title");
	tooltip = CreateEl("span", "tooltip");
	s = CreateEl("span", "top");
	s.appendChild(document.createTextNode(t));
	tooltip.appendChild(s);
	setOpacity(tooltip);
	el.tooltip = tooltip;
	el.onmouseover = showTooltip;
	el.onmouseout = hideTooltip;
	el.onmousemove = Locate;
}

function htmlTooltip(el, t) {
	var tooltip, s;
	if (t == null || t.length == 0) {
		return;
	}
	if (el.getAttribute("title")) {
		el.removeAttribute("title");
	}
	tooltip = CreateEl("span", "tooltip");
	s = CreateEl("span", "top");
	s.innerHTML = t;
	tooltip.appendChild(s);
	el.tooltip = tooltip;
	el.onmouseover = showTooltip;
	el.onmouseout = hideTooltip;
	el.onmousemove = Locate;
	el.onmouseover(el);
}

function showTooltip(e) {
	document.getElementById("btc").appendChild(this.tooltip);
	Locate(e);
}

function hideTooltip(e) {
	var d = document.getElementById("btc");
	if (d.childNodes.length > 0) {
		d.removeChild(d.firstChild);
	}
}

function setOpacity(el) {
	el.style.filter = "alpha(opacity:85)";
	el.style.KHTMLOpacity = "0.85";
	el.style.MozOpacity = "0.85";
	el.style.opacity = "0.85";
}

function CreateEl(t, c) {
	var x = document.createElement(t);
	x.className = c;
	x.style.display = "block";
	return (x);
}

function Locate(e) {
	var posx = 0, posy = 0, t;
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
	t = document.getElementById("btc");
	var left = (posx - t.offsetWidth);
	if (left - t.offsetWidth < 0) {
		left += t.offsetWidth;
	}
	t.style.top = (posy + 10) + "px";
	t.style.left = left + "px";
}

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