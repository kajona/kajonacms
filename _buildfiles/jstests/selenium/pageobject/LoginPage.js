"use strict";

/**
 * require statements
 */
const SeleniumWaitHelper = requireHelper('/util/SeleniumWaitHelper.js');
const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
const BasePage = requireHelper('/pageobject/base/BasePage.js');
const AdminLandingPage = requireHelper('/pageobject/AdminLandingPage.js');


/** Constants */
const USERNAME = by.id("name");
const PASSWORD = by.id("passwort");
const LOGINBUTTON = by.css("button");

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
        return this.webDriver.findElement(USERNAME);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_password() {
        return this.webDriver.findElement(PASSWORD);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_loginBtn() {
        return this.webDriver.findElement(LOGINBUTTON);
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
