//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
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
	
	showNewMenu: function (strPlaceholder, objAttach) {
		kajonaAdminTooltip.hide();
		
		kajonaAjaxHelper.Loader.load(["menu"], null, function() {
			var arrPlaceholder = kajonaPeNewMenus[strPlaceholder];
			var arrElements = arrPlaceholder["elements"];
			var menu;
			
			if (YAHOO.lang.isUndefined(arrPlaceholder["menu"])) {
				arrPlaceholder["menu"] = menu = new YAHOO.widget.Menu("menu_"+strPlaceholder, {
					shadow: false,
					lazyLoad: true
				});
				
				var handleClick = function (strType, arrArgs, objElement) {
					kajonaPortalEditorHelper.openDialog(objElement.elementHref);
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
		});

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
		kajonaAdminTooltip.hide(e);
		kajonaAdminTooltip.container.appendChild(this.tooltip);
		kajonaAdminTooltip.locate(e);
	},
	
	hide : function(e) {
		var c = kajonaAdminTooltip.container;
		
		if (c != null && c.childNodes.length > 0) {
			c.removeChild(c.firstChild);
		}
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
		YAHOO.lang.later(10, this, function() {this.dialog.center();});
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
