"use strict";

/**
 * require statements
 */
var MainContent = requireHelper('/pageobject/MainContent.js');
var Constants = requireHelper('/pageobject/Constants.js');

/**
 *
 */
class Form extends MainContent {

    constructor() {
        super();
    }


    get element_form() {
        return this.element_mainContent.findElement(By.css(Constants.FORM_CSS_ROOT));
    }

    get save_button() {
        return this.element_form.findElement(By.css(Constants.FORM_CSS_SAVEBUTTON));
    }
}

/** @type {Form} */
module.exports = Form;
