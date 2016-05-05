"use strict";

/**
 * require statements
 */
var BasePage = require('../base/BasePage.js');
var LeftNavigation = require('../../pageobject/LeftNavigation.js');
var MainContent = require('../../pageobject/MainContent.js');
var TopMenu = require('../../pageobject/TopMenu.js');

/**
 * 
 *
 * @constructor
 */
class AdminBasePage extends BasePage {

    /**
     *
     * @param {MainContent} mainContentPage
     */
    constructor(mainContentPage) {
        super();

        /** @type {LeftNavigation} */
        this._leftNavigation = new LeftNavigation();

        /** @type {TopMenu} */
        this._topMenu = new TopMenu();


        if(!mainContentPage) {
            mainContentPage = new MainContent()
        }
        /** @type {MainContent} */
        this._mainContent = mainContentPage
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
