"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');

/** Constants */
const CONTENTTOPBAR = by.css("div.contentTopbar");

/**
 *
 */
class ContentTopBar extends BasePage {

    /**
     *
     */
    constructor() {
        super();
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get elemContentTopBar() {
        return this.webDriver.findElement(CONTENTTOPBAR);
    }
}

/** @type {ContentTopBar} */
module.exports = ContentTopBar;
