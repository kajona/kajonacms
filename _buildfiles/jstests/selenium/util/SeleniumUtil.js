"use strict";

/**
 * 
 */
class SeleniumUtil {

    /**
     *
     * Moves the mouse to the given element
     *
     * @param {webdriver.WebDriver} webDriver
     * @param {webdriver.WebElement} element - The Element to which should be moved to
     *
     * @returns {webdriver.promise.Promise<void>}
     */
    static moveToElement(webDriver, element) {
        return webDriver.actions().mouseMove(element).perform();
    };

    /**
     * Gets the Base url
     *
     * @returns {string}
     */
    static getBaseUrl() {
        return browser.baseUrl;
    }
}

module.exports = SeleniumUtil;
