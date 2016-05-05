"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/base/BasePage.js');
var SeleniumWaitHelper = require('../util/SeleniumWaitHelper.js');

/**
 *
 */
class MainContent extends BasePage {

    constructor() {
        super();

        this._MAINCONTENT = ".//*[@id='content']";

        this._PATHCONTAINER = this._MAINCONTENT + "//div[contains(concat(' ', @class, ' '), ' pathNaviContainer ')]";
        this._BREADCRUMP = this._PATHCONTAINER + "//ul[contains(concat(' ', @class, ' '), ' breadcrumb ')]";

        this._CONTENTTOPBAR = this._MAINCONTENT + "//div[contains(concat(' ', @class, ' '), ' contentTopbar ')]";


        /** @type {WebElementPromise} */
        this._element_mainContent = this.webDriver.findElement(By.xpath(this._MAINCONTENT));

        /** @type {WebElementPromise} */
        this._element_pathContainer = this.webDriver.findElement(By.xpath(this._PATHCONTAINER));

        /** @type {WebElementPromise} */
        this._element_breadCrumb = this.webDriver.findElement(By.xpath(this._BREADCRUMP));

        /** @type {WebElementPromise} */
        this._element_contentTopBar = this.webDriver.findElement(By.xpath(this._CONTENTTOPBAR));


    }

    /**
     * Gets the title of the main content
     *
     * @returns {webdriver.promise.Promise<string>}
     */
    getMainContentTitle() {
        return this._element_mainContent.findElement(By.id('moduleTitle')).getText();
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
