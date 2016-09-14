
/**
 * little helper function for the system permissions matrix
 */
define(["jquery"], function($){

    return {
        checkRightMatrix : function () {
            // mode 1: inheritance
            if (document.getElementById('inherit').checked) {
                // loop over all checkboxes to disable them
                for (var intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
                    var objCurElement = document.forms['rightsForm'].elements[intI];
                    if (objCurElement.type == 'checkbox') {
                        if (objCurElement.id != 'inherit') {
                            objCurElement.disabled = true;
                            objCurElement.checked = false;
                            var strCurId = "inherit," + objCurElement.id;
                            if (document.getElementById(strCurId) != null) {
                                if (document.getElementById(strCurId).value == '1') {
                                    objCurElement.checked = true;
                                }
                            }
                        }
                    }
                }
            } else {
                // mode 2: no inheritance, make all checkboxes editable
                for (intI = 0; intI < document.forms['rightsForm'].elements.length; intI++) {
                    var objCurElement = document.forms['rightsForm'].elements[intI];
                    if (objCurElement.type == 'checkbox') {
                        if (objCurElement.id != 'inherit') {
                            objCurElement.disabled = false;
                        }
                    }
                }
            }
        },

        toggleMode : null,
        toggleEmtpyRows : function (strVisibleName, strHiddenName, parentSelector) {

            var $rowToggleLink = $('#rowToggleLink');
            KAJONA.admin.permissions.toggleMode = $rowToggleLink.hasClass("rowsVisible")  ? "hide" : "show";

            $(parentSelector).each(function() {

                if($(this).find("input:checked").length == 0 && $(this).find("th").length == 0) {

                    if(KAJONA.admin.permissions.toggleMode == "show") {
                        $(this).removeClass("hidden");
                    }
                    else {
                        $(this).addClass("hidden");
                    }
                }
                else if(KAJONA.admin.permissions.toggleMode == "show") {
                    $(this).removeClass("hidden");
                }
            });


            if($rowToggleLink.hasClass("rowsVisible")) {
                $rowToggleLink.html(strVisibleName);
                $rowToggleLink.removeClass("rowsVisible");
            }
            else {
                $rowToggleLink.html(strHiddenName);
                $rowToggleLink.addClass("rowsVisible")
            }
        },

        submitForm : function() {
            var objResponse = {
                bitInherited : $("#inherit").is(":checked"),
                arrConfigs : []
            };

            $('#rightsForm table tr input:checked').each(function(){
                if($(this).find("input:checked").length == 0) {
                    objResponse.arrConfigs.push($(this).attr('id'));
                }
            });

            $("#responseContainer").html('').addClass("loadingContainer");

            $.ajax({
                url: KAJONA_WEBPATH + '/xml.php?admin=1&module=right&action=saveRights&systemid='+ $('#systemid').val(),
                type: 'POST',
                data: {json: JSON.stringify(objResponse)},
                dataType: 'json'
            }).done(function(data) {
                $("#responseContainer").removeClass("loadingContainer").html(data.message);
            });


            return false;
        },

        /**
         * Filters the rows of the permission matrix based on the value of the input element
         * @param evt
         * @returns {boolean}
         */
        filterMatrix : function(evt) {

            // If it's the propertychange event, make sure it's the value that changed.
            if (window.event && event.type == "propertychange" && event.propertyName != "value")
                return false;


            var strFilter = $('#filter').val().toLowerCase();
            if(strFilter.length < 3 && strFilter.length > 0)
                return false;

            // Clear any previously set timer before setting a fresh one, default delay are 500ms
            window.clearTimeout($(this).data("timeout"));
            $(this).data("timeout", setTimeout(function () {
                // Do your thing here
                var strFilter = $('#filter').val().toLowerCase();


                $('#rightsForm table tr').each(function() {
                    var $tr = $(this);

                    if(strFilter.length > 0 && $tr.find("td:first-child").text().toLowerCase().indexOf(strFilter) === -1) {
                        $tr.addClass("hidden")
                    }
                    else {
                        $tr.removeClass("hidden");
                    }

                });

            }, 500));


            return false;
        }
    };

});