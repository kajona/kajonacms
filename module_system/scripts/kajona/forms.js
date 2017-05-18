/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Form management
 *
 * @module forms
 */
define('forms', ['jquery', 'tooltip', 'router'], function ($, tooltip, router) {

    /** @exports forms */
    var forms = {};

    var arrOnMandatoryRendering = [];

    /**
     * Hides a field in the form
     *
     * @param objField - my be a jquery field or a id selector
     */
    forms.hideField = function(objField) {
        objField = this.getObjField(objField);

        var objFormGroup = objField.closest('.form-group');

        //1. Hide field
        objFormGroup.slideUp(0);

        //2. Hide hint -> check if previous element has 'form-group' and if <span> with .help-block exists
        var objHintFormGroup = objFormGroup.prev('.form-group');
        if(objHintFormGroup.find('div > span.help-block').length > 0) {
            objHintFormGroup.slideUp(0);
        }
    };

    /**
     * Shows a field in the form
     *
     * @param objField - my be a jquery object or an id selector
     */
    forms.showField = function(objField) {
        objField = this.getObjField(objField);

        var objFormGroup = objField.closest('.form-group');

        //1. Show field
        objFormGroup.slideDown(0);

        //2. Show hint -> check if previous element has 'form-group' and if <span> with .help-block exists
        var objHintFormGroup = objFormGroup.prev('.form-group');
        if(objHintFormGroup.find('div > span.help-block').length > 0) {
            objHintFormGroup.slideDown(0);
        }
    };

    /**
     * Disables a field
     *
     * @param objField - my be a jquery object or an id selector
     */
    forms.setFieldReadOnly = function(objField) {
        objField = this.getObjField(objField);

        if (objField.is('input:checkbox') || objField.is('select')) {
            objField.prop("disabled", "disabled");
        }
        else {
            objField.attr("readonly", "readonly");
        }
    };

    /**
     * Enables a field
     *
     * @param objField - my be a jquery object or an id selector
     */
    forms.setFieldEditable = function(objField) {
        objField = this.getObjField(objField);

        if (objField.is('input:checkbox') || objField.is('select')) {
            objField.removeProp("disabled");
        }
        else {
            objField.removeProp("readonly");
        }
    };

    /**
     * Gets the jQuery object
     *
     * @param objField - my be a jquery object or an id selector
     */
    forms.getObjField = function (objField) {
        // If objField is already a jQuery object
        if(objField instanceof jQuery) {
            return objField
        } else {
            // Convert to jQuery object
            return $(objField);
        }
    };


    forms.initForm = function(strFormid) {
        $('#'+strFormid+' input , #'+strFormid+' select , #'+strFormid+' textarea ').each(function() {
            $(this).attr("data-kajona-initval", $(this).val());
        });
    };

    forms.animateSubmit = function(objForm) {
        //try to get the button currently clicked

        if($(document.activeElement).prop('tagName') == "BUTTON") {
            $(document.activeElement).addClass('processing');
        }
        else {
            $(objForm).find('.savechanges[name=submitbtn]').addClass('processing');
        }
    };



    forms.changeLabel = '';
    forms.changeConfirmation = '';

    /**
     * Adds an onchange listener to the formentry with the passed ID. If the value is changed, a warning is rendered below the field.
     * In addition, a special confirmation may be required to change the field to the new value.
     *
     * @param strElementId
     * @param bitConfirmChange
     */
    forms.addChangelistener = function(strElementId, bitConfirmChange) {

        $('#'+strElementId).on('change', function(objEvent) {
            if($(this).val() != $(this).attr("data-kajona-initval")) {
                if($(this).closest(".form-group").find("div.changeHint").length == 0) {

                    if(bitConfirmChange && bitConfirmChange == true) {
                        var bitResponse = confirm(forms.changeConfirmation);
                        if(!bitResponse) {
                            $(this).val($(this).attr("data-kajona-initval"));
                            objEvent.preventDefault();
                            return;
                        }
                    }

                    $(this).closest(".form-group").addClass("has-warning");
                    $(this).closest(".form-group").children("div:first").append($('<div class="changeHint text-warning"><span class="glyphicon glyphicon-warning-sign"></span> ' + forms.changeLabel + '</div>'));
                }
            }
            else {
                if($(this).closest(".form-group").find("div.changeHint"))
                    $(this).closest(".form-group").find("div.changeHint").remove();

                $(this).closest(".form-group").removeClass("has-warning");
            }
        });

    };


    forms.renderMandatoryFields = function(arrFields) {

        for(var i=0; i<arrFields.length; i++) {
            var arrElement = arrFields[i];
            if(arrElement.length == 2) {
                var $objElement = $("#" + arrElement[0]);
                if($objElement)
                    $objElement.addClass("mandatoryFormElement");
            }
        }

        for(var i=0; i<arrOnMandatoryRendering.length; i++) {
            arrOnMandatoryRendering[i]();
        }

        arrOnMandatoryRendering = [];
    };

    forms.renderMissingMandatoryFields = function(arrFields) {
        $(arrFields).each(function(intIndex, strField) {
            var strFieldName = strField[0];
            if($("#"+strFieldName) && !$("#"+strFieldName).hasClass('inputWysiwyg')) {
                $("#"+strFieldName).closest(".form-group").addClass("has-error has-feedback");
                var objNode = $('<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>');
                $("#"+strFieldName).closest("div:not(.input-group)").append(objNode);
            }
        });
    };

    forms.loadTab = function(strEl, strHref) {
        if (strHref && $("#" + strEl).length > 0) {
            $("#" + strEl).html("");
            $("#" + strEl).addClass("loadingContainer");
            $.get(strHref, function(data) {
                $("#" + strEl).removeClass("loadingContainer");
                $("#" + strEl).html(data);
                tooltip.initTooltip();
            });
        }
    };

    forms.defaultOnSubmit = function (objForm) {
        $(objForm).on('submit', function() {
            return false;
        });
        KAJONA.admin.forms.submittedEl = objForm;
        $(window).off('unload');

        this.animateSubmit(objForm);


        var $btn = $(document.activeElement);
        if (
            /* there is an activeElement at all */
            $btn.length &&

            /* it's a child of the form */
            $(objForm).has($btn) &&

            /* it's really a submit element */
            $btn.is('button[type="submit"], input[type="submit"], input[type="image"]') &&

            /* it has a "name" attribute */
            $btn.is('[name]')
            ) {
                //name, value
                $(objForm).append($('<input>').attr('name', $btn.attr('name')).attr('value', $btn.val()));
                /* access $btn.attr("name") and $btn.val() for data */
        }

        router.removeLoadCallback("form_unlock");

        if(objForm.action == document.location) {
            routie.reload();
        } else {
            routie(objForm.action.substr(objForm.action.indexOf('#')));
        }

        return false;
    };


    forms.registerUnlockId = function (strId) {
        router.registerLoadCallback("form_unlock", function() {
            $.ajax({url: KAJONA_WEBPATH + '/xml.php?admin=1&module=system&action=unlockRecord&systemid='+strId});
            router.removeLoadCallback("form_unlock");
        });
    };


    /**
     * Adds a callback invoked as soon as the rendering of mandatory elements finished.
     * Usefull to adjust some classes or other content afterwards
     * @param objFn
     */
    forms.addMandatoryRenderingCallback = function(objFn) {
        arrOnMandatoryRendering.push(objFn);
    };


    return forms;

});


