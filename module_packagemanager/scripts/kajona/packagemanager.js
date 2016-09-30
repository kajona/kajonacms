//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$


define(["jquery", "ajax", "statusDisplay", "tooltip", "util"], function($, ajax, statusDisplay, tooltip, util) {

    var packagemanager = {

        arrPackageNames: [],
        objWrapperNames: {},


        addPackageToTest : function (strName, strWrapperName) {
            packagemanager.arrPackageNames.push(strName);
            packagemanager.objWrapperNames[strName] = strWrapperName;
        },

        triggerUpdateCheck : function () {
            ajax.genericAjaxCall('packagemanager', 'getUpdateIcons', '&packages=' + packagemanager.arrPackageNames.join(","), function (data, status, jqXHR) {
                if (status == 'success') {
                    $.each(jQuery.parseJSON(data), function (packageName, content) {
                        $('#updateWrapper' + packagemanager.objWrapperNames[packageName]).html(content);
                        util.evalScript(content);
                        tooltip.addTooltip($('#updateWrapper' + packagemanager.objWrapperNames[packageName] + ' img'));
                        tooltip.addTooltip($('#updateWrapper' + packagemanager.objWrapperNames[packageName] + ' a'));
                        tooltip.addTooltip($('#updateWrapper' + packagemanager.objWrapperNames[packageName] + ' span'));
                    });
                }
                else {
                    statusDisplay.messageError('<b>Request failed!</b><br />' + data);
                }

            });
        }
    };

    return packagemanager;
});




