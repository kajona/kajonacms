"use strict";

/**
 * require statements
 */
var SeleniumUtil = require('../../util/SeleniumUtil.js');
var MainContent = require('../../pageobject/MainContent.js');

/**
 *
 */
class BaseForm extends MainContent {

    constructor() {
        super();

        this._initElements();
        this._initObjects();
    }

    _initElements() {
        super._initElements();

        this._CSS_FORM_ROOT = "form.form-horizontal";

        this._CSS_LIST_ROWS = "tbody > tr:not([data-systemid='batchActionSwitch'])";
        this._CSS_LIST_BATCHACTIONROW ="tbody > tr[data-systemid='batchActionSwitch']";
        this._CSS_LIST_PAGINATION = ".pager";


        /** @type {WebElementPromise}*/
        this._elementForm = this.element_mainContent.findElement(By.css(this._CSS_FORM_ROOT));
    }

    _initObjects() {
        super._initObjects();
    }


}

/** @type {BaseForm} */
module.exports = BaseForm;
