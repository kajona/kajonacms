
require(['router', 'jquery', 'jquery-ui', 'jquery-touchPunch', 'bootstrap', 'v4skin', 'loader', 'dialog', 'folderview', 'lists', 'dialogHelper', 'ajax'],
    function(router, jquery, jqueryui, touch, bootstrap, v4skin, loader, Dialog, folderview, lists, dialogHelper, ajax) {

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

    $ = jquery;

    router.init();

    // BC layer

    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);

    KAJONA.admin.forms.submittedEl = null;

    //register the global router




});
