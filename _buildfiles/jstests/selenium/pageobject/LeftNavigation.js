"use strict";

/**
 * require statements
 */
var BasePage = require('../pageobject/BasePage.js');
var SeleniumWaitHelper = require('../util/SeleniumWaitHelper.js');

/**
 *
 */
class LeftNavigation extends BasePage {

    constructor() {
        super();

        this._NAVIGATION = ".//*[@id='moduleNavigation']";
        this._NAVIGATION_HAMBURGER = ".//*[@data-toggle='offcanvas']";//visible when page width < 932px

        /** @type {!webdriver.WebElement} */
        this.element_navigation = this.webDriver.findElement(By.xpath(this._NAVIGATION));

        /** @type {!webdriver.WebElement} */
        this.element_navigationHamburger = this.webDriver.findElement(By.xpath(this._NAVIGATION_HAMBURGER));
    }


    /**
     * Opens the navigation
     *
     * @returns {*}
     */
    showNavigation() {
        var context = this;

        return this.isNavigationDisplayed().then(function(isMenuDisplayed) {
            if(!isMenuDisplayed) {
                context.isNavigationHamburgerDisplayed().then(function(isHamburgerMenuVisible) {
                    if(isHamburgerMenuVisible) {
                        context.element_navigationHamburger.click();
                    }
                });
            }
        });
    }

    /**
     * Checks if the hamburger element to open/close the navigation is present
     *
     * @returns {Promise<R>}
     */
    isNavigationHamburgerDisplayed() {
        return this.element_navigationHamburger.isDisplayed().then(function (isHamburgerVisible) {
            return isHamburgerVisible;
        });
    }

    /**
     * Checks if the navigation is displayed
     *
     * @returns {Promise<boolean>}
     */
    isNavigationDisplayed() {
        var strPath = this._NAVIGATION + "/../../..";

        //if element has class active -> menu is displayed
        return this.webDriver.findElement(By.xpath(strPath)).getAttribute("class").then(function (strValue) {
            return strValue.indexOf("active") !== -1
        });
    }


    /**
     * Gets a module from the navigation with the given name
     *
     * @param {string} strMenuName
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    getNavigationModule(strMenuName) {
        var strPathMenu = this._NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]";
        return SeleniumWaitHelper.getElementWhenDisplayed(this.webDriver, By.xpath(strPathMenu)).then(function (menuElement) {
          return menuElement;
        });
    }

    /**
     * Checks if the module in the navigation is already opened
     *
     * @param {string} strMenuName
     * @returns {*}
     */
    isNavigationModuleOpened(strMenuName) {
        return this.getNavigationModule(strMenuName).then(function (menuElement) {

            return menuElement.getAttribute("class").then(function (strValueclass) {
                if (strValueclass === null || strValueclass.indexOf("collapsed") > -1) {
                    return false;
                }
                else if (strValueclass === "") {
                    return menuElement.getAttribute("aria-expanded").then(function (strValueAria) {
                        return (strValueAria === "true");
                    });
                }

                return true;
            });
        });
    }

    /**
     * Opens a module in the navigation
     *
     * @param {string} strMenuName
     * @returns {*}
     */
    openNavigationModule(strMenuName) {
        var context = this;

        return this.showNavigation().then(function () {

            context.isNavigationModuleOpened(strMenuName).then(function(isModuleMenuOpened) {
                if(!isModuleMenuOpened) {
                    context.getNavigationModule(strMenuName).then(function (menuElement) {
                        menuElement.click();
                    });
                }
            });

        });
    };


    /**
     * Gets a links for a navigation module
     *
     * @param {string} strMenuName
     * @returns {*}
     */
    getNavigationModuleLinks(strMenuName) {
        var context = this;

        return this.openNavigationModule(strMenuName)
            .then(function () {
                var strPathToLinks = context._NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]/../..//li[a[contains(concat(' ', @class, ' '), ' adminnavi ')]]";
                return SeleniumWaitHelper.getElementsWhenPresent(context.webDriver, By.xpath(strPathToLinks));
            });
    };


    /**
     * Gets a single link from a navigation module
     * @param {string} strMenuName
     * @param {integer} intLinkPosition
     * @returns {*}
     */
    getModuleMenuLink(strMenuName, intLinkPosition) {
        var context = this;

        return this.openNavigationModule(strMenuName).then(function () {
            var strPathToLinks = context._NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]/../..//li[a[contains(concat(' ', @class, ' '), ' adminnavi ')]][" + intLinkPosition + "]";
            return SeleniumWaitHelper.getElementWhenDisplayed(context.webDriver, By.xpath(strPathToLinks));
        });
    }

}


/** @type {LeftNavigation} */
module.exports = LeftNavigation;
