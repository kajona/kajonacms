/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * AJAX functions for connecting to the server
 *
 * @module ajax
 */
define('ajax', ['jquery', 'statusDisplay', 'workingIndicator', 'tooltip'], function ($, statusDisplay, workingIndicator, tooltip) {

    return /** @alias module:ajax */ {

        /**
         * Shorthand method to load a html fragement into a node identified by the selector.
         * Data is fetched by GET, loading indicators are triggered automatically.
         * Scripts in the response are executed, tooltips are enabled, too.
         * During loading, a loading container is shown and the general loading animation is enabled
         *
         * Possible usage:
         * ajax.loadUrlToElement('#report_container', '/xml.php?admin=1&module=stats&action=getReport', '&plugin=general');
         *
         * @param strElementSelector
         * @param strUrl
         * @param strData
         */
        loadUrlToElement: function(strElementSelector, strUrl, strData, bitBlockLoadingContainer) {
            workingIndicator.start();

            if(!bitBlockLoadingContainer) {
                $(strElementSelector).html('<div class="loadingContainer"></div>');
            }

            var target = strElementSelector;
            $.get(KAJONA_WEBPATH+strUrl, strData)
                .done(
                    function(data) {
                        $(strElementSelector).html(data);
                        tooltip.initTooltip();
                    }
                )
                .always(
                    function(response) {
                        workingIndicator.stop();
                    }
                )
                .error(function(data) {
                    statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                });
        },

        getDataObjectFromString: function(strData, bitFirstIsSystemid) {
            if (typeof strData === "string") {
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
            } else {
                return strData;
            }
        },

        regularCallback: function(data, status, jqXHR) {
            if(status == 'success') {
                statusDisplay.displayXMLMessage(data)
            }
            else {
                statusDisplay.messageError("<b>Request failed!</b>")
            }
        },

        /**
         * General helper to fire an ajax request against the backend
         *
         * @param module
         * @param action
         * @param systemid
         * @param objCallback
         * @param objDoneCallback
         * @param objErrorCallback
         * @param strMethod default is POST
         */
        genericAjaxCall : function(module, action, systemid, objCallback, objDoneCallback, objErrorCallback, strMethod) {
            var postTarget = KAJONA_WEBPATH + '/xml.php?admin=1&module='+module+'&action='+action;
            var data;
            if(systemid) {
                data = this.getDataObjectFromString(systemid, true);
            }

            workingIndicator.start();
            $.ajax({
                type: strMethod ? strMethod : 'POST',
                url: postTarget,
                data: data,
                error: objCallback,
                success: objCallback,
                dataType: 'text'
            }).always(
                function() {
                    workingIndicator.stop();
                }
            ).error(function() {
                if(objErrorCallback) {
                    objErrorCallback();
                }
            }).done(function() {
                if(objDoneCallback) {
                    objDoneCallback();
                }
            });

        },

        setAbsolutePosition : function(systemIdToMove, intNewPos, strIdOfList, objCallback, strTargetModule) {
            if(strTargetModule == null || strTargetModule == "")
                strTargetModule = "system";

            if(typeof objCallback == 'undefined' || objCallback == null)
                objCallback = this.regularCallback;


            this.genericAjaxCall(strTargetModule, "setAbsolutePosition", systemIdToMove + "&listPos=" + intNewPos, objCallback);
        },

        setSystemStatus : function(strSystemIdToSet, bitReload) {
            var me = this;
            var objCallback = function(data, status, jqXHR) {
                if (status == 'success') {
                    statusDisplay.displayXMLMessage(data);

                    if (bitReload !== null && bitReload === true) {
                        location.reload();
                    }

                    if (data.indexOf('<error>') == -1 && data.indexOf('<html>') == -1) {
                        var newStatus = $($.parseXML(data)).find("newstatus").text();
                        var link = $('#statusLink_' + strSystemIdToSet);

                        var adminListRow = link.parents('.admintable > tbody').first();
                        if (!adminListRow.length) {
                            adminListRow = link.parents('.grid > ul > li').first();
                        }

                        if (newStatus == 0) {
                            link.html(me.setSystemStatusMessages.strInActiveIcon);
                            adminListRow.addClass('disabled');
                        } else {
                            link.html(me.setSystemStatusMessages.strActiveIcon);
                            adminListRow.removeClass('disabled');
                        }

                        tooltip.addTooltip($('#statusLink_' + strSystemIdToSet).find("[rel='tooltip']"));
                    }
                } else {
                    // in the error case the arguments are (jqXHR, status) so we need to get the responseText from the
                    // xhr object
                    statusDisplay.messageError(data.responseText);
                }
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
