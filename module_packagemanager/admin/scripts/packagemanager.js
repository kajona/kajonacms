//   (c) 2007-2013 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (!KAJONA) {
	alert('load kajona.js before!');
}


KAJONA.admin.packagemanager = {
    arrPackageNames : [],

    addPackageToTest : function(strName) {
        this.arrPackageNames.push(strName);
    },

    triggerUpdateCheck : function() {
        KAJONA.admin.ajax.genericAjaxCall('packagemanager', 'getUpdateIcons', '&packages='+this.arrPackageNames.join(","), function(data, status, jqXHR) {
            if(status == 'success') {
                $.each(jQuery.parseJSON(data), function (packageName, content) {
                    $('#updateWrapper'+packageName).html(content);
                    KAJONA.util.evalScript(content);
                    KAJONA.admin.tooltip.addTooltip($('#updateWrapper'+packageName+' img'));
                    KAJONA.admin.tooltip.addTooltip($('#updateWrapper'+packageName+' a'));
                });
            }
            else {
                KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);
            }

        });
    }
};





