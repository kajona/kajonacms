"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/BasePage.js');
var LeftNavigation = require('../pageobject/LeftNavigation.js');
var MainContent = require('../pageobject/MainContent.js');

/**
 * 
 * @param {webdriver.WebDriver} webDriver
 * @constructor
 */
class AdminBasePage extends BasePage {

    constructor() {
        super();

        /** @type {LeftNavigation} */
        this._leftNavigation = new LeftNavigation();

        /** @type {TopMenu} */
        this._topMenu = new TopMenu();

        /** @type {MainContent} */
        this._mainContent = new MainContent();
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

var TopMenu = require('../pageobject/TopMenu.js');//put here due to cycle!!
