"use strict";

/**
 * require statements
 */
var BasePage = require('../page_object/BasePage.js');

/**
 *
 */
class LoginPage extends BasePage {

    /**
     *
     * @param {webdriver.WebDriver} webDriver
     */
    constructor(webDriver) {
        super(webDriver);

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
     * Logins the user.
     * 
     * 
     * @param {string} strUserName
     * @param {string} strPassword
     * @returns {Promise<LoginPage>}
     */
    login(strUserName, strPassword) {
        var context = this;

        this._element_userName.sendKeys(strUserName);
        this._element_password.sendKeys(strPassword);
        return this._element_loginBtn.click().then(function(){
            return new AdminLandingPage(context.webDriver);
        });
    };
}

/** @type {LoginPage} */
module.exports = LoginPage;

//Require here ncause of require cycles
var AdminLandingPage = require('../page_object/AdminLandingPage.js');
