
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
    if (typeof KAJONA == "undefined") {
        KAJONA = {
            util: {},
            portal: {
                lang: {}
            },
            admin: {
                lang: {},
                forms: {}
            }
        };
    }

    KAJONA.util = util;
    KAJONA.util.desktopNotification = desktopNotification;

    KAJONA.admin.loader = loader;

    KAJONA.admin.folderview = folderview;

    KAJONA.admin.folderview.dialog = new Dialog('folderviewDialog', 0);
    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);

    KAJONA.admin.tooltip = tooltip;
    KAJONA.admin.switchLanguage = function(strLanguageToLoad) {
        util.switchLanguage(strLanguageToLoad);
    };
    //KAJONA.admin.permissions = permissions;
    KAJONA.admin.statusDisplay = statusDisplay;
    //KAJONA.admin.systemtask = systemTask;
    KAJONA.admin.ajax = ajax;
    KAJONA.admin.forms = forms;
    KAJONA.admin.lists = lists;
    //KAJONA.admin.dashboardCalendar = dashboardCalendar;
    KAJONA.admin.messaging = messaging;
    KAJONA.admin.renderTocNavigation = messaging.render;
    KAJONA.admin.WorkingIndicator = workingIndicator;
    //KAJONA.admin.changelog = changelog;

    // BC layer now we can fire the jquery document ready events
    $.holdReady(false);

});
