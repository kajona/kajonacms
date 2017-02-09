"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');
const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
const SeleniumWaitHelper = requireHelper('/util/SeleniumWaitHelper.js');


/** Constants */
const SEARCHBOX_INPUT = by.xpath("//*[@id='globalSearchInput']");
const USERMENU = By.xpath("//*[@class='dropdown userNotificationsDropdown']");
const USERMENU_LOGOUT_LNK = by.xpath("//*[@class='dropdown userNotificationsDropdown']"+"/ul/li[last()]/a");

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
        return this.webDriver.findElement(SEARCHBOX_INPUT);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_lnkUserMenu() {
        return this.webDriver.findElement(USERMENU);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_lnkUserMenuLogOut() {
        return this.webDriver.findElement(USERMENU_LOGOUT_LNK);
    }


    /**
     *
     * @param {string} strSearchTerm
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    search(strSearchTerm) {
        return this.element_searchBox.sendKeys(strSearchTerm);
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
