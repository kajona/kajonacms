//   (c) 2013-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

define(['jquery', 'ajax', 'tooltip', 'statusDisplay'], function ($, ajax, tooltip, statusDisplay) {


    var search = {


        triggerFullSearch: function () {

            var strQuery = $('#search_query').val();
            if (strQuery == "")
                return;

            $('#search_container').html("<div class=\"loadingContainer\"></div>");


            var filtermodules = "";
            $('input[name=search_formfiltermodules\\[\\]]:checked').each(function () {
                if (filtermodules != "")
                    filtermodules += ",";

                filtermodules += ($(this).val());
            });

            var startdate = $('#search_changestartdate').val();
            var enddate = $('#search_changeenddate').val();
            var userfilter = $('#search_formfilteruser_id').val();

            ajax.genericAjaxCall("search", "renderSearch", "&search_query=" + encodeURIComponent(strQuery) + "&filtermodules=" + filtermodules + "&search_changestartdate=" + startdate + "&search_changeenddate=" + enddate + "&search_formfilteruser_id=" + userfilter + "", function (data, status, jqXHR) {
                if (status == 'success') {
                    var intStart = data.indexOf("[CDATA[") + 7;
                    $("#search_container").html(data.substr(intStart, data.lastIndexOf("]]>") - intStart));
                    if (data.indexOf("[CDATA[") < 0) {
                        var intStart = data.indexOf("<error>") + 7;
                        $("#search_container").html(data.substr(intStart, data.indexOf("</error>") - intStart));
                    }
                    tooltip.initTooltip();
                }
                else {
                    statusDisplay.messageError("<b>Request failed!</b><br />" + data);
                }
            });
        }


    };

    return search;

});