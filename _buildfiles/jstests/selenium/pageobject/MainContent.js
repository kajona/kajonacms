"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/base/BasePage.js');
var SeleniumUtil = require('../util/SeleniumUtil.js');
var SeleniumWaitHelper = require('../util/SeleniumWaitHelper.js');
var ContentTopBar = require('../pageobject/ContentTopBar.js');
var PathNavi = require('../pageobject/PathNavi.js');

/**
 *
 */
class MainContent extends BasePage {

    constructor() {
        super();

        this._MAINCONTENT = "div#content";

        this._initElements();
        this._initObjects();
    }

    _initElements() {
        /** @type {WebElementPromise} */
        this._element_mainContent = this.webDriver.findElement(By.css(this._MAINCONTENT));
    }

    _initObjects() {
        this._pathNavi = new PathNavi(this._element_mainContent);
        this._contentTopBar = new ContentTopBar(this._element_mainContent);
    }


    /**
     *
     * @returns {PathNavi}
     */
    get pathNavi() {
        return this._pathNavi;
    }

    /**
     *
     * @returns {ContentTopBar}
     */
    get contentTopBar() {
        return this._contentTopBar;
    }


    /**
     * Gets the title of the main content
     *
     * @returns {webdriver.promise.Promise<string>}
     */
    getMainContentTitle() {
        return this.contentTopBar.getTitle();

    }

    /**
     *
     * @returns {WebElementPromise}
     */
    get element_mainContent() {
        return this._element_mainContent;
    }
}


/** @type {MainContent} */
module.exports = MainContent;
