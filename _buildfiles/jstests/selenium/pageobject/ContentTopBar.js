"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');

/** Constants */
const CONTENTTOPBAR = by.css("div.contentTopbar");
const CONTENTTOPBAR_TITLE = by.id("moduleTitle");


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

    /**
     *
     * @returns {webdriver.promise.Promise<string>|*}
     */
    getTitle() {
        return this.elemContentTopBar.findElement(CONTENTTOPBAR_TITLE).getText();
    }


}

/** @type {ContentTopBar} */
module.exports = ContentTopBar;
