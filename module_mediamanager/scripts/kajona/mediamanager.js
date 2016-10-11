//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt


define(["jquery", "ajax", "statusDisplay"], function($, ajax, statusDisplay) {




    var createFolderBackend = function (strFmRepoId, strFolder) {
        ajax.genericAjaxCall("mediamanager", "createFolder", strFmRepoId + "&folder=" + strFolder, function (data, status, jqXHR) {
            if (status == 'success') {
                //check if answer contains an error
                if (data.indexOf("<error>") != -1) {
                    statusDisplay.displayXMLMessage(data);
                }
                else {
                    ajax.genericAjaxCall("mediamanager", "partialSyncRepo", strFmRepoId, function (data, status, jqXHR) {
                        if (status == 'success')
                            location.reload();
                        else
                            statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                    });
                }
            }
            else {
                statusDisplay.messageError("<b>Request failed!</b><br />" + data);
            }
        })
    };

    var mediamanager = {};

    mediamanager.createFolder = function (strInputId, strRepoId) {
        var strNewFoldername = document.getElementById(strInputId).value;
        if (strNewFoldername != "") {
            createFolderBackend(strRepoId, strNewFoldername);
        }

    };


    return mediamanager;

});

