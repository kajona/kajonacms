
/**
 * AJAX functions for connecting to the server
 */
define(['jquery', 'statusDisplay', 'workingIndicator', 'tooltip'], function ($, statusDisplay, workingIndicator, tooltip) {

    return {

        getDataObjectFromString: function(strData, bitFirstIsSystemid) {
            //strip other params, backwards compatibility
            var arrElements = strData.split("&");
            var data = { };

            if(bitFirstIsSystemid)
                data["systemid"] = arrElements[0];

            //first one is the systemid
            if(arrElements.length > 1) {
                $.each(arrElements, function(index, strValue) {
                    if(!bitFirstIsSystemid || index > 0) {
                        var arrSingleParams = strValue.split("=");
                        data[arrSingleParams[0]] = arrSingleParams[1];
                    }
                });
            }
            return data;
        },

        regularCallback: function(data, status, jqXHR) {
            if(status == 'success') {
                statusDisplay.displayXMLMessage(data)
            }
            else {
                statusDisplay.messageError("<b>Request failed!</b>")
            }
        },


        genericAjaxCall : function(module, action, systemid, objCallback) {
            var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
            var data;
            if(systemid) {
                data = this.getDataObjectFromString(systemid, true);
            }

            workingIndicator.start();
            $.ajax({
                type: 'POST',
                url: postTarget,
                data: data,
                error: objCallback,
                success: objCallback,
                dataType: 'text'
            }).always(
                function(response) {
                    workingIndicator.stop();
                }
            );

        },

        setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList, objCallback, strTargetModule) {
            if(strTargetModule == null || strTargetModule == "")
                strTargetModule = "system";

            if(typeof objCallback == 'undefined' || objCallback == null)
                objCallback = this.regularCallback;


            this.genericAjaxCall(strTargetModule, "setAbsolutePosition", systemIdToMove + "&listPos=" + intNewPos, objCallback);
        },

        setSystemStatus : function(strSystemIdToSet, bitReload) {
            var objCallback = function(data, status, jqXHR) {
                if(status == 'success') {
                    statusDisplay.displayXMLMessage(data);

                    if(bitReload !== null && bitReload === true)
                        location.reload();

                    if (data.indexOf('<error>') == -1 && data.indexOf('<html>') == -1) {
                        var newStatus = $($.parseXML(data)).find("newstatus").text();
                        var link = $('#statusLink_' + strSystemIdToSet);

                        var adminListRow = link.parents('.admintable > tbody').first();
                        if (!adminListRow.length) {
                            adminListRow = link.parents('.grid > ul > li').first();
                        }

                        if (newStatus == 0) {
                            link.html(KAJONA.admin.ajax.setSystemStatusMessages.strInActiveIcon);
                            adminListRow.addClass('disabled');
                        } else {
                            link.html(KAJONA.admin.ajax.setSystemStatusMessages.strActiveIcon);
                            adminListRow.removeClass('disabled');
                        }

                        tooltip.addTooltip($('#statusLink_' + strSystemIdToSet).find("[rel='tooltip']"));
                    }
                }
                else
                    statusDisplay.messageError(data);
            };

            tooltip.removeTooltip($('#statusLink_' + strSystemIdToSet).find("[rel='tooltip']"));
            this.genericAjaxCall("system", "setStatus", strSystemIdToSet, objCallback);
        },

        setSystemStatusMessages : {
            strInActiveIcon : '',
            strActiveIcon : ''
        }

    };

});
