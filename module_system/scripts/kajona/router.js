/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 *
 *
 * @module router
 */
define("router", ['jquery', 'contentToolbar', 'tooltip', 'breadcrumb', 'moduleNavigation', 'quickhelp', 'ajax'], function ($, contentToolbar, tooltip, breadcrumb, moduleNavigation, quickhelp, ajax) {

    var initRouter = function() {

        routie('*', function(url) {
            console.log('processing url '+url);

            if(url.trim() === '') {
                if($('#loginContainer')) {
                    return;
                }
                url = "dashboard";
            }

            if(url.charAt(0) == "/") {
                url = url.substr(1);
            }

            //react on peClose statements by reloading the parent view
            var isStackedDialog = !!(window.frameElement && window.frameElement.nodeName && window.frameElement.nodeName.toLowerCase() == 'iframe');
            if(isStackedDialog && url.indexOf('peClose=1') != -1) {
                parent.KAJONA.admin.folderview.dialog.hide();
                console.log('parent call: '+parent.window.location.hash);
                parent.routie.reload();
                return;
            }


            //split to get module, action and params
            var strParams = '';
            if( url.indexOf('?') > 0) {
                strParams = url.substr(url.indexOf('?')+1);
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

            strUrlToLoad += "&"+strParams;

            if(isStackedDialog && strUrlToLoad.indexOf('folderview') == -1) {
                strUrlToLoad += "&folderview=1";
            }

            strUrlToLoad += "&contentFill=1";
            console.log('Loading url '+strUrlToLoad);

            contentToolbar.resetBar();
            breadcrumb.resetBar();
            quickhelp.resetQuickhelp();
            tooltip.removeTooltip($('*[rel=tooltip]'));
            moduleNavigation.setModuleActive(arrSections[0]);

            //split between post and get
            if(KAJONA.admin.forms.submittedEl != null) {
                var data = $(KAJONA.admin.forms.submittedEl).serialize();
                KAJONA.admin.forms.submittedEl = null;
                ajax.loadUrlToElement('#moduleOutput', strUrlToLoad, data, false, 'POST');

            } else {
                ajax.loadUrlToElement('#moduleOutput', strUrlToLoad);

            }


        });
    };



    /** @alias module:router */
    return {

        loadUrl : function(strUrl) {
            if(strUrl == document.location.hash) {
                routie.reload();
            } else {
                routie(strUrl);
            }

        },

        init : function() {
            initRouter();
        }



    };


});

