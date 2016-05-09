"use strict";

/**
 * require statements
 */
var SeleniumUtil = require('../util/SeleniumUtil.js');
var MainContent = require('../pageobject/MainContent.js');

/**
 *
 */
class ContentTopBar {

    /**
     *
     * @param {WebElementPromise} elemMainContent
     */
    constructor(elemMainContent) {

        this._CSS_CONTENTTOPBAR = "div.contentTopbar";
        this._ID_TITLE = "moduleTitle";

        /** @type {WebElementPromise} */
        this._elemContentTopBar = elemMainContent.findElement(By.css(this._CSS_CONTENTTOPBAR));
    }

    /**
     *
     * @returns {webdriver.promise.Promise<string>|*}
     */
    getTitle() {
        return this._elemContentTopBar.findElement(By.id(this._ID_TITLE)).getText();
    }
}

/** @type {ContentTopBar} */
module.exports = ContentTopBar;
