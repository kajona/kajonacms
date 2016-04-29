"use strict";


/**
 *
 */
class SeleniumWaitHelper {

    /**
     * Returns an elements when it is displayed.
     * This is a blocking wait.
     *
     * @param {webdriver.WebDriver} webDriver
     * @param locator
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    static getElementWhenDisplayed(webDriver, locator) {
        webDriver.wait(
            protractor.until.elementIsVisible(webDriver.findElement(locator), 10000)
        );

        return webDriver.findElement(locator);
    }

    /**
     *
     * @param {webdriver.WebDriver} webDriver
     * @param locator
     * @returns {webdriver.promise.Promise<WebElement[]>|!webdriver.promise.Promise}
     */
    static getElementsWhenPresent(webDriver, locator) {
        webDriver.wait(
            protractor.until.elementsLocated(locator, 10000)
        );

        return webDriver.findElements(locator);
    }

    /**
     * Returns an elements when it is present in the DOM.
     * This is a blocking wait.
     *
     * @param webDriver
     * @param locator
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    static getElementWhenPresent(webDriver, locator) {
        webDriver.wait(
            protractor.until.elementLocated(locator, 10000)
        );

        return webDriver.findElement(locator);
    }
}

module.exports = SeleniumWaitHelper;
