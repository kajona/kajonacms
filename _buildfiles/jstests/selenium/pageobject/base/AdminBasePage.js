"use strict";

/**
 * require statements
 */
var BasePage = require('./BasePage.js');
var LeftNavigation = require('../LeftNavigation.js');
var MainContent = require('../MainContent.js');
var TopMenu = require('../TopMenu.js');
var ContentTopBar = require('../ContentTopBar.js');
var PathNavi = require('../PathNavi.js');

/**
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

        /** @type {PathNavi} */
        this._pathNavi = new PathNavi();

        /** @type {ContentTopBar} */
        this._contentTopBar = new ContentTopBar();


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

    /**
     *
     * @returns {PathNavi}
     */
    get pathNavi() {
        return this._pathNavi;
    };

    /**
     *
     * @returns {ContentTopBar}
     */
    get contentTopBar() {
        return this._contentTopBar;
    };
    
    
}

/** @type {AdminBasePage} */
module.exports = AdminBasePage;
