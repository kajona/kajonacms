"use strict";

/**
 * require statements
 */
var SeleniumWaitHelper = require('../util/SeleniumWaitHelper.js');
var SeleniumUtil = require('../util/SeleniumUtil.js');
var BasePage = require('./base/BasePage.js');
var AdminLandingPage = require('./AdminLandingPage.js');
var Constants = require('./Constants');

/**
 *
 */
class LoginPage extends BasePage {

    constructor() {
        super();
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_userName() {
        return this.webDriver.findElement(By.xpath(Constants.LOGINPAGE_XPATH_INPUT_USERNAME));
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_password() {
        return this.webDriver.findElement(By.xpath(Constants.LOGINPAGE_XPATH_INPUT_PASSWORD));
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_loginBtn() {
        return this.webDriver.findElement(By.xpath(Constants.LOGINPAGE_XPATH_LOGINBUTTON));
    }

    /**
     *
     * @returns {Promise<LoginPage>}
     */
    static getPage() {
        return SeleniumUtil.gotToUrl("index.php?admin=1").then(function () {
            return new LoginPage();
        });
    }

    /**
     * Logins the user.
     * 
     * @param {string} strUserName
     * @param {string} strPassword
     * @returns {Promise<AdminLandingPage>}
     */
    login(strUserName, strPassword) {

        this.element_userName.sendKeys(strUserName);
        this.element_password.sendKeys(strPassword);
        return this.element_loginBtn.click()
            .then(function () {
                return new AdminLandingPage();
            });
    };
}

/** @type {LoginPage} */
module.exports = LoginPage;
