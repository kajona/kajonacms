//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2015 by Kajona, www.kajona.de
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



/*
 * -------------------------------------------------------------------------
 * Portaleditor-specific functions
 * -------------------------------------------------------------------------
 */


KAJONA.admin.portaleditor = {
	objPlaceholderWithElements: {},

    initPortaleditor : function() {
        CKEDITOR_BASEPATH = KAJONA_WEBPATH+"/core/module_system/admin/scripts/ckeditor/";

        KAJONA.admin.loader.loadFile([
            "/core/module_system/admin/scripts/ckeditor/ckeditor.js"
        ], function() {
            //span and other tags are officially not support, nevertheless working...
            CKEDITOR.dtd.$editable.span = 1;
            CKEDITOR.dtd.$editable.a = 1;
            CKEDITOR.dtd.$editable.label = 1;
            CKEDITOR.dtd.$editable.td = 1;
            CKEDITOR.disableAutoInline = true;
            KAJONA.admin.portaleditor.RTE.init();
        });

        // init drag&drop of page elements
        KAJONA.admin.portaleditor.dragndrop.init();
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

	closeDialog: function (bitSkipConfirmation) {
        if(!bitSkipConfirmation)
	        var bitClose = confirm(KAJONA.admin.lang["pe_dialog_close_warning"]);
	    if(bitClose || bitSkipConfirmation) {
	    	peDialog.hide();
	    	//reset iframe
	    	peDialog.setContentRaw("");
	    }
	},

	addNewElements: function (strPlaceholder, strPlaceholderName, arrElements) {
		this.objPlaceholderWithElements[strPlaceholder] = {
			placeholderName: strPlaceholderName,
			elements: arrElements
		};
	},

    changeElementData : function(strDataPlaceholder, strDataSystemid, objElementData) {

        var $objContent = jQuery.parseHTML(objElementData);

        //see if the element is already present, then flip the contents
        if($("div.peElementWrapper[data-systemid='"+strDataSystemid+"']").length) {
            $("div.peElementWrapper[data-systemid='"+strDataSystemid+"']").html($($objContent).closest("div.peElementWrapper[data-systemid="+strDataSystemid+"]").html());
        }
        else {
            //add it as the last element to the placeholder itself
            strDataPlaceholder = strDataPlaceholder.replace(/\|/g, '\\|');
            $("#menuContainer_"+strDataPlaceholder).before($($objContent).closest("div.peElementWrapper[data-systemid="+strDataSystemid+"]"));
        }

    },

    deleteElementData : function(strSystemid) {
        $("div.peElementWrapper[data-systemid='"+strSystemid+"']").remove();
        //and delete the element on the backend
        var data = {
            systemid: strSystemid
        };
        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=deleteElementFinalXML', data, function () {
        }).fail(function() {
            location.reload();
        });
    }
};

KAJONA.admin.portaleditor.RTE = {};
KAJONA.admin.portaleditor.RTE.config = {};
KAJONA.admin.portaleditor.RTE.modifiedFields = {};

KAJONA.admin.portaleditor.RTE.savePage = function () {

    //console. group('savePage');
    $.each(KAJONA.admin.portaleditor.RTE.modifiedFields, function (key, value) {
        var keySplitted = key.split('#');

        var data = {
            systemid: keySplitted[0],
            property: keySplitted[1],
            value: value
        };

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=updateObjectProperty', data, function () {
            //console. warn('server response');
            //console. log(this.responseText);
        });
    });
    //console. groupEnd('savePage');

    KAJONA.admin.portaleditor.RTE.modifiedFields = {};
};

/**
 * To init the portaleditor, use a syntax like
 *
 * systemid#property#mode
 *
 * whereas systemid is the id of the target record, property the property of the target-object to update.
 * The mode controls the different editor-modes:
 *
 * wysiwyg: full CKEditor
 * plain: limited CKEditor, no linebreaks and only undo/redo buttons
 * textarea: limited CKEditor, linebreaks are allowed, undo/redo buttons
 */
KAJONA.admin.portaleditor.RTE.init = function () {

    var count = 0;

    $('*[data-kajona-editable]').each(function () {

        var editable = $(this);

        if(editable.attr('id') == undefined) {
            editable.attr('id', 'ckeditor-hack-'+count++);
        }

        var keySplitted = editable.attr('data-kajona-editable').split('#');

        var strMode = keySplitted[2] ? keySplitted[2] : 'wysiwyg';

        var ckeditorConfig = KAJONA.admin.portaleditor.RTE.config;
        ckeditorConfig.toolbar = strMode == 'wysiwyg' ? 'pe_full' : 'pe_lite';
        ckeditorConfig.forcePasteAsPlainText = true;
        ckeditorConfig.kajona_strMode = strMode;
        ckeditorConfig.title = false;
        ckeditorConfig.on = {
            blur: function( event ) {
                var data = event.editor.getData();
                var attr = $(event.editor.element).attr('data-kajona-editable');

                //validate, if injected <br />s have to be removed. its then up to the
                //portal element to decide if \n should be nl2br()ed.
                if(event.editor.config.kajona_strMode == 'textarea') {
                    data = data.replace("<br />", "");
                }

                KAJONA.admin.portaleditor.RTE.modifiedFields[attr] = data;


                // save field on blur
                KAJONA.admin.portaleditor.RTE.savePage();
            },
            key: function( event ) {
                //prevent enter in plaintext
                if (event.data.keyCode == 13 && event.editor.config.kajona_strMode == 'plain') {
                    event.cancel();
                }
            }
        };

        //disable drag n drop
        editable.bind('drop drag', function () {
            return false;
        });

        editable.attr("contenteditable", "true");
        CKEDITOR.inline(editable.get(0), ckeditorConfig);
    });

    // warn user if there are unsaved changes when leaving the page
    $(window).on('beforeunload', function () {
        // check if there are unsaved changes
        var unsavedChanges = false;
        $.each(KAJONA.admin.portaleditor.RTE.modifiedFields, function() {
            unsavedChanges = true;
            return false;
        });

        if (unsavedChanges) {
            return KAJONA.admin.lang.pe_rte_unsavedChanges;
        }
    });
};


/**
 * Initialise the drag & drop logic to move page elements
 */
KAJONA.admin.portaleditor.dragndrop = {};
KAJONA.admin.portaleditor.dragndrop.init = function () {

    // checks if the page element is allowed in the given placeholder or not
    var isElementAllowedInPlaceholder = function (ui, $placeholderWrapper) {
        var elementName = ui.item.data('element');
        var placeholder = $placeholderWrapper.data('placeholder');

        //if either the source or target element is from the master-page, only placeholders on the master-page are allowes
        if(placeholder.substring(0, "master".length) == "master" && ui.item.parent('.pePlaceholderWrapper').data('placeholder').substring(0, "master".length) != "master")
            return false;

        var allowedElements = placeholder.split('_')[1].split('|');
        return allowedElements.indexOf(elementName) !== -1;
    };

    // checks if the page element is allowed in the given placeholder or not
    var saveElementPosition = function (systemId, newPos) {
        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setAbsolutePosition', {
            systemid: systemId,
            listPos: newPos + 1
        });
    };

    var oldPos;
    var suspendStop = false;

    $('.pePlaceholderWrapper').sortable({
        items: 'div.peElementWrapper',
        handle: '.moveHandle',
        connectWith: '.pePlaceholderWrapper',
        cursor: 'move',
        forcePlaceholderSize: true,
        placeholder: 'peElementMovePlaceholder',
        start: function(event, ui) {
            oldPos = ui.item.parent().children('div.peElementWrapper').index(ui.item);
        },
        activate: function(event, ui) {
            var $placeholderWrapper = $(this);
            if (isElementAllowedInPlaceholder(ui, $placeholderWrapper)) {
                $placeholderWrapper.addClass('pePlaceholderWrapperDropTarget');
            }
        },
        over: function(event, ui) {
            var $placeholderWrapper = $(this);

            // hide placeholder if element is not allowed
            if (isElementAllowedInPlaceholder(ui, $placeholderWrapper)) {
                $(ui.placeholder).show();
            } else {
                $(ui.placeholder).hide();
            }
        },
        receive: function(event, ui) {
            var $placeholderWrapper = $(this);
            var $oldPlaceholderWrapper = ui.sender;

            if (isElementAllowedInPlaceholder(ui, $placeholderWrapper)) {
                var newPlaceholder = $placeholderWrapper.data('placeholder');
                var systemId = ui.item.data('systemid');
                var newPos = ui.item.parent().children('div.peElementWrapper').index(ui.item);

                suspendStop = true;
                $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=moveElement', {
                    systemid: systemId,
                    placeholder: newPlaceholder
                }, function () {
                    saveElementPosition(systemId, newPos);
                    suspendStop = false;
                });
            } else {
                $oldPlaceholderWrapper.sortable("cancel");
            }
        },
        stop: function(event, ui) {
            if (!suspendStop) { // to prevent double requests
                var newPos = ui.item.parent().children('div.peElementWrapper').index(ui.item);

                if (oldPos !== newPos) {
                    var systemId = ui.item.data('systemid');
                    saveElementPosition(systemId, newPos);
                }
            }

            oldPos = null;
        },
        deactivate: function(event, ui) {
            var $placeholderWrapper = $(this);
            $placeholderWrapper.removeClass('pePlaceholderWrapperDropTarget');
        },
        delay: KAJONA.util.isTouchDevice() ? 500 : 0
    });
};



/**
 * Folderview functions
 */
KAJONA.admin.folderview = {
    /**
     * holds a reference to the ModalDialog
     */
    dialog: undefined,

    /**
     * holds CKEditors CKEditorFuncNum parameter to read it again in KAJONA.admin.folderview.fillFormFields()
     * so we don't have to pass through the param with all requests
     */
    selectCallbackCKEditorFuncNum: 0,

    /**
     * To be called when the user selects an page/folder/file out of a folderview dialog/popup
     * Detects if the folderview is embedded in a dialog or popup to find the right context
     *
     * @param {Array} arrTargetsValues
     * @param {function} objCallback
     */
    selectCallback: function (arrTargetsValues, objCallback) {
        if (window.opener) {
            window.opener.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
        } else if (parent) {
            parent.KAJONA.admin.folderview.fillFormFields(arrTargetsValues);
        }

        if ($.isFunction(objCallback)) {
            objCallback();
        }

        this.close();

    },

    /**
     * fills the form fields with the selected values
     */
    fillFormFields: function (arrTargetsValues) {
        for (var i in arrTargetsValues) {
            if (arrTargetsValues[i][0] == "ckeditor") {
                CKEDITOR.tools.callFunction(this.selectCallbackCKEditorFuncNum, arrTargetsValues[i][1]);
            } else {
                var formField = $("#"+arrTargetsValues[i][0]).get(0);

                if (formField != null) {
                    formField.value = arrTargetsValues[i][1];

                    //fire the onchange event on the form field
                    if (document.createEvent) { //Firefox
                        var evt = document.createEvent("Events");
                        evt.initEvent('change', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                        formField.dispatchEvent(evt);
                    } else if (document.createEventObject) { //IE
                        var evt = document.createEventObject();
                        formField.fireEvent('onchange', evt);
                    }

                }
            }
        }
    },

    /**
     * fills the form fields with the selected values
     */
    close: function () {
        if (window.opener) {
            window.close();
        } else if (parent) {
            var context = parent.KAJONA.admin.folderview;
            context.dialog.hide();
            context.dialog.setContentRaw("");
        }
    }
};



/**
 * Loader for dynamically loading additional js and css files after the onDOMReady event
 *
 */
KAJONA.admin.loader = new KAJONA.util.Loader();


KAJONA.admin.tooltip = {
    initTooltip : function() {
        KAJONA.admin.loader.loadFile(['/core/module_system/admin/scripts/qtip2/jquery.qtip.min.js', '/core/module_system/admin/scripts/qtip2/jquery.qtip.min.css'], function() {

            $('*[rel=tooltip]').qtip({
                position: {
                    viewport: $(window)
                },
                style: {
                    classes: 'qtip-youtube qtip-shadow'
                }
            });
        });
    },

    addTooltip : function(objElement, strText) {
        KAJONA.admin.loader.loadFile(['/core/module_system/admin/scripts/qtip2/jquery.qtip.min.js', '/core/module_system/admin/scripts/qtip2/jquery.qtip.min.css'], function() {

            if(strText) {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-youtube qtip-shadow'
                    },
                    content : {
                        text: strText
                    }
                });
            }
            else {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-youtube qtip-shadow'
                    }
                });
            }
        });
    }

};

KAJONA.admin.tooltip.initTooltip();