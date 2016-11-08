
define(['jquery', 'dialog'], function ($, Dialog) {

    return {
        /**
         * Creates a new confirmation dialog
         *
         * @param strTitle
         * @param strContent
         * @param strConfirmationLabel
         * @param strConfirmationHref
         * @returns {*}
         */
        showConfirmationDialog : function(strTitle, strContent, strConfirmationLabel, strConfirmationHref) {
            var dialogInstance = new Dialog('jsDialog_1', 1);
            dialogInstance.setTitle(strTitle);
            dialogInstance.setContent(strContent, strConfirmationLabel, strConfirmationHref);
            dialogInstance.init();
            return dialogInstance;
        },

        /**
         * Opens an iframe based dialog to load other pages within a dialog. saves the dialog reference to folderview.dialog
         * in order to modify / access it later
         *
         * @param strUrl
         * @param strTitle
         * @returns
         */
        showIframeDialog : function(strUrl, strTitle) {
            var dialogInstance = new Dialog('folderviewDialog', 0);
            dialogInstance.setContentIFrame(strUrl);
            dialogInstance.setTitle(strTitle);
            dialogInstance.init();

            //register the dialog
            require(['folderview'], function(folderview) {
                folderview.dialog = dialogInstance;
            });

            return dialogInstance;

        }
    };

});
