"use strict";

/**
 * require statements
 */
var BasePage = require('../page_object/BasePage.js');
var LeftNavigation = require('../page_object/LeftNavigation.js');
var TopMenu = require('../page_object/TopMenu.js');
var MainContent = require('../page_object/MainContent.js');

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
