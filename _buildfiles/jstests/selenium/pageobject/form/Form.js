"use strict";

/**
 * require statements
 */
var MainContent = require('../MainContent.js');
var Constants = require('../Constants.js');

/**
 *
 */
class Form extends MainContent {

    constructor() {
        super();
    }


    get elementForm() {
        return this.element_mainContent.findElement(By.css(Constants.FORM_CSS_ROOT));
    }
}

/** @type {Form} */
module.exports = Form;
