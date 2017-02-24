/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Folderview functions
 *
 * @module folderview
 */
define("folderview", ["jquery", "util"], function($, util){

    return /** @alias module:folderview */ {
        /**
         * holds a reference to the ModalDialog
         */
        dialog: undefined,

        /**
         * holds CKEditors CKEditorFuncNum parameter to read it again in folderview.fillFormFields()
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
                window.opener.require('folderview').fillFormFields(arrTargetsValues);
            } else if (parent) {
                parent.require('folderview').fillFormFields(arrTargetsValues);
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
         * Sets an array of items to an object list. We remove only elements which are available in the arrAvailableIds array
         *
         * @param {string} strElementName  - name of the objectlist element
         * @param {Array} arrItems        - array with item of the following format {strSystemId: <systemid>, strDisplayName:<displayname>, strIcon:<icon>}
         * @param {Array} arrAvailableIds -
         * @param {string} strDeleteButton -
         */
        setObjectListItems: function(strElementName, arrItems, arrAvailableIds, strDeleteButton){
            var table = util.getElementFromOpener(strElementName);

            var tbody = table.find('tbody');
            if(tbody.length > 0) {
                // remove only elements which are in the arrAvailableIds array
                tbody.children().each(function(){
                    var strId = $(this).find('input[type="hidden"]').val();
                    if($.inArray(strId, arrAvailableIds) !== -1) {//if strId in array
                        $(this).remove();
                    }
                });

                // add new elements
                for(var i = 0; i < arrItems.length; i++) {
                    var strEscapedTitle = $('<div></div>').text(arrItems[i].strDisplayName).html();
                    var html = '';
                    html+= '<tr>';
                    html+= '    <td>' + arrItems[i].strIcon + '</td>';
                    html+= '    <td>' + strEscapedTitle + ' <input type="hidden" name="' + strElementName + '[]" value="' + arrItems[i].strSystemId + '" /></td>';
                    html+= '    <td class="icon-cell">';
                    html+= '        <a href="#" onclick="require(\'v4skin\').removeObjectListItem(this);return false">' + strDeleteButton + '</a>';
                    html+= '    </td>';
                    html+= '</tr>';

                    tbody.append(html);
                }
            }

            this.close();
        },

        /**
         * Sets an array of items to an checkbox object list
         *
         * @param {string} strElementName  - name of the objectlist element
         * @param {Array} arrItems        - array with item of the following format {strSystemId: <systemid>, strDisplayName:<displayname>, strIcon:<icon>, strPath:<string>}
         */
        setCheckboxArrayObjectListItems : function(strElementName, arrItems){
            var form = util.getElementFromOpener(strElementName);

            var table = form.find('table');
            if(table.length > 0) {
                // add new elements
                for(var i = 0; i < arrItems.length; i++) {
                    var strEscapedTitle = $('<div></div>').text(arrItems[i].strDisplayName).html();
                    var html = '';

                    // check whether form entry exists already in the table if so skip. We need to escape the form element name
                    // since it contains brackets
                    var formElementName = strElementName + '[' + arrItems[i].strSystemId + ']';
                    var existingFormEls = table.find('input[name=' + formElementName.replace(/(:|\.|\[|\]|,)/g, "\\$1") + ']');
                    if (existingFormEls.length > 0) {
                        continue;
                    }

                    html+= '<tbody>';
                    html+= '<tr data-systemid="' + arrItems[i].strSystemId + '">';

                    var value;
                    if (arrItems[i].strValue) {
                        value = JSON.stringify(arrItems[i].strValue);
                        value = value.replace(/"/g, "&quot;");
                    } else {
                        value = 'on';
                    }

                    html+= '    <td class="listcheckbox"><input type="checkbox" name="' + formElementName + '" value="' + value + '" data-systemid="' + arrItems[i].strSystemId + '" checked></td>';
                    html+= '    <td class="listimage">' + arrItems[i].strIcon + '</td>';
                    html+= '    <td class="title">';
                    html+= '        <div class="small text-muted">' + arrItems[i].strPath + '</div>';
                    html+= '        ' + arrItems[i].strDisplayName;
                    html+= '    </td>';
                    html+= '</tr>';
                    html+= '</tbody>';

                    table.append(html);
                }
            }

            this.close();
        },

        /**
         * fills the form fields with the selected values
         */
        close: function () {
            if (window.opener) {
                window.close();
            } else if (parent) {
                var context = parent.require('folderview');
                // in case we call setCheckboxArrayObjectListItems without dialog
                if (context.dialog) {
                    context.dialog.hide();
                    context.dialog.setContentRaw("");
                }
            }
        }
    };

});