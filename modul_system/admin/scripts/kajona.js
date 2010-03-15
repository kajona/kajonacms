//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

//--- GLOBAL ELEMENTS, MOVER ------------------------------------------------------------
var currentMouseXPos;
var currentMouseYPos;
// used for "the mover" ;)
var objToMove = null;
var objDiffX = 0;
var objDiffY = 0;

checkMousePosition = function(e) {
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

var objMover = {
	mousePressed :0,
	objPosX :0,
	objPosY :0,
	diffX :0,
	diffY :0,

	setMousePressed : function(obj) {
		objToMove = obj;
		objDiffX = currentMouseXPos - objToMove.offsetLeft;
		objDiffY = currentMouseYPos - objToMove.offsetTop;
	},

	unsetMousePressed : function() {
		objToMove = null;
	}
}

// --- MISC -----------------------------------------------------------------------------
function fold(id, callbackShow) {
	var style = document.getElementById(id).style.display;
	if (style == 'none') {
		document.getElementById(id).style.display = 'block';
		if (callbackShow != undefined) {
			callbackShow();
		}
	} else {
		document.getElementById(id).style.display = 'none';
	}
}

function foldImage(id, bildid, bild_da, bild_weg) {
	style = document.getElementById(id).style.display;
	if (style == 'none') {
		document.getElementById(id).style.display = 'block';
		document.getElementById(bildid).src = bild_da;
	} else {
		document.getElementById(id).style.display = 'none';
		document.getElementById(bildid).src = bild_weg;
	}
}

function switchLanguage(strLanguageToLoad) {
	var url = window.location.href;
	url = url.replace(/(\?|&)language=([a-z]+)/, "");
	if (url.indexOf('?') == -1) {
		window.location.replace(url + '?language=' + strLanguageToLoad);
	} else {
		window.location.replace(url + '&language=' + strLanguageToLoad);
	}
}

function inArray(needle, haystack) {
	for ( var i = 0; i < haystack.length; i++) {
		if (haystack[i] == needle) {
			return true;
		}
	}
	return false;
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
	}

	this.init = function() {
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
		try {
			this.dialog.hide();
		}
		catch (e) {};
	}
}


// --- RIGHTS-STUFF ---------------------------------------------------------------------
function checkRightMatrix() {
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

// --- TOOLTIPS -------------------------------------------------------------------------
// originally based on Bubble Tooltips by Alessandro Fulciniti
// (http://pro.html.it - http://web-graphics.com)
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


// --- AJAX-STUFF -----------------------------------------------------------------------
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
		yuiBase : KAJONA_WEBPATH + "/admin/scripts/yui/",
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
		
		/*
		 * load([ "connection" ], [ "" ], callback);
		 * 
		 */
		load : function(arrYuiComponents, arrFiles, callback) {
			var yuiLoader;
			
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
			
			//list of required modules to load
			var arrRequiredModules = [];

			//add YUI components, but only if they are not already requested or loaded
			if (YAHOO.lang.isArray(arrYuiComponents)) {
				arrRequiredModules = arrRequiredModules.concat(arrYuiComponents);
				for (var i = 0; i < arrYuiComponents.length; i++) {
					if (!(arrYuiComponents[i] in this.arrLoadedModules)) {
						if (!(arrYuiComponents[i] in this.arrRequestedModules)) {
							yuiLoader.require(arrYuiComponents[i]);
							this.arrRequestedModules[arrYuiComponents[i]] = true;
						}
					}
				}
			}
			
			//add own JS/CSS files, but only if they are not already requested or loaded
			if (YAHOO.lang.isArray(arrFiles)) {
				arrRequiredModules = arrRequiredModules.concat(arrFiles);
				for (var i = 0; i < arrFiles.length; i++) {
					if (!(arrFiles[i] in this.arrLoadedModules)) {
						if (!(arrFiles[i] in this.arrRequestedModules)) {
							yuiLoader.addModule( {
								name : arrFiles[i],
								type : arrFiles[i].substr(arrFiles[i].length-2, 2) == 'js' ? 'js' : 'css',
								skinnable : false,
								fullpath : arrFiles[i]
							});
		
							yuiLoader.require(arrFiles[i]);
							this.arrRequestedModules[arrFiles[i]] = true;
						}
					}
				}
			}
			
			//if all modules are already loaded, execute the callback
			if (arrRequiredModules.length == 0) {
				if (YAHOO.lang.isFunction(callback)) {
					callback();
				}
			} else {
				//register the callback to be called later on
				if (YAHOO.lang.isFunction(callback)) {
					this.arrCallbacks.push({
						'callback' : callback,
						'requiredModules' : arrRequiredModules
					});
				}

				//fire YUILoader if this function is called after the onDOMReady event
				if (!this.bitBeforeDOMReady) {
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
		var customFiles = [
		    KAJONA_WEBPATH + "/admin/scripts/messagebox.js"
		];
		
		if (!YAHOO.lang.isUndefined(additionalFiles)) {
			customFiles.push(this.convertAdditionalFiles(additionalFiles));
		}

		this.Loader.load([ "connection" ], customFiles, callback);
	},
	
	loadDragNDropBase : function(callback, additionalFiles) {
		this.Loader.load([ "connection", "animation", "dragdrop" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadAnimationBase : function(callback, additionalFiles) {
		this.Loader.load([ "animation" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadAutocompleteBase : function(callback, additionalFiles) {
		this.Loader.load([ "connection", "datasource", "autocomplete" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadCalendarBase : function(callback, additionalFiles) {
		var customFiles = [
		    KAJONA_WEBPATH + "/admin/scripts/yui/calendar/calendar-min.js",
            KAJONA_WEBPATH + "/admin/scripts/yui/calendar/assets/calendar.css",
		];
		if (!YAHOO.lang.isUndefined(additionalFiles)) {
			customFiles.push(this.convertAdditionalFiles(additionalFiles));
		}
		this.Loader.load(null, customFiles, callback);
	},
	
	loadUploaderBase : function(callback, additionalFiles) {
		this.Loader.load([ "uploader" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadImagecropperBase : function(callback, additionalFiles) {
		this.Loader.load([ "imagecropper" ], this.convertAdditionalFiles(additionalFiles), callback);
	},
	
	loadDialogBase : function(callback, additionalFiles) {
		var customFiles = [
		    KAJONA_WEBPATH + "/admin/scripts/yui/container/container-min.js"
		];
		if (!YAHOO.lang.isUndefined(additionalFiles)) {
			customFiles.push(this.convertAdditionalFiles(additionalFiles));
		}
		this.Loader.load(null, customFiles, callback);
	},
	
	loadTreeviewBase : function(callback, additionalFiles) {
		this.Loader.load([ "treeview", "connection" ], this.convertAdditionalFiles(additionalFiles), callback);
	}
};

var regularCallback = {
	success : function(o) {
		kajonaStatusDisplay.displayXMLMessage(o.responseText)
	},
	failure : function(o) {
		kajonaStatusDisplay.messageError("<b>Request failed!</b>")
	}
};

var systemStatusCallback = function(o, bitSuccess) {
	if (bitSuccess) {
		kajonaStatusDisplay.displayXMLMessage(o.responseText);

		var strSystemid = o.argument[0];

		if (o.responseText.indexOf('<error>') == -1
				&& o.responseText.indexOf('<html>') == -1) {
			var image = document.getElementById('statusImage_' + strSystemid);
			var link = document.getElementById('statusLink_' + strSystemid);

			if (image.src.indexOf('icon_enabled.gif') != -1) {
				image.src = strInActiveImageSrc;
				image.setAttribute('alt', strInActiveText);
				link.setAttribute('title', strInActiveText);
			} else {
				image.src = strActiveImageSrc;
				image.setAttribute('alt', strActiveText);
				link.setAttribute('title', strActiveText);
			}
			
			kajonaAdminTooltip.add(link);
		}
	} else {
		kajonaStatusDisplay.messageError(o.responseText);
	}
};

var kajonaAdminAjax = {
	posConn :null,
	pagesConn :null,
	dashboardConn :null,
	statusConn :null,
	cropConn :null,
	rotateConn :null,
	genericCall :null,
    systemTaskCall : null,

    executeSystemtask : function(strTaskname, strAdditionalParam, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=executeSystemTask&task='+strTaskname;
		var postBody = strAdditionalParam;

		if (kajonaAdminAjax.systemTaskCall == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.systemTaskCall)) {
			kajonaAdminAjax.systemTaskCall = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, objCallback, postBody);
		}
	},

	genericAjaxCall : function(module, action, systemid, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
		var postBody = 'systemid=' + systemid;
	
		if (kajonaAdminAjax.genericCall == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.genericCall)) {
			kajonaAdminAjax.genericCall = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, objCallback, postBody);
		}
	},

	setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setAbsolutePosition';
		var postBody = 'systemid=' + systemIdToMove + '&listPos=' + intNewPos;

		if (kajonaAdminAjax.posConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.posConn)) {
			kajonaAdminAjax.posConn = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, regularCallback, postBody);
		}
	},

	setDashboardPos : function(systemIdToMove, intNewPos, strIdOfList) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=dashboard&action=setDashboardPosition';
		var postBody = 'systemid=' + systemIdToMove + '&listPos=' + intNewPos
				+ '&listId=' + strIdOfList;

		if (kajonaAdminAjax.dashboardConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.dashboardConn)) {
			kajonaAdminAjax.dashboardConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, regularCallback, postBody);
		}
	},

	setSystemStatus : function(systemIdToSet, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setStatus';
		var postBody = 'systemid=' + systemIdToSet;

		if (kajonaAdminAjax.statusConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.statusConn)) {
			kajonaAdminAjax.statusConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, objCallback, postBody);
		}
	},

	saveImageCropping : function(intX, intY, intWidth, intHeight, strRepoId,
			strFolder, strFile, objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=filemanager&action=saveCropping';
		var postBody = 'systemid=' + strRepoId + '&folder=' + strFolder
				+ '&file=' + strFile + '&intX=' + intX + '&intY=' + intY
				+ '&intWidth=' + intWidth + '&intHeight=' + intHeight + '';

		if (kajonaAdminAjax.cropConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.cropConn)) {
			kajonaAdminAjax.cropConn = YAHOO.util.Connect.asyncRequest('POST',
					postTarget, objCallback, postBody);
		}
	},

	saveImageRotating : function(intAngle, strRepoId, strFolder, strFile,
			objCallback) {
		var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module=filemanager&action=rotate';
		var postBody = 'systemid=' + strRepoId + '&folder=' + strFolder
				+ '&file=' + strFile + '&angle=' + intAngle + '';

		if (kajonaAdminAjax.rotateConn == null
				|| !YAHOO.util.Connect
						.isCallInProgress(kajonaAdminAjax.rotateConn)) {
			kajonaAdminAjax.rotateConn = YAHOO.util.Connect.asyncRequest(
					'POST', postTarget, objCallback, postBody);
		}
	},

    deleteFile : function (strFmRepoId, strFolder, strFile, strSourceModule, strSourceModuleAction) {
        kajonaAdminAjax.genericAjaxCall("filemanager", "deleteFile", strFmRepoId+"&folder="+strFolder+"&file="+strFile, {
                success : function(o) {
                    kajonaAdminAjax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
							success : function(o) {
								location.reload();
							},
							failure : function(o) {
								kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
							}
						}
						);
                },
                failure : function(o) {
                    kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    deleteFolder : function (strFmRepoId, strFolder, strSourceModule, strSourceModuleAction) {
        kajonaAdminAjax.genericAjaxCall("filemanager", "deleteFolder", strFmRepoId+"&folder="+strFolder, {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        kajonaStatusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        kajonaAdminAjax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
                                success : function(o) {
                                    location.reload();
                                },
                                failure : function(o) {
                                    kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                                }
                            }
                        );
                    }
                },
                failure : function(o) {
                    kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    createFolder : function (strFmRepoId, strFolder, strSourceModule, strSourceModuleAction) {
        kajonaAdminAjax.genericAjaxCall("filemanager", "createFolder", strFmRepoId+"&folder="+strFolder, {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        kajonaStatusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        if(strSourceModule != "" && strSourceModuleAction != "") {
                            kajonaAdminAjax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
                                    success : function(o) {
                                        location.reload();
                                    },
                                    failure : function(o) {
                                        kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
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
                    kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    renameFile : function (strFmRepoId, strNewFilename, strOldFilename, strFolder, strSourceModule, strSourceModuleAction) {
        kajonaAdminAjax.genericAjaxCall("filemanager", "renameFile", strFmRepoId+"&folder="+strFolder+"&oldFilename="+strOldFilename+"&newFilename="+strNewFilename  , {
                success : function(o) {
                    //check if answer contains an error
                    if(o.responseText.indexOf("<error>") != -1) {
                        kajonaStatusDisplay.displayXMLMessage(o.responseText);
                    }
                    else {
                        if(strSourceModule != "" && strSourceModuleAction != "") {
                            kajonaAdminAjax.genericAjaxCall(strSourceModule, strSourceModuleAction, '', {
                                    success : function(o) {
                                        location.reload();
                                    },
                                    failure : function(o) {
                                        kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
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
                    kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
                }
            });
    },

    loadPagesTreeViewNodes : function (node, fnLoadComplete)  {
        var nodeSystemid = node.systemid;
        kajonaAdminAjax.genericAjaxCall("pages", "getChildnodes", nodeSystemid  , {
            success : function(o) {
                //check if answer contains an error
                if(o.responseText.indexOf("<error>") != -1) {
                    kajonaStatusDisplay.displayXMLMessage(o.responseText);
                    o.argument.fnLoadComplete();
                }
                else {
                    //success, start transforming the childs to tree-view nodes
                    //TODO: use xml parser instead of string-parsing
                    //process nodes
                    var intStart = o.responseText.indexOf("<folders>")+9;
                    var strFolders = o.responseText.substr(intStart, o.responseText.indexOf("</folders>")-intStart);

                    while(strFolders.indexOf("<folder>") != -1 ) {
                        var intFolderStart = strFolders.indexOf("<folder>")+8;
                        var intFolderEnd = strFolders.indexOf("</folder>")-intFolderStart;
                        var strSingleFolder = strFolders.substr(intFolderStart, intFolderEnd);

                        var intTemp = strSingleFolder.indexOf("<name>")+6;
                        var strName = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</name>")-intTemp);

                        intTemp = strSingleFolder.indexOf("<systemid>")+10;
                        var strSystemid = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</systemid>")-intTemp);

                        intTemp = strSingleFolder.indexOf("<link>")+6;
                        var strLink = strSingleFolder.substr(intTemp, strSingleFolder.indexOf("</link>")-intTemp);

                        var tempNode = new YAHOO.widget.TextNode( { label:strName, href:strLink }, node);
                        tempNode.systemid = strSystemid;
                        tempNode.labelStyle = "treeView-foldernode";

                        strFolders = strFolders.substr(strFolders.indexOf("</folder>")+9);
                    }

                    intStart = o.responseText.indexOf("<pages>")+7;
                    var strPages = o.responseText.substr(intStart, o.responseText.indexOf("</pages>")-intStart);

                    while(strPages.indexOf("<page>") != -1 ) {
                        var intPageStart = strPages.indexOf("<page>")+6;
                        var intPageEnd = strPages.indexOf("</page>")-intPageStart;
                        var strSinglePage = strPages.substr(intPageStart, intPageEnd);

                        intTemp = strSinglePage.indexOf("<name>")+6;
                        strName = strSinglePage.substr(intTemp, strSinglePage.indexOf("</name>")-intTemp);

                        intTemp = strSinglePage.indexOf("<systemid>")+10;
                        strSystemid = strSinglePage.substr(intTemp, strSinglePage.indexOf("</systemid>")-intTemp);

                        intTemp = strSinglePage.indexOf("<link>")+6;
                        strLink = strSinglePage.substr(intTemp, strSinglePage.indexOf("</link>")-intTemp);

                        tempNode = new YAHOO.widget.TextNode({ label:strName, href:strLink}, node);
                        tempNode.systemid = strSystemid;
                        tempNode.isLeaf = true;
                        tempNode.labelStyle = "treeView-pagenode";

                        strPages = strPages.substr(strPages.indexOf("</page>")+7);
                    }

                    o.argument.fnLoadComplete();
                    kajonaUtils.checkInitialTreeViewToggling();
                }
            },
            failure : function(o) {
                kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
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
        kajonaAdminAjax.genericAjaxCall("navigation", "getChildnodes", nodeSystemid  , {
            success : function(o) {
                //check if answer contains an error
                if(o.responseText.indexOf("<error>") != -1) {
                    kajonaStatusDisplay.displayXMLMessage(o.responseText);
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

                        var tempNode = new YAHOO.widget.TextNode( { label:strName, href:strLink }, node);
                        tempNode.systemid = strSystemid;
                        tempNode.labelStyle = "treeView-navigationnode";

                        strPoints = strPoints.substr(strPoints.indexOf("</point>")+8);
                    }

                    o.argument.fnLoadComplete();
                    kajonaUtils.checkInitialTreeViewToggling();
                }
            },
            failure : function(o) {
                kajonaStatusDisplay.messageError("<b>Request failed!</b><br />" + o.responseText);
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

function filemanagerCreateFolder(strInputId, strRepoId, strRepoFolder, strSourceModule, strSourceAction) {
    //add typed folder
    var strNewFoldername = document.getElementById(strInputId).value;
    if(strNewFoldername != "") {
        kajonaAdminAjax.createFolder(strRepoId, strRepoFolder+"/"+strNewFoldername, strSourceModule, strSourceAction);
    }
}

function filemanagerRenameFile(strInputId, strRepoId, strRepoFolder, strOldName, strSourceModule, strSourceAction) {
    //add typed folder
    var strNewFilename = document.getElementById(strInputId).value;
    if(strNewFilename != "") {
        kajonaAdminAjax.renameFile(strRepoId, strNewFilename, strOldName, strRepoFolder, strSourceModule, strSourceAction);
    }
}

// Uploader
function KajonaUploader(config) {
	var self = this;

	this.config = config;
	this.uploader;
	this.fileList;
	this.fileCount = 0;
	this.fileCountUploaded = 0;
	this.fileTotalSize = 0;
	this.listElementSample;
	
	this.init = function() {
		//try to load the uploader, show fallback content in case of errors if available
		try {
			this.uploader = new YAHOO.widget.Uploader(self.config['overlayContainerId']);
		} catch (e) {
			try {
				document.getElementById('kajonaUploadFallbackContainer').style.display = 'block';
			} catch (e) {}
			
			document.getElementById('kajonaUploadButtonsContainer').style.display = 'none';
			return;
		}

		this.uploader.addListener('contentReady', self.handleContentReady);
		this.uploader.addListener('fileSelect', self.onFileSelect)
		this.uploader.addListener('uploadStart', self.onUploadStart);
		this.uploader.addListener('uploadProgress', self.onUploadProgress);
		this.uploader.addListener('uploadComplete', self.onUploadComplete);
		this.uploader.addListener('uploadCompleteData', self.onUploadResponse);
		this.uploader.addListener('uploadError', self.onUploadError);

		YAHOO.util.Event
				.onDOMReady( function() {
					kajonaAdminTooltip.hide();
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
var kajonaImageEditor = {

    cropArea : null,
    fm_cropObj : null,
    fm_image_isScaled : true,

    filemanagerShowRealsize : function () {
        document.getElementById('fm_filemanagerPic').src = fm_image_rawurl + "&x="
            + (new Date()).getMilliseconds();
        
        kajonaImageEditor.fm_image_isScaled = false;

        kajonaImageEditor.filemanagerHideCropping();
    },

    filemanagerShowPreview : function () {
        document.getElementById('fm_filemanagerPic').src = fm_image_scaledurl.replace("__width__", fm_image_scaledMaxWidth).replace("__height__", fm_image_scaledMaxHeight)
            + "&x=" + (new Date()).getMilliseconds();

        kajonaImageEditor.fm_image_isScaled = true;

        kajonaImageEditor.filemanagerHideCropping();
    },


    filemanagerShowCropping : function () {
        // init the cropping
        if (kajonaImageEditor.fm_cropObj == null) {
            kajonaImageEditor.fm_cropObj = new YAHOO.widget.ImageCropper('fm_filemanagerPic', {
                status :true
            });
            document.getElementById("accept_icon").src = document
                    .getElementById("accept_icon").src.replace(
                    "icon_crop_acceptDisabled.gif", "icon_crop_accept.gif");

            document.getElementById("fm_filemanagerPic_wrap").ondblclick = kajonaImageEditor.filemanagerSaveCropping;
            
            //show confirm box when existing the page without saving the cropping
            YAHOO.util.Event.addListener(window, 'beforeunload', kajonaImageEditor.filemanagerShowWarningUnsaved);
        } else {
        	kajonaImageEditor.filemanagerHideCropping();
        }
    },
    
    filemanagerHideCropping : function () {
        if (kajonaImageEditor.fm_cropObj != null) {
        	YAHOO.util.Event.removeListener(window, 'beforeunload');
        	
            kajonaImageEditor.fm_cropObj.destroy();
            kajonaImageEditor.fm_cropObj = null;
            document.getElementById("accept_icon").src = document
                    .getElementById("accept_icon").src.replace(
                    "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
        }
    },

    filemanagerShowWarningUnsaved : function (event) {
    	event.returnValue = fm_warning_unsavedHint;
    },
    
    filemanagerSaveCropping : function () {   	
        if (kajonaImageEditor.fm_cropObj != null) {
        	YAHOO.util.Event.removeListener(window, 'beforeunload');
        	
            init_fm_crop_save_warning_dialog();
        }
    },
    
    filemanagerSaveCroppingToBackend : function () {  	
        jsDialog_1.hide();
        init_fm_screenlock_dialog();
        kajonaImageEditor.cropArea = kajonaImageEditor.fm_cropObj.getCropCoords();
        if (fm_image_isScaled) {
            // recalculate the "real" crop-coordinates
            var intScaledWidth = document.getElementById('fm_filemanagerPic').width;
            var intScaledHeight = document.getElementById('fm_filemanagerPic').height;
            var intOriginalWidth = document.getElementById('fm_int_realwidth').value;
            var intOriginalHeigth = document.getElementById('fm_int_realheight').value;

            kajonaImageEditor.cropArea.left = Math.floor(kajonaImageEditor.cropArea.left * (intOriginalWidth / intScaledWidth));
            kajonaImageEditor.cropArea.top = Math.floor(kajonaImageEditor.cropArea.top * (intOriginalHeigth / intScaledHeight));
            kajonaImageEditor.cropArea.width = Math.floor(kajonaImageEditor.cropArea.width * (intOriginalWidth / intScaledWidth));
            kajonaImageEditor.cropArea.height = Math.floor(kajonaImageEditor.cropArea.height * (intOriginalHeigth / intScaledHeight));

        }
        kajonaAdminAjax.saveImageCropping(kajonaImageEditor.cropArea.left, kajonaImageEditor.cropArea.top,
                kajonaImageEditor.cropArea.width, kajonaImageEditor.cropArea.height, fm_repo_id, fm_folder, fm_file,
                kajonaImageEditor.fm_cropping_callback);
    },

    fm_cropping_callback : {
        success : function(o) {
            kajonaStatusDisplay.displayXMLMessage(o.responseText);
            kajonaImageEditor.fm_cropObj.destroy();
            kajonaImageEditor.fm_cropObj = null;
            document.getElementById("accept_icon").src = document
                    .getElementById("accept_icon").src.replace(
                    "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
            document.getElementById('fm_image_dimensions').innerHTML = kajonaImageEditor.cropArea.width
                    + ' x ' + kajonaImageEditor.cropArea.height;
            document.getElementById('fm_image_size').innerHTML = 'n.a.';
            document.getElementById('fm_int_realwidth').value = kajonaImageEditor.cropArea.width;
            document.getElementById('fm_int_realheight').value = kajonaImageEditor.cropArea.height;

            if (kajonaImageEditor.fm_image_isScaled) {
                kajonaImageEditor.filemanagerShowPreview();
            } else {
                kajonaImageEditor.filemanagerShowRealsize();
            }

            kajonaImageEditor.cropArea = null;

            hide_fm_screenlock_dialog();
        },
        failure : function(o) {
            kajonaStatusDisplay.messageError("<b>Request failed!</b>"
                    + o.responseText);
            hide_fm_screenlock_dialog();
        }
    },

    filemanagerRotate : function (intAngle) {   	
        init_fm_screenlock_dialog();
        kajonaAdminAjax.saveImageRotating(intAngle, fm_repo_id, fm_folder, fm_file,
                kajonaImageEditor.fm_rotate_callback);
    },

    fm_rotate_callback : {
        success : function(o) {
            kajonaStatusDisplay.displayXMLMessage(o.responseText);

            if (kajonaImageEditor.fm_cropObj != null) {
                kajonaImageEditor.fm_cropObj.destroy();
                kajonaImageEditor.fm_cropObj = null;
                document.getElementById("accept_icon").src = document
                        .getElementById("accept_icon").src.replace(
                        "icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
            }

            //switch width and height
            var intScaledMaxWidthOld = fm_image_scaledMaxWidth;
            fm_image_scaledMaxWidth = fm_image_scaledMaxHeight;
            fm_image_scaledMaxHeight = intScaledMaxWidthOld;

            if (kajonaImageEditor.fm_image_isScaled) {
                kajonaImageEditor.filemanagerShowPreview();
            } else {
                kajonaImageEditor.filemanagerShowRealsize();
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
            kajonaStatusDisplay.messageError("<b>Request failed!</b>"
                    + o.responseText);
            hide_fm_screenlock_dialog();
        }
    }

};

var kajonaSystemtaskHelper =  {

    executeTask : function(strTaskname, strAdditionalParam, bitNoContentReset) {
        if(bitNoContentReset == null || bitNoContentReset == undefined) {

            if(document.getElementById('taskParamForm') != null) {
                document.getElementById('taskParamForm').style.display = "none";
            }

            jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE);
            jsDialog_0.setContentRaw(kajonaSystemtaskDialogContent);
            document.getElementById(jsDialog_0.containerId).style.width = "550px";
            document.getElementById('systemtaskCancelButton').onclick = kajonaSystemtaskHelper.cancelExecution;
            jsDialog_0.init();
        }
        
        kajonaAdminAjax.executeSystemtask(strTaskname, strAdditionalParam, {
            success : function(o) {
                var strResponseText = o.responseText;
                
                //parse the response and check if it's valid
                if(strResponseText.indexOf("<error>") != -1) {
                    kajonaStatusDisplay.displayXMLMessage(strResponseText);
                }
                else if(strResponseText.indexOf("<statusinfo>") == -1) {
                	kajonaStatusDisplay.messageError("<b>Request failed!</b><br />"+strResponseText);
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

                    if(strReload == "") {
                    	jsDialog_0.setTitle(KAJONA_SYSTEMTASK_TITLE_DONE);
                    	document.getElementById('systemtaskLoadingDiv').style.display = "none";
                    	document.getElementById('systemtaskCancelButton').value = KAJONA_SYSTEMTASK_CLOSE;
                    }
                    else {
                        kajonaSystemtaskHelper.executeTask(strTaskname, strReload, true);
                    }
                }
            },
            
            failure : function(o) {
                jsDialog_0.hide();
                kajonaStatusDisplay.messageError("<b>Request failed!</b><br />"+o.responseText);
            }
        });
    },

    cancelExecution : function() {
        if(YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.systemTaskCall)) {
           YAHOO.util.Connect.abort(kajonaAdminAjax.systemTaskCall, null, false);
        }
        jsDialog_0.hide();
    },

    setName : function(strName) {
    	document.getElementById('systemtaskNameDiv').innerHTML = strName;
    }
};


var kajonaUtils = {
    focusHelper : {
		setBrowserFocus : function(strElementId) {
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
		}
	},

    checkInitialTreeViewToggling : function() {
        if(arrTreeViewExpanders.length > 0) {
            var strValue = arrTreeViewExpanders.shift();
            var objNode = tree.getNodeByProperty("systemid", strValue);
            if(objNode != null) {
                objNode.expand();
            }
        }
    },
	
    /*
     * called when the user selects an page/folder/file out of a folderview popup
     */
    folderviewSelectCallback : function(arrTargetsValues) {
    	for (var i in arrTargetsValues) {
	    	if (arrTargetsValues[i][0] == "ckeditor") {
	    		CKEDITOR.tools.callFunction(2, arrTargetsValues[i][1]);
	    	} else {
	    		YAHOO.util.Dom.get(arrTargetsValues[i][0]).value = arrTargetsValues[i][1];
	    	}
    	}
	}

};