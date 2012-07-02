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
 * Object to show a modal dialog
 */
KAJONA.admin.ModalDialog = function(strDialogId, intDialogType, bitDragging, bitResizing) {
	this.dialog;
	this.containerId = strDialogId;
	this.iframeId;

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
		document.getElementById(this.containerId + "_content").innerHTML = "<iframe src=\""+strUrl+"\" width=\"100%\" height=\"100%\" frameborder=\"0\" name=\""+this.iframeId+"\" id=\""+this.iframeId+"\"></iframe>";
	}

	this.init = function() {
		this.dialog = new YAHOO.widget.Panel(this.containerId, {
			fixedcenter: true,
			close: false,
			draggable: false,
			dragOnly: true,
			underlay: "none",
			constraintoviewport: true,
			zindex: 4000,
			modal: true,
			visible: true
		});

		document.getElementById(this.containerId).style.display = "block";

		this.dialog.render(document.body);
		this.dialog.show();
		this.dialog.focusLast();

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