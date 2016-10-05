
/**
 * Folderview functions
 */
define(["jquery"], function($){

    return {
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
            // debugger;
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
         * fills the form fields with the selected values
         */
        close: function () {
            if (window.opener) {
                window.close();
            } else if (parent) {
                var context = parent.require('folderview');
                context.dialog.hide();
                context.dialog.setContentRaw("");
            }
        }
    };

});