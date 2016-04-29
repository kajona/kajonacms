"use strict";

/**
 * require statements
 */
var AdminBasePage = require('../pageobject/AdminBasePage.js');

/**
 *
 * @param {webdriver.WebDriver} webDriver
 * @constructor
 */
class AdminLandingPage extends AdminBasePage {
    /**
     *
     * @param {webdriver.WebDriver} webDriver
     */
    constructor(webDriver) {
        super(webDriver);
    }
}

/** @type {AdminLandingPage} */
module.exports = AdminLandingPage;
