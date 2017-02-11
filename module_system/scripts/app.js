
require(['jquery', 'jquery-ui', 'jquery-touchPunch', 'bootstrap', 'v4skin', 'loader', 'dialog', 'folderview', 'lists', 'dialogHelper', 'ajax', 'contentToolbar', 'tooltip', 'breadcrumb'],
    function(jquery, jqueryui, touch, bootstrap, v4skin, loader, Dialog, folderview, lists, dialogHelper, ajax, contentToolbar, tooltip, breadcrumb) {

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

    // BC layer

    jsDialog_0 = new Dialog('jsDialog_0', 0);
    jsDialog_1 = new Dialog('jsDialog_1', 1);
    jsDialog_2 = new Dialog('jsDialog_2', 2);
    jsDialog_3 = new Dialog('jsDialog_3', 3);


    //register the global router

    routie('*', function(url) {
        console.log('processing url '+url);

        if(url.trim() === '') {
            url = "dashboard";

        }

        if(url.charAt(0) == "/") {
            url = url.substr(1);
        }



        //split to get module, action and params
        var strParams = '';
        if( url.indexOf('?') > 0) {
            strParams = url.substr(url.indexOf('?'));
            url = url.substr(0, url.indexOf('?'));
        }

        var arrSections = url.split("/");

        var strUrlToLoad = '/index.php?admin=1&module='+arrSections[0];
        if(arrSections.length >= 2) {
            strUrlToLoad += '&action='+arrSections[1];
        }
        if(arrSections.length >= 3) {
            strUrlToLoad += '&systemid='+arrSections[2];
        }

        strUrlToLoad += strParams;

        console.log('Loading url '+strUrlToLoad);

        contentToolbar.resetBar();
        breadcrumb.resetBar();
        tooltip.removeTooltip($('*[rel=tooltip]'));

        ajax.loadUrlToElement('#moduleOutput', strUrlToLoad, 'contentFill=1');



    });

});
