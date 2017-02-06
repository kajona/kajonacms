"use strict";

/** require statements */
const MainContent = requireHelper('/pageobject/MainContent.js');

/** Constants */
const FORM = by.css("form.form-horizontal");
const SAVE_BUTTON = by.css("button[name=submitbtn]");

/**
 *
 */
class Form extends MainContent {

    constructor() {
        super();
    }


    get element_form() {
        return this.element_mainContent.findElement(FORM);
    }

    get save_button() {
        return this.element_form.findElement(SAVE_BUTTON);
    }
}

/** @type {Form} */
module.exports = Form;
