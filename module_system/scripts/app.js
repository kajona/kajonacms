
require(['jquery', 'jqueryui', 'jquery-touchPunch', 'bootstrap', 'v4skin', 'loader', 'dialog'], function(jquery, jqueryui, touch, bootstrap, v4skin, loader, Dialog) {
    // BC layer
    KAJONA.admin.folderview.dialog = new Dialog('folderviewDialog', 0);
    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);

    // BC layer now we can fire the jquery document ready events
    $.holdReady(false);
});
