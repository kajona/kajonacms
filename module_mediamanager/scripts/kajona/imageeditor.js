//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt


define(["jquery", "ajax", "statusDisplay", "jcrop"], function($, ajax, statusDisplay, jcrop) {


    var imageEditor = {
        cropArea: null,
        fm_cropObj: null,
        fm_image_isScaled: true,

        strCropEnabled : '',
        strCropDisabled : '',


        fm_image_rawurl : '',
        fm_image_scaledurl : '',
        fm_image_scaledMaxWidth : '',
        fm_image_scaledMaxHeight : '',
        fm_file : '',

        init_fm_crop_save_warning_dialog : null,
        init_fm_screenlock_dialog : null,
        hide_fm_screenlock_dialog : null,

        saveImageCropping : function (intX, intY, intWidth, intHeight, strFile, objCallback) {
            var postBody = 'file=' + strFile + '&intX=' + intX + '&intY=' + intY
                + '&intWidth=' + intWidth + '&intHeight=' + intHeight + '';
            ajax.genericAjaxCall("mediamanager", "saveCropping", "&" + postBody, objCallback);
        },

        saveImageRotating : function (intAngle, strFile, objCallback) {
            var postBody = 'file=' + strFile + '&angle=' + intAngle + '';
            ajax.genericAjaxCall("mediamanager", "rotate", "&" + postBody, objCallback);
        },

        showRealSize: function () {
            $('#fm_mediamanagerPic').attr('src', this.fm_image_rawurl + "&x=" + (new Date()).getMilliseconds());
            this.fm_image_isScaled = false;
            this.hideCropping();
        },

        showPreview: function () {
            $('#fm_mediamanagerPic').attr('src', this.fm_image_scaledurl.replace("__width__", this.fm_image_scaledMaxWidth).replace("__height__", this.fm_image_scaledMaxHeight) + "&x=" + (new Date()).getMilliseconds());
            this.fm_image_isScaled = true;
            this.hideCropping();
        },

        showCropping: function () {
            // init the cropping
            var iE = this;
            if (this.fm_cropObj == null) {
                $('#fm_mediamanagerPic').Jcrop({}, function () {
                    iE.fm_cropObj = this;
                });

                this.fm_cropObj.animateTo([120, 120, 80, 80]);

                $("#accept_icon").html(this.strCropEnabled);
                $("#fm_mediamanagerPic_wrap").bind('dblclick', function (event) {
                    this.saveCropping();
                });
            } else {
                this.hideCropping();
            }
        },

        hideCropping: function () {
            if (this.fm_cropObj != null) {
                this.fm_cropObj.destroy();
                this.fm_cropObj = null;
                $('#fm_mediamanagerPic').css("visibility", "visible");
                $("#accept_icon").html(this.strCropDisabled);
            }
        },

        saveCropping: function () {
            if (this.fm_cropObj != null) {
                this.init_fm_crop_save_warning_dialog();
            }
        },

        saveCroppingToBackend: function () {
            jsDialog_1.hide();
            this.init_fm_screenlock_dialog();
            this.cropArea = this.fm_cropObj.tellSelect();

            if (this.fm_image_isScaled) {
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

            var iE = this;
            var callback = function (data, status, jqXHR) {
                if (status == 'success') {

                    statusDisplay.displayXMLMessage(data);
                    iE.fm_cropObj.destroy();
                    iE.fm_cropObj = null;
                    $("#accept_icon").html(iE.strCropEnabled);
                    $('#fm_image_dimensions').html(iE.cropArea.w + ' x ' + iE.cropArea.h);
                    $('#fm_image_size').html('n.a.');
                    $('#fm_int_realwidth').val(iE.cropArea.w);
                    $('#fm_int_realheight').val(iE.cropArea.h);

                    $('#fm_mediamanagerPic').css("visibility", "visible");
                    if (iE.fm_image_isScaled) {
                        iE.showPreview();
                    } else {
                        iE.showRealSize();
                    }

                    iE.cropArea = null;

                    location.reload();
                    iE.hide_fm_screenlock_dialog();
                }
                else {
                    statusDisplay.messageError("<b>Request failed!</b>" + data);
                    iE.hide_fm_screenlock_dialog();
                }
            };

            this.saveImageCropping(this.cropArea.x, this.cropArea.y,
                this.cropArea.w, this.cropArea.h, this.fm_file, callback);
        },

        rotate: function (intAngle) {
            this.init_fm_screenlock_dialog();

            var iE = this;
            var callback = function (data, status, jqXHR) {
                if (status == 'success') {
                    statusDisplay.displayXMLMessage(data);

                    if (iE.fm_cropObj != null) {
                        iE.fm_cropObj.destroy();
                        iE.fm_cropObj = null;
                        $("#accept_icon").html(iE.strCropDisabled);
                    }

                    //switch width and height
                    var intScaledMaxWidthOld = iE.fm_image_scaledMaxWidth;
                    iE.fm_image_scaledMaxWidth = iE.fm_image_scaledMaxHeight;
                    iE.fm_image_scaledMaxHeight = intScaledMaxWidthOld;

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
                    $('#fm_image_dimensions').html(intHeightOld + ' x ' + intWidthOld);

                    iE.hide_fm_screenlock_dialog();
                }
                else {
                    statusDisplay.messageError("<b>Request failed!</b>" + data);
                    iE.hide_fm_screenlock_dialog();
                }
            };

            this.saveImageRotating(intAngle, this.fm_file, callback);
        }
    };


    return imageEditor;

});

