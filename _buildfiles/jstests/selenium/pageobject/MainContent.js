"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');


/** Constants */
const MAINCONTENT = by.css("div#content");

/**
 *
 */
class MainContent extends BasePage {

    constructor() {
        super();
    }

    /**
     *
     * @returns {WebElementPromise}
     */
    get element_mainContent() {
        return this.webDriver.findElement(MAINCONTENT);
    }
}


/** @type {MainContent} */
module.exports = MainContent;
