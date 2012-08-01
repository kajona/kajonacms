//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2012 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA == "undefined") {
	alert('load kajona.js before!');
}



KAJONA.admin.loader.loadUploaderBase = function(objCallback, arrAdditionalFiles) {
	this.load([ "uploader", "swf" ], this.convertAdditionalFiles(arrAdditionalFiles), objCallback);
};


KAJONA.admin.ajax.saveImageCropping = function(intX, intY, intWidth, intHeight, strFile, objCallback) {
    var postBody = 'file=' + strFile + '&intX=' + intX + '&intY=' + intY
        + '&intWidth=' + intWidth + '&intHeight=' + intHeight + '';
    KAJONA.admin.ajax.genericAjaxCall("mediamanager", "saveCropping", "&"+postBody , objCallback);
};


KAJONA.admin.ajax.saveImageRotating = function(intAngle, strFile, objCallback) {
    var postBody = 'file=' + strFile + '&angle=' + intAngle + '';
    KAJONA.admin.ajax.genericAjaxCall("mediamanager", "rotate", "&"+postBody , objCallback);
};


KAJONA.admin.ajax.createFolder = function (strFmRepoId, strFolder) {
    KAJONA.admin.ajax.genericAjaxCall("mediamanager", "createFolder", strFmRepoId+"&folder="+strFolder, function(data, status, jqXHR) {
        if(status == 'success') {
            //check if answer contains an error
            if(data.indexOf("<error>") != -1) {
                KAJONA.admin.statusDisplay.displayXMLMessage(data);
            }
            else {
                KAJONA.admin.ajax.genericAjaxCall("mediamanager", "partialSyncRepo", strFmRepoId, function(data, status, jqXHR) {
                    if(status == 'success')
                        location.reload();
                    else
                        KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                });
            }
        }
        else  {
            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b><br />" + data);
        }
    });
};



// --- mediamanager ----------------------------------------------------------------------
KAJONA.admin.mediamanager = {
	createFolder : function(strInputId, strRepoId) {
	    var strNewFoldername = document.getElementById(strInputId).value;
	    if(strNewFoldername != "") {
	        KAJONA.admin.ajax.createFolder(strRepoId, strNewFoldername);
	    }
	}
};

KAJONA.admin.mediamanager.Uploader = function(config) {
	var self = this;

	this.config = config;
	this.uploader;
	this.fileList;
	this.fileCount = 0;
	this.fileCountUploaded = 0;
	this.fileTotalSize = 0;
	this.listElementSample;

	this.init = function() {
		//check if Flash Player is available in needed version, otherwise abort and show fallback upload
		if (!YAHOO.util.SWFDetect.isFlashVersionAtLeast(9.045)) {
			try {
				document.getElementById('kajonaUploadFallbackContainer').style.display = 'block';
			} catch (e) {}

			document.getElementById('kajonaUploadButtonsContainer').style.display = 'none';
			return;
		}

		this.uploader = new YAHOO.widget.Uploader(self.config['overlayContainerId']);
		this.uploader.addListener('contentReady', self.handleContentReady);
		this.uploader.addListener('fileSelect', self.onFileSelect)
		this.uploader.addListener('uploadStart', self.onUploadStart);
		this.uploader.addListener('uploadProgress', self.onUploadProgress);
		this.uploader.addListener('uploadComplete', self.onUploadComplete);
		this.uploader.addListener('uploadCompleteData', self.onUploadResponse);
		this.uploader.addListener('uploadError', self.onUploadError);

		YAHOO.util.Event
				.onDOMReady( function() {
					KAJONA.admin.tooltip.hide();
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
		listElementSample = document.getElementById('kajonaUploadFileSample').cloneNode(true);
	}

	this.onFileSelect = function(event) {
		self.fileList = event.fileList;

		jsDialog_0.setContentRaw(document.getElementById('kajonaUploadDialog').innerHTML);
		document.getElementById('kajonaUploadDialog').innerHTML = '';

		self.createFileList();

		jsDialog_0.init();
		YAHOO.util.Dom.setStyle(YAHOO.util.Dom.get('kajonaUploadDialog'), 'display', "block");
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
KAJONA.admin.mediamanager.imageEditor = {
    cropArea : null,
    fm_cropObj : null,
    fm_image_isScaled : true,

    showRealSize : function () {
        $('#fm_mediamanagerPic').attr('src', fm_image_rawurl + "&x="+ (new Date()).getMilliseconds());
        this.fm_image_isScaled = false;
        this.hideCropping();
    },

    showPreview : function () {
        $('#fm_mediamanagerPic').attr('src', fm_image_scaledurl.replace("__width__", fm_image_scaledMaxWidth).replace("__height__", fm_image_scaledMaxHeight)+ "&x=" + (new Date()).getMilliseconds());
        this.fm_image_isScaled = true;
        this.hideCropping();
    },

    showCropping : function () {
        // init the cropping
        if (this.fm_cropObj == null) {
            $('#fm_mediamanagerPic').Jcrop({}, function() {
                KAJONA.admin.mediamanager.imageEditor.fm_cropObj = this;
            });

            this.fm_cropObj.animateTo([ 120,120,80,80 ]);

            $("#accept_icon").attr('src', $("#accept_icon").attr('src').replace("icon_crop_acceptDisabled.gif", "icon_crop_accept.gif"));
            $("#fm_mediamanagerPic_wrap").bind('dblclick', function (event) {
            	KAJONA.admin.mediamanager.imageEditor.saveCropping();
            });
        } else {
            this.hideCropping();
        }
    },

    hideCropping : function () {
        if (this.fm_cropObj != null) {
        	this.fm_cropObj.destroy();
        	this.fm_cropObj = null;
            $('#fm_mediamanagerPic').css("visibility", "visible");
            $("#accept_icon").attr('src', $("#accept_icon").attr('src').replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif"));
        }
    },

    saveCropping : function () {
        if (this.fm_cropObj != null) {
            init_fm_crop_save_warning_dialog();
        }
    },

    saveCroppingToBackend : function () {
        jsDialog_1.hide();
        init_fm_screenlock_dialog();
        this.cropArea = this.fm_cropObj.tellSelect();

        if (fm_image_isScaled) {
            // recalculate the "real" crop-coordinates
            var intScaledWidth = document.getElementById('fm_mediamanagerPic').width;
            var intScaledHeight = document.getElementById('fm_mediamanagerPic').height;
            var intOriginalWidth = document.getElementById('fm_int_realwidth').value;
            var intOriginalHeigth = document.getElementById('fm_int_realheight').value;

            this.cropArea.x = Math.floor(this.cropArea.x * (intOriginalWidth / intScaledWidth));
            this.cropArea.y = Math.floor(this.cropArea.y * (intOriginalHeigth / intScaledHeight));
            this.cropArea.w = Math.floor(this.cropArea.w * (intOriginalWidth / intScaledWidth));
            this.cropArea.h = Math.floor(this.cropArea.h * (intOriginalHeigth / intScaledHeight));
        }

        var callback = function(data, status, jqXHR) {
            if(status == 'success') {
        		var iE = KAJONA.admin.mediamanager.imageEditor;
                KAJONA.admin.statusDisplay.displayXMLMessage(data);
                iE.fm_cropObj.destroy();
                iE.fm_cropObj = null;
                $("#accept_icon").attr('src', $("#accept_icon").attr('src').replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif"));
                $('#fm_image_dimensions').html(iE.cropArea.w+' x '+iE.cropArea.h);
                $('#fm_image_size').html('n.a.');
                $('#fm_int_realwidth').val(iE.cropArea.w);
                $('#fm_int_realheight').val(iE.cropArea.h);

                $('#fm_mediamanagerPic').css("visibility", "visible");
                if (this.fm_image_isScaled) {
                	iE.showPreview();
                } else {
                	iE.showRealSize();
                }

                iE.cropArea = null;
                hide_fm_screenlock_dialog();
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>"+data);
                hide_fm_screenlock_dialog();
            }
        };

        KAJONA.admin.ajax.saveImageCropping(this.cropArea.x, this.cropArea.y,
        		this.cropArea.w, this.cropArea.h, fm_file, callback);
    },

    rotate : function (intAngle) {
        init_fm_screenlock_dialog();

        var callback = function(data, status, jqXHR) {
            if(status == 'success') {
        		var iE = KAJONA.admin.mediamanager.imageEditor;
                KAJONA.admin.statusDisplay.displayXMLMessage(data);

                if (iE.fm_cropObj != null) {
                	iE.fm_cropObj.destroy();
                	iE.fm_cropObj = null;
                    $("#accept_icon").attr('src', $("#accept_icon").attr('src').replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif"));
                }

                //switch width and height
                var intScaledMaxWidthOld = fm_image_scaledMaxWidth;
                fm_image_scaledMaxWidth = fm_image_scaledMaxHeight;
                fm_image_scaledMaxHeight = intScaledMaxWidthOld;

                if (iE.fm_image_isScaled) {
                	iE.showPreview();
                } else {
                	iE.showRealSize();
                }

                // update size-info & hidden elements
                var intWidthOld = $('#fm_int_realwidth').val();
                var intHeightOld = $('#fm_int_realheight').val();
                $('#fm_int_realwidth').val(intHeightOld);
                $('#fm_int_realheight').val(intWidthOld);
                $('#fm_image_dimensions').html(intHeightOld+ ' x ' + intWidthOld);

                hide_fm_screenlock_dialog();
            }
            else {
                KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>"+ data);
                hide_fm_screenlock_dialog();
            }
        };

        KAJONA.admin.ajax.saveImageRotating(intAngle, fm_file, callback);
    }
};




