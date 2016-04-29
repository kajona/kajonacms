"use strict";

/**
 *
 */
class BasePage {

    /**
     *
     * @param {webdriver.WebDriver} webDriver
     */
    constructor(webDriver) {
        this.webDriver = webDriver;
    }
}

/** @type {BasePage} */
module.exports = BasePage;