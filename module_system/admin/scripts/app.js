
require(['jquery', 'jqueryui', 'jquery-touchPunch', 'bootstrap', 'dialog', 'loader'], function ($, jqueryui, touch, bootstrap, ModalDialog, Loader) {

    // @TODO this is really bad since we write to the global scope
    KAJONA = {};
    KAJONA.admin = {};
    KAJONA.admin.loader = new Loader();
    KAJONA.admin.folderview = {};
    KAJONA.admin.folderview.dialog = new ModalDialog('folderviewDialog', 0);
    jsDialog_0 = new ModalDialog('jsDialog_0', 0);
    jsDialog_1 = new ModalDialog('jsDialog_1', 1);
    jsDialog_2 = new ModalDialog('jsDialog_2', 2);
    jsDialog_3 = new ModalDialog('jsDialog_3', 3);

});
