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
    }

    /**
     *
     * @param strUrl
     * @returns {webdriver.promise.Promise<void>}
     */
    static gotToUrl(strUrl) {
        return SeleniumUtil.getWebDriver().get(SeleniumUtil.getBaseUrl()+"/"+strUrl);
    }

    /**
     * Gets the current webdriver instance
     *
     * @returns {webdriver.WebDriver}
     */
    static getWebDriver() {
        return browser.driver;
    }
}

module.exports = SeleniumUtil;


