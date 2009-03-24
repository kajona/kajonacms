//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

var kajonaPortalEditorHelper = {
	portalEditorHover: function (elementSysId) {
	    divElement = document.getElementById('container_'+elementSysId);
	    divElement.className="peContainerHover";
	    menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuHover";
	},
	
	portalEditorOut: function (elementSysId) {
	    divElement = document.getElementById('container_'+elementSysId);
		divElement.className="peContainerOut";
	    menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuOut";
	},
	
	portalEditorStatus: function (status) {
	    var status = status == true ? 'true' : 'false';
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
	        window.location.replace(url+'?pe='+status);
	    } else {
	        window.location.replace(url+'&pe='+status);
	    }
	}
}

//--- TOOLTIPS -------------------------------------------------------------------------
//originally based on Bubble Tooltips by Alessandro Fulciniti
//(http://pro.html.it - http://web-graphics.com)
var kajonaAdminTooltip = {
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
		if (kajonaAdminTooltip.container == null) {
			var h = document.createElement("span");
			h.id = "kajonaAdminTooltipContainer";
			h.setAttribute("id", "kajonaAdminTooltipContainer");
			h.style.position = "absolute";
			h.style.zIndex = 2000;
			document.getElementsByTagName("body")[0].appendChild(h);
			kajonaAdminTooltip.container = h;
		}
		
		objElement.tooltip = tooltip;
		objElement.onmouseover = kajonaAdminTooltip.show;
		objElement.onmouseout = kajonaAdminTooltip.hide;
		objElement.onmousemove = kajonaAdminTooltip.locate;
		objElement.onmouseover(objElement);
	},
	
	show : function(e) {
		kajonaAdminTooltip.container.appendChild(this.tooltip);
		kajonaAdminTooltip.locate(e);
	},
	
	hide : function(e) {
		try {
			var c = kajonaAdminTooltip.container;
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
			posx = kajonaAdminTooltip.lastMouseX;
			posy = kajonaAdminTooltip.lastMouseY;
		} else {
			kajonaAdminTooltip.lastMouseX = posx;
			kajonaAdminTooltip.lastMouseY = posy;
		}
		
		c = kajonaAdminTooltip.container;
		var left = (posx - c.offsetWidth);
		if (left - c.offsetWidth < 0) {
			left += c.offsetWidth;
		}
		c.style.top = (posy + 10) + "px";
		c.style.left = left + "px";
	}
};

//--- LITTLE HELPERS --------------------------------------------------------------------
function addCss(file) {
	var l=document.createElement("link");
	l.setAttribute("type", "text/css");
	l.setAttribute("rel", "stylesheet");
	l.setAttribute("href", file);
	document.getElementsByTagName("head")[0].appendChild(l);
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