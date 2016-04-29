"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/BasePage.js');
var LeftNavigation = require('../pageobject/LeftNavigation.js');
var TopMenu = require('../pageobject/TopMenu.js');
var MainContent = require('../pageobject/MainContent.js');

/**
 * 
 * @param {webdriver.WebDriver} webDriver
 * @constructor
 */
class AdminBasePage extends BasePage {
    /**
     *
     * @param {webdriver.WebDriver} webDriver
     */
    constructor(webDriver) {
        super(webDriver);

        /** @type {LeftNavigation} */
        this._leftNavigation = new LeftNavigation(webDriver);

        /** @type {TopMenu} */
        this._topMenu = new TopMenu(webDriver);

        /** @type {MainContent} */
        this._mainContent = new MainContent(webDriver);
    }

    /**
     *
     * @returns {TopMenu}
     */
    get topMenu() {
        return this._topMenu;
    };

    /**
     *
     * @returns {LeftNavigation}
     */
    get leftNavigation() {
        return this._leftNavigation;
    };

    /**
     *
     * @returns {MainContent}
     */
    get mainContent() {
        return this._mainContent;
    };

}

/** @type {AdminBasePage} */
module.exports = AdminBasePage;
