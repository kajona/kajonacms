//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

//please leave this line for remote debuggers such as webstorm / phpstorm
//@ sourceURL=/core/module_pages/admin/scripts/kajona_portaleditor.js

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
            if($('div.pePlaceholderWrapper[data-placeholder='+strDataPlaceholder+']')) {
                $('div.pePlaceholderWrapper[data-placeholder='+strDataPlaceholder+']').append($($objContent));
            }

            else if($("#menuContainer_"+strDataPlaceholder)) {
                $("#menuContainer_"+strDataPlaceholder).before($($objContent).closest("div.peElementWrapper[data-systemid="+strDataSystemid+"]"));
            }
        }

    },

    deleteElement : function(strSystemid) {

        var $objWrapper = $("div.peElementWrapper[data-systemid='"+strSystemid+"']");

        var objStatusIndicator = null;
        if($objWrapper) {
            objStatusIndicator = new KAJONA.admin.portaleditor.RTE.SaveIndicator($objWrapper);
        }

        //and delete the element on the backend
        var data = {
            systemid: strSystemid
        };
        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=delete', data, function () {
        }).always(function () {
                if(objStatusIndicator) {
                    $objWrapper.addClass('peInactiveElement');
                    objStatusIndicator.showProgress();
                }
            })
            .done(function () {
                if(objStatusIndicator) {
                    objStatusIndicator.addClass('peSaved');
                    window.setTimeout(function () {
                        objStatusIndicator.hide();

                        $objWrapper.remove();
                    }, 1000);
                }
                else {
                    location.reload();
                }
            }).fail(function () {
                if(objStatusIndicator) {
                    objStatusIndicator.addClass('peFailed');
                    window.setTimeout(function () {
                        objStatusIndicator.hide();
                    }, 5000);
                }
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

        var $editable = $('[data-kajona-editable="' + key + '"]');
        $editable.addClass('peSaving');

        var objStatusIndicator = new KAJONA.admin.portaleditor.RTE.SaveIndicator($editable);

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=updateObjectProperty', data)
            .always(function () {
                objStatusIndicator.showProgress();
            })
            .done(function () {
                objStatusIndicator.addClass('peSaved');
                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000);
            }).fail(function () {
                $editable.addClass('peFailed');
                objStatusIndicator.addClass('peFailed');

                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000);
        });
    });
    //console. groupEnd('savePage');

    KAJONA.admin.portaleditor.RTE.modifiedFields = {};
};

/**
 * The saveIndicator is used to show a working-indicator associated with a ui element.
 * currently the indicator may represent various states:
 * - showProgress showing the indicator
 * - addClass adding a class, e.g. to indicate a new status
 * - hide destroying the indicator completely
 * @param $objSourceElement
 */
KAJONA.admin.portaleditor.RTE.SaveIndicator = function($objSourceElement) {

    var objDiv = null;
    var objSourceElement = $objSourceElement;

    this.showProgress = function () {

        objDiv = $('<div>').addClass('peProgressIndicator peSaving');//.attr('data-kajona-indicator', indicatorId);
        $('body').append(objDiv);
        objDiv.css('left', objSourceElement.position().left+objSourceElement.width()).css('top', objSourceElement.position().top);
    };

    this.addClass = function(strClass) {

        objDiv.addClass(strClass);
    };

    this.hide = function() {
        objSourceElement.removeClass('peFailed');
        objDiv.remove();
        objDiv = null;
    };
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

        //quit if the init run before
        if(editable.hasClass('cke_editable')) {
            return;
        }

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
 * A helper to trigger the status-actions of a page-element, so setting the element active / inactive
 * @type {{setStatus: KAJONA.admin.portaleditor.status.setStatus}}
 */
KAJONA.admin.portaleditor.status = {
    setStatus : function(strSystemid, intStatus) {

        var $objElement = $('.peElementWrapper[data-systemid="'+strSystemid+'"]');

        var objStatusIndicator = new KAJONA.admin.portaleditor.RTE.SaveIndicator($objElement);


        if(intStatus == 0) {
            $objElement.addClass("peInactiveElement");
        }
        else {
            $objElement.removeClass("peInactiveElement");
        }

        var data = {
            systemid: strSystemid,
            status: intStatus
        };

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setStatus', data)
            .always(function () {
                objStatusIndicator.showProgress();
            })
            .done(function () {
                objStatusIndicator.addClass('peSaved');
                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000);
            }).fail(function () {
            $objElement.addClass('peFailed');
            objStatusIndicator.addClass('peFailed');

            window.setTimeout(function () {
                objStatusIndicator.hide();
            }, 5000);
        });

    }
};

/**
 * Initialise the drag & drop logic to move page elements
 */
KAJONA.admin.portaleditor.dragndrop = {};
KAJONA.admin.portaleditor.dragndrop.init = function () {

    // checks if the page element is allowed in the given placeholder or not
    var isElementAllowedInPlaceholder = function (ui, $placeholderWrapper) {

        //split between regular elements and block elements


        var elementName = ui.item.data('element');
        var placeholder = $placeholderWrapper.data('placeholder');

        if(elementName == "block") {
            if(ui.item.parent(".pePlaceholderWrapper").data('placeholder') == placeholder) {
                return true;
            }
        }
        else {
            //if either the source or target element is from the master-page, only placeholders on the master-page are allowed
            if (placeholder.substring(0, "master".length) != "master" && ui.item.parent('.pePlaceholderWrapper').data('placeholder').substring(0, "master".length) == "master") {
                return false;
            }

            var allowedElements = placeholder.split('_')[1].split('|');
            return allowedElements.indexOf(elementName) !== -1;
        }

        return false;
    };

    // checks if the page element is allowed in the given placeholder or not
    var saveElementPosition = function (systemId, newPos, $objUiElement) {

        var objStatusIndicator = new KAJONA.admin.portaleditor.RTE.SaveIndicator($objUiElement);

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=setAbsolutePosition', {
            systemid: systemId,
            listPos: newPos + 1
        }).always(function () {
                objStatusIndicator.showProgress();
            })
            .done(function () {
                objStatusIndicator.addClass('peSaved');
                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000);
            }).fail(function () {
                $editable.addClass('peFailed');
                objStatusIndicator.addClass('peFailed');

                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000);
            });
    };

    var oldPos;
    var suspendStop = false;

    $('.pePlaceholderWrapper').sortable({
        items: 'div.peElementWrapper:not(.peNoDnd)',
        handle: '.moveHandle',
        connectWith: '.pePlaceholderWrapper',
        cursor: 'move',
        forcePlaceholderSize: true,
        placeholder: 'peElementMovePlaceholder',
        tolerance: 'pointer',

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
                    saveElementPosition(systemId, newPos, ui.item);
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
                    saveElementPosition(systemId, newPos, ui.item);
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


KAJONA.admin.portaleditor.globalToolbar = {
    init: function() {
        var $objBody = $('body');

        var $objContainer = $('<div>').addClass('peGlobalToolbar');

        $objContainer.append($('<div>').addClass('peToolbarHeader').append(
            $('<i>').addClass('fa fa-bars')).on('click', function() {
                var $peGlobalToolbar = $('.peGlobalToolbar');
                if($peGlobalToolbar.hasClass('peGlobalToolbarOpen'))
                    $peGlobalToolbar.removeClass('peGlobalToolbarOpen');
                else
                    $peGlobalToolbar.addClass('peGlobalToolbarOpen');
            })
        );


        //render various page-informations
        var $objInfoContainer = $('<div>').addClass('peGlobalToolbarInfo');
        $.each(KAJONA.admin.pageInfo, function(entryName, objInfo) {
            var $objRowContent = $('<div>').addClass('peGlobalToolbarInfoText').append($('<div>').html(objInfo.label)).append($('<div>').html(objInfo.value));
            var $objRow = $('<div>').addClass('peGlobalToolbarInfoRow').append($('<div>').addClass('peGlobalToolarbarInfoIcon').append($('<i>').addClass('fa '+objInfo.icon))).append($objRowContent);
            $objInfoContainer.append($objRow);
        });
        $objContainer.append($objInfoContainer);



        //render various page-actions
        var $objActionContainer = $('<div>').addClass('peGlobalToolbarInfo');
        $.each(KAJONA.admin.pageActions, function(entryName, objInfo) {
            var $objLink = $('<a>').on('click', objInfo.onclick).append($('<div>').addClass('peGlobalToolarbarInfoIcon').append($('<i>').addClass('fa '+objInfo.icon))).append($('<div>').addClass('peGlobalToolbarInfoText').append(objInfo.label));
            var $objRowContent = $('<div>').addClass('peGlobalToolbarInfoLink').append($objLink);
            var $objRow = $('<div>').addClass('peGlobalToolbarInfoRow peGlobalToolbarActionRow').append($objRowContent);
            $objActionContainer.append($objRow);
        });
        $objContainer.append($objActionContainer);


        //attach a page-jump autocomplete
        var $objJumpContainer = $('<div>').addClass('peGlobalToolbarInfo');
        var $objInput = $('<input>').attr('id', 'peGlobalToolbarPageJump').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: KAJONA_WEBPATH+'/xml.php?admin=1&module=pages&action=getPagesByFilter',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        filter: request.term
                    },
                    success: response
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                log( ui.item ?
                "Selected: " + ui.item.value + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );
            }


        });
        $objInput.data("ui-autocomplete")._renderMenu = function( ul, items ) {
            var that = this;
            $.each( items, function( index, item ) {
                that._renderItemData( ul, item );
            });
            $( ul ).addClass('peGlobalAutocompleteSuggest');
        };
        $objJumpContainer.append($objInput);

        $objContainer.append($objJumpContainer);

        $objBody.append($objContainer);
    }
};


/**
 * Initialise the action toolbar logic
 */
KAJONA.admin.portaleditor.elementActionToolbar = {
    init: function () {
        KAJONA.admin.portaleditor.elementActionToolbar.injectPlaceholderActions(KAJONA.admin.actions);
    },

    injectPlaceholderActions: function (actions) {

        $.each(actions.placeholder, function (placeholderName, actions) {
            KAJONA.admin.portaleditor.elementActionToolbar.injectElementCreateUI($('[data-name="' + placeholderName + '"]'), actions, placeholderName);
        });

        $.each(actions.systemIds, function (systemId, actions) {
            KAJONA.admin.portaleditor.elementActionToolbar.injectElementEditUI($('[data-systemid="' + systemId + '"]'), actions);
        });

        KAJONA.admin.tooltip.initTooltip();
    },

    injectElementEditUI: function ($element, actions) {
        $element.prepend(KAJONA.admin.portaleditor.elementActionToolbar.generateActionList(actions));
    },

    injectElementCreateUI: function ($element, actions, placeholderName) {
        var $addButton = $('<div class="peAddButton"><i class="fa fa-plus-circle"></i></div>');
        var $objMenu = $(KAJONA.admin.portaleditor.elementActionToolbar.generateAddActionList(actions, $objMenu));
        $addButton.append($objMenu);
        $element.after($addButton);
    },


    generateAddActionList: function (actions, $objParent) {

        var $actionList = $('<div>').addClass('peActionToolbarActionContainer');

        actions.forEach(function (action) {
            var actionTitle = KAJONA.admin.lang['pe' + action.type];
            switch (action.type) {
                case 'CREATE':
                    var $actionElement = $('<a>');
                    $actionElement.append($('<i>').addClass('fa fa-plus-circle'));
                    $actionElement.append(' '+action.name);
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.openDialog(action.link);
                    });

                    $actionList.append($actionElement);
                    break;
            }

        });

        return $('<div>').addClass('peActionToolbar').append($('<div>').addClass('peActionToolbarCaretTop')).append($actionList);
    },

    generateActionList: function (actions) {
        var $actionList = $('<div>').addClass('peActionToolbarActionContainer');
        actions.forEach(function (action) {
            var actionTitle = KAJONA.admin.lang['pe' + action.type];
            var $actionElement = $('<a>');
            $actionElement.attr('rel', 'tooltip').attr('title', actionTitle);
            switch (action.type) {
                case 'EDIT':
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.openDialog(action.link);
                    });
                    $actionElement.append($('<i>').addClass('fa fa-pencil'));
                    break;
                case 'DELETE':

                    $actionElement.on('click', function () {
                        delDialog.setTitle(actionTitle);
                        delDialog.setContent(KAJONA.admin.lang.peDELETEWARNING, actionTitle, function() {
                            delDialog.hide();
                            KAJONA.admin.portaleditor.deleteElement(action.systemid);
                            return false;
                        });
                        delDialog.init();
                        return false;
                    });
                    $actionElement.append($('<i>').addClass('fa fa-trash'));
                    break;
                case 'SETACTIVE':
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.status.setStatus(action.systemid, 1);
                        //KAJONA.admin.portaleditor.openDialog(action.link);
                    });
                    $actionElement.append($('<i>').addClass('fa fa-eye'));
                    break;
                case 'SETINACTIVE':
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.status.setStatus(action.systemid, 0);
                        //KAJONA.admin.portaleditor.openDialog(action.link);
                    });
                    $actionElement.append($('<i>').addClass('fa fa-eye-slash'));
                    break;
                case 'CREATE':
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.openDialog(action.link);
                    });
                    $actionElement.append($('<i>').addClass('fa fa-plus-circle'));
                    break;

                case 'COPY':
                    $actionElement.on('click', function () {
                        KAJONA.admin.portaleditor.openDialog(action.link);
                    });
                    $actionElement.append($('<i>').addClass('fa fa-files-o'));
                    break;
                case 'MOVE':
                    $actionElement.addClass('moveHandle').append($('<i>').addClass('fa fa-arrows moveHandle'));
                    break;
                default:
                    return;
            }

            $actionList.append($actionElement);
        });

        //create the wrapper code
        return $('<div>').addClass('peActionToolbar').append($actionList).append($('<div>').addClass('peActionToolbarCaretBottom'));
    },

    show : function(element) {
        var $objEl = $(element);
        if($objEl.children('.peActionToolbar')) {
            $objEl.children('.peActionToolbar')
                .css('top', ($objEl.position().top) - 35)
                .css('left', ($objEl.position().left))
                .addClass('peShown');

            //$objEl.closest(".peShown").css('display', 'none');
            //$objEl.parent().parent().parents(".peElementWrapper").find(".peShown").css('display', 'none');
        }

    },

    hide : function(element) {
        var $objEl = $(element);
        if($objEl.children('.peActionToolbar')) {
            $objEl.children('.peActionToolbar')
                .removeClass('peShown');
        }

    }


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


$(function () {
    KAJONA.admin.portaleditor.elementActionToolbar.init();
    KAJONA.admin.portaleditor.globalToolbar.init();
    KAJONA.admin.tooltip.initTooltip();
});