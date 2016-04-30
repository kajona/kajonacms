"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/BasePage.js');
var SeleniumUtil = require('../util/SeleniumUtil');

/**
 *
 */
class LoginPage extends BasePage {

    constructor() {
        super();

        this._LOGIN_CONTAINER = "//*[@id='loginContainer_content']";
        this._LOGIN_INPUT_USERNAME = this._LOGIN_CONTAINER + "//*[@id='name']";
        this._LOGIN_INPUT_PASSWORD = this._LOGIN_CONTAINER + "//*[@id='passwort']";
        this._LOGIN_BUTTON = this._LOGIN_CONTAINER + "/form[1]/div[last()]/div/button";
        this._LOGIN_ERROR_BOX = this._LOGIN_CONTAINER + "/div[@id='loginError']";

        /** @type {!webdriver.WebElement} */
        this._element_userName = this.webDriver.findElement(By.xpath(this._LOGIN_INPUT_USERNAME));

        /** @type {!webdriver.WebElement} */
        this._element_password = this.webDriver.findElement(By.xpath(this._LOGIN_INPUT_PASSWORD));

        /** @type {!webdriver.WebElement} */
        this._element_loginBtn = this.webDriver.findElement(By.xpath(this._LOGIN_BUTTON));
        // this.element_loginErrorBox = this.webDriver.findElement(by.xpath(LoginPage.LOGIN_ERROR_BOX));
    }

    /**
     *
     * @returns {Promise<LoginPage>}
     */
    static getPage() {
        return SeleniumUtil.gotToUrl("index.php?admin=1").then(function(){
            return new LoginPage();
        });
    }


    /**
     * Logins the user.
     * 
     * 
     * @param {string} strUserName
     * @param {string} strPassword
     * @returns {Promise<LoginPage>}
     */
    login(strUserName, strPassword) {

        this._element_userName.sendKeys(strUserName);
        this._element_password.sendKeys(strPassword);
        return this._element_loginBtn.click().then(function(){
            return new AdminLandingPage();
        });
    };
}

/** @type {LoginPage} */
module.exports = LoginPage;

//Require here ncause of require cycles
var AdminLandingPage = require('../pageobject/AdminLandingPage.js');
