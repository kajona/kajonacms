"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

/**
 *
 */
class BasePage {

    constructor() {
        this.webDriver = SeleniumUtil.getWebDriver();
    }
}

/** @type {BasePage} */
module.exports = BasePage;

