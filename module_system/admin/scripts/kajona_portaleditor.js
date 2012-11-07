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



/*
 * -------------------------------------------------------------------------
 * Portaleditor-specific functions
 * -------------------------------------------------------------------------
 */


KAJONA.admin.portaleditor = {
	objPlaceholderWithElements: {},

    initPortaleditor : function() {

        KAJONA.admin.loader.loadFile([
            "/core/module_system/admin/scripts/jqueryui/jquery-ui.custom.min.js",
            "/core/module_system/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css",
            "/core/module_system/admin/scripts/jquery/jquery.htmlClean.min.js",
            "/core/module_pages/admin/scripts/rangy/rangy-core.js",
            "/core/module_pages/admin/scripts/halloeditor/hallo-min.js",
            "/core/module_pages/admin/scripts/halloeditor/hallo.css",
            "/core/module_pages/admin/scripts/halloeditor/fontawesome/css/font-awesome.css",
        ], function() {

            KAJONA.admin.portaleditor.RTE.init();
        });


    },

	showActions: function (elementSysId) {
	    $('#container_'+elementSysId).attr('class', 'peContainerHover');
	    $('#menu_'+elementSysId).attr('class', 'menuHover');
	},

	hideActions: function (elementSysId) {
		$('#container_'+elementSysId).attr('class', 'peContainerOut');
		$('#menu_'+elementSysId).attr('class', 'menuOut');
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

	    	//reset iframe
	    	peDialog.setContentRaw("");
	    }
	},

	addNewElements: function (strPlaceholder, strPlaceholderName, arrElements) {
		this.objPlaceholderWithElements[strPlaceholder] = {
			placeholderName: strPlaceholderName,
			elements: arrElements
		};
	}


};

KAJONA.admin.portaleditor.RTE = {};
KAJONA.admin.portaleditor.RTE.modifiedFields = {};

KAJONA.admin.portaleditor.RTE.savePage = function () {

    //console.group('savePage');

    $.each(KAJONA.admin.portaleditor.RTE.modifiedFields, function (key, value) {
        var keySplitted = key.split('#');

        var data = {
            systemid: keySplitted[0],
            property: keySplitted[1],
            value: value
        };

        $.post(KAJONA_WEBPATH + '/xml.php?admin=1&module=pages_content&action=updateObjectProperty', data, function () {
            //console.warn('server response');
            //console.log(this.responseText);
        });
    });
    //console.groupEnd('savePage');
    $('#savePageLink > img').attr('src', $('#savePageLink > img').attr('src').replace(".png", "Disabled.png"));
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
        //console.warn('paste val: ', content, cleanContent);
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
    //console.log("RTE editor init");
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
            plugins: {
                halloreundo: {}
            },
            modified: function (event, obj) {
                var attr = $(this).attr('data-kajona-editable');

                $('#savePageLink > img').attr('src', $('#savePageLink > img').attr('src').replace("Disabled", ""));
                KAJONA.admin.portaleditor.RTE.modifiedFields[attr] = obj.content;
                //console.log('modified field', attr, obj.content);
            }
        };

        if (!isPlaintext) {
            halloConfig.plugins = {
                halloformat: {},
                hallolists: {},
                halloreundo: {}
            };
        }

        //finally init hallo editor
        editable.hallo(halloConfig);
    });

    // warn user if there are unsaved changes when leaving the page
    $(window).on('beforeunload', function () {
        // check if there are unsaved changes
        var unsavedChanges = false;
        $.each(KAJONA.admin.portaleditor.RTE.modifiedFields, function () {
            unsavedChanges = true;
            return false;
        });

        if (unsavedChanges) {
            return KAJONA.admin.lang.pe_rte_unsavedChanges;
        }
    });
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
                    classes: 'ui-tooltip-youtube ui-tooltip-shadow'
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
                        classes: 'ui-tooltip-youtube ui-tooltip-shadow'
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
                        classes: 'ui-tooltip-youtube ui-tooltip-shadow'
                    }
                });
            }
        });
    }

};


KAJONA.admin.tooltip.initTooltip();