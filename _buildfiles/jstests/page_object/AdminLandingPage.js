"use strict";

/**
 * require statements
 */
var AdminBasePage = require('../page_object/AdminBasePage.js');

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
