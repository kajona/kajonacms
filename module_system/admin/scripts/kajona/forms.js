
/*
 * -------------------------------------------------------------------------
 * Admin form functions
 * -------------------------------------------------------------------------
 */
define(['jquery'], function ($) {

    var forms = {};

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


    return forms;

});


