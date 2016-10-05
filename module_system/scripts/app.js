
require(['jquery', 'jqueryui', 'jquery-touchPunch', 'bootstrap', 'v4skin', 'loader', 'dialog', 'folderview'], function(jquery, jqueryui, touch, bootstrap, v4skin, loader, Dialog, folderview) {

    //backwards compatibility
    if (typeof KAJONA == "undefined") {
        KAJONA = {
            util: {},
            portal: {
                lang: {}
            },
            admin: {
                folderview: {},
                lang: {},
                forms: {}
            }
        };
    }

    KAJONA.admin.folderview.dialog = new Dialog('folderviewDialog', 0);
    folderview.dialog = KAJONA.admin.folderview.dialog;

    // BC layer

    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);
});
