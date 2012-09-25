//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2012 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 4895 2012-08-22 07:32:39Z sidler $

if (typeof KAJONA == "undefined") {
    alert('load kajona.js before!');
}


/**
 * Object to show a modal dialog
 */
KAJONA.admin.ModalDialog = function(strDialogId, intDialogType, bitDragging, bitResizing) {
    this.dialog;
    this.containerId = strDialogId;
    this.iframeId;
    this.iframeURL;

    this.setTitle = function (strTitle) {
        if(strTitle == "")
            strTitle = "&nbsp;";
        $('#' + this.containerId + '_title').html(strTitle);
    };

    this.setContent = function (strContent, strConfirmButton, strLinkHref) {
        if (intDialogType == 1) {
            $('#' + this.containerId + '_content').html(strContent);

            var $confirmButton = $('#' + this.containerId + '_confirmButton');
            $confirmButton.html(strConfirmButton);
            $confirmButton.click(function() {
                window.location = strLinkHref;
                return false;
            });
        }
    };

    this.setContentRaw = function(strContent) {
        $('#' + this.containerId + '_content').html(strContent);
    };

    this.setContentIFrame = function(strUrl) {
        this.iframeId = this.containerId + '_iframe';
        this.iframeURL = strUrl;
    };

    this.init = function(intWidth, intHeight) {


        if(!intWidth) {
            if($('#' + this.containerId).hasClass("fullsize")) {
                intWidth = $(window).width() * 0.6;
            }
            else
                intWidth = 400;
        }

        if(!intHeight) {
            if($('#' + this.containerId).hasClass("fullsize")) {
                intHeight = $(window).height() * 0.6;
            }
            else
                intHeight = '';
        }

        $('#' + this.containerId).modal({
            backdrop: true,
            keyboard: false,
            show: true
        }).css({
            width: intWidth,
            'margin-left': function () {
                return -($(this).width() / 2);
            }
        });


        if(this.iframeURL != null) {
            $('#' + this.containerId + '_content').html('<iframe src="' + this.iframeURL + '" width="100%" height="'+(intHeight)+'" name="' + this.iframeId + '" id="' + this.iframeId + '" class="seamless" seamless></iframe>');
            this.iframeURL = null;
        }

        if (bitDragging) {
            this.enableDragging();
        }
        if (bitResizing) {
            this.enableResizing();
        }
    };

    this.hide = function() {
        $('#' + this.containerId).modal('hide');
    };

    this.enableDragging = function() {};

    this.enableResizing = function() {};
};


