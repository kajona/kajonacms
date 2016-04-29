"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/BasePage.js');

/**
 *
 */
class  MainContent extends BasePage {
    /**
     *
     * @param {webdriver.WebDriver} webDriver
     */
    constructor(webDriver) {
        super(webDriver);

        this._MAINCONTENT = ".//*[@id='content']";
        this._BREADCRUMP = this._MAINCONTENT + "/div[1]/div/ul";


        /** @type {!webdriver.WebElement} */
        this.element_mainContent = this.webDriver.findElement(By.xpath(this._MAINCONTENT));

        /** @type {!webdriver.WebElement} */
        this.element_breadCrumb = this.webDriver.findElement(By.xpath(this._BREADCRUMP));


    }
}


/** @type {MainContent} */
module.exports = MainContent;
