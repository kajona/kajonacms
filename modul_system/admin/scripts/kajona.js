//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
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

// deprecated, use kajonaAjaxHelper.Loader object instead
function addCss(file) {
	var l = new kajonaAjaxHelper.Loader();
	l.addCssFile(file);
	l.load();
}


function inArray(needle, haystack) {
	for ( var i = 0; i < haystack.length; i++) {
		if (haystack[i] == needle) {
			return true;
		}
	}
	return false;
}

// deprecated, use YAHOO.util.Event.onDOMReady instead, if YUI loaded
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		};
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
		objElement.onmouseover();
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

	// Loader object for dynamically loading additional js and css files
	Loader : function() {
		var additionalFileCounter = 0;
		this.jsBase = KAJONA_WEBPATH + "/admin/scripts/";

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

		this.addJavascriptFile = function(fileWithPath) {
			this.addFile(fileWithPath, "js");
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

	loadAjaxBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "connection" ]);
		l.addJavascriptFile(l.jsBase + "messagebox.js");
		l.load(callback);
	},

	loadDragNDropBase : function(callback, additionalJsFile) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "connection", "animation", "dragdrop" ]);

		if (additionalJsFile != null) {
			l.addJavascriptFile(additionalJsFile);
		}

		l.load(callback);
	},

	loadAnimationBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "animation" ]);
		l.load(callback);
	},

	loadAutocompleteBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "connection", "datasource", "autocomplete" ]);
		l.load(callback);
	},

	loadCalendarBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addJavascriptFile(l.yuiBase + "calendar/calendar-min.js");
		l.addCssFile(l.yuiBase+"calendar/assets/calendar.css");
		l.load(callback);
	},

	loadUploaderBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "uploader" ]);
		l.load(callback);
	},

	loadImagecropperBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addYUIComponents( [ "imagecropper" ]);
		l.load(callback);
	},

	loadDialogBase : function(callback) {
		var l = new kajonaAjaxHelper.Loader();
		l.addJavascriptFile(l.yuiBase + "container/container-min.js");
		l.load(callback);
	}
};

var regularCallback = {
	success : function(o) {
		kajonaStatusDisplay.displayXMLMessage(o.responseText)
	},
	failure : function(o) {
		kajonaStatusDisplay.messageError("<b>request failed!!!</b>")
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
			kajonaAdminTooltip.hide();
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
								kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
							}
						}
						);
                },
                failure : function(o) {
                    kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
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
                                    kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
                                }
                            }
                        );
                    }
                },
                failure : function(o) {
                    kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
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
                                        kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
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
                    kajonaStatusDisplay.messageError("<b>request failed!!!</b>" + o.responseText);
                }
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
            kajonaStatusDisplay.messageError("<b>request failed!!!</b>"
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
            kajonaStatusDisplay.messageError("<b>request failed!!!</b>"
                    + o.responseText);
            hide_fm_screenlock_dialog();
        }
    }

};