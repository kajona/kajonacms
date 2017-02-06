"use strict";

const LOGINCONTAINER = By.id("loginContainer");
const FOLDERVIEW_IFRAME = By.id('folderviewDialog_iframe');

/**
 * 
 */
class SeleniumUtil {

    /**
     *
     * Moves the mouse to the given element
     *
     * @param {webdriver.WebElement} element - The Element to which should be moved to
     *
     * @returns {webdriver.promise.Promise<void>}
     */
    static moveToElement(element) {
        return SeleniumUtil.getWebDriver().actions().mouseMove(element).perform();
    };

    /**
     * Gets the Base url
     *
     * @returns {string}
     */
    static getBaseUrl() {
        return browser.baseUrl;
    };

    /**
     *
     * @param strUrl
     * @returns {webdriver.promise.Promise<void>}
     */
    static gotToUrl(strUrl) {
        return SeleniumUtil.getWebDriver().get(SeleniumUtil.getBaseUrl()+"/"+strUrl);
    };

    /**
     * Gets the current webdriver instance
     *
     * @returns {webdriver.WebDriver}
     */
    static getWebDriver() {
        return browser.driver;
    };

    /**
     * If user is logged in, this method logs out the user
     * If user is not logged in, this method logs in the user
     *
     * @param strUserName
     * @param strPassword
     *
     * @returns {webdriver.promise.Promise<void>}
     */
    static loginOrLogout(strUserName, strPassword) {

        const SeleniumUtil = this;
        const LoginPage = requireHelper('/pageobject/LoginPage.js');
        const AdminLandingPage = requireHelper('/pageobject/AdminLandingPage.js');

        //check if user is not logged in -> if yes log in
        return LoginPage.getPage().then(function (loginPage) {
            return SeleniumUtil.getWebDriver().wait(protractor.until.elementLocated(LOGINCONTAINER)).then(function(bitLoginContainerIsPresent) {

                //if login containe ris present => login
                if(bitLoginContainerIsPresent) {
                    return loginPage.login(strUserName, strPassword);
                }

                //else logout user
                let page = AdminLandingPage.getPage();
                return page.then(function (adminlandingPage) {
                    return adminlandingPage.topMenu.logout();
                });
            });
        });
    };

    /**
     * Switch to the modal dialog
     */
    static switchToModalDialog() {
        browser.driver.wait(protractor.until.elementLocated(FOLDERVIEW_IFRAME), 5000);
        browser.driver.switchTo().frame(browser.driver.findElement(FOLDERVIEW_IFRAME));
    }
}

module.exports = SeleniumUtil;


