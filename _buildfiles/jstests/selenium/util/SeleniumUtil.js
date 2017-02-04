"use strict";

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

        var SeleniumUtil = this;
        var Constants = requireHelper('/pageobject/Constants');
        var LoginPage = requireHelper('/pageobject/LoginPage.js');
        var AdminLandingPage = requireHelper('/pageobject/AdminLandingPage.js');

        //check if user is not logged in -> if yes log in
        return LoginPage.getPage().then(function (loginPage) {
            return SeleniumUtil.getWebDriver().wait(protractor.until.elementLocated(By.xpath(Constants.LOGINPAGE_XPATH_CONTAINER))).then(function(bitLoginContainerIsPresent) {

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
     *
     */
    static switchToModalDialog() {
        browser.driver.wait(protractor.until.elementLocated(By.id('folderviewDialog_iframe')), 5000);
        browser.driver.switchTo().frame(browser.driver.findElement(by.id('folderviewDialog_iframe')));
    }
}

module.exports = SeleniumUtil;


