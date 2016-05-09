"use strict";

/**
 * require statements
 */
var BasePage = require('./base/BasePage.js');
var Constants = require('./Constants.js');

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
        return this.webDriver.findElement(By.css(Constants.MAINCONTENT_CSS_MAINCONTENT));
    }
}


/** @type {MainContent} */
module.exports = MainContent;
