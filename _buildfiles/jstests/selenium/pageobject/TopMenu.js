"use strict";

/**
 * require statements
 */
var BasePage = requireHelper('/pageobject/base/BasePage.js');
var Constants = requireHelper('/pageobject/Constants.js');
var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
var SeleniumWaitHelper = requireHelper('/util/SeleniumWaitHelper.js');

/**
 *
 */
class TopMenu extends BasePage {

    constructor() {
        super();
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_searchBox() {
        return this.webDriver.findElement(By.xpath(Constants.TOPMENU_XPATH_SEARCHBOX_INPUT));
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_lnkUserMenu() {
        return this.webDriver.findElement(By.xpath(Constants.TOPMENU_XPATH_USERMENU));
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_lnkUserMenuLogOut() {
        return this.webDriver.findElement(By.xpath(Constants.TOPMENU_XPATH_USERMENU_LOGOUT_LNK));
    }


    /**
     *
     * @param {string} strSearchTerm
     */
    search(strSearchTerm) {
        this.element_searchBox.sendKeys(strSearchTerm);
        // SeleniumWaitHelper.waitForElementUntilPresent(driver, By.xpath(Constants.TOPMENU_SEARCHBOX_LNK_SEARCHRESULTS), 10);
        // lnkShowAllSearchResults.click();
    };

    /**
     * Logs out.
     *
     * @returns {Promise<void>}
     */
    logout() {
        var context = this;

        return this.showUserMenu().then(function () {
            return context.element_lnkUserMenuLogOut.click();
        });
    }

    /**
     * Displays the user menu.
     *
     * @returns {webdriver.promise.Promise.<void>}
     */
    showUserMenu() {
        return this.element_lnkUserMenu.then(function (element) {
            return SeleniumUtil.moveToElement(element);
        });
    }
}

/** @type {TopMenu} */
module.exports = TopMenu;
