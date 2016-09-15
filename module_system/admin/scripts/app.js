
require([
    'jquery', 'jqueryui', 'jquery-touchPunch',
    'bootstrap', 'v4skin',
    'loader', 'dialog', 'util', 'desktopNotification', 'folderview', 'tooltip', 'statusDisplay',
    'ajax', 'forms', 'lists', 'messaging', 'workingIndicator'], function (
        jquery, jqueryui, touch,
        bootstrap, v4skin,
        loader, Dialog, util, desktopNotification, folderview, tooltip, statusDisplay,
        ajax, forms, lists, messaging, workingIndicator) {

    // BC layer
    KAJONA.admin.folderview.dialog = new Dialog('folderviewDialog', 0);
    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);

});
