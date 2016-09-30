//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA == "undefined") {
	alert('load kajona.js before!');
}

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

            $("#accept_icon").html(KAJONA.admin.strCropEnabled);
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
            $("#accept_icon").html(KAJONA.admin.strCropDisabled);
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
                $("#accept_icon").html(KAJONA.admin.strCropEnabled);
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

                location.reload();
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
                    $("#accept_icon").html(KAJONA.admin.strCropDisabled);
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




