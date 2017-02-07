"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');
const SeleniumWaitHelper = requireHelper('/util/SeleniumWaitHelper.js');

/** Constants */
const LEFTNAVIGATION_XPATH_NAVIGATION = ".//*[@id='moduleNavigation']";

const NAVIGATION = by.xpath(LEFTNAVIGATION_XPATH_NAVIGATION);
const NAVIGATION_HAMBURGER = by.xpath(".//*[@data-toggle='offcanvas']");//visible when page width < 932px


/**
 *
 */
class LeftNavigation extends BasePage {

    constructor() {
        super();
    }


    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_navigation() {
        return this.webDriver.findElement(NAVIGATION);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get element_navigationHamburger() {
        return this.webDriver.findElement(NAVIGATION_HAMBURGER);
    }

    /**
     * Opens the navigation
     *
     * @returns {*}
     */
    showNavigation() {
        var context = this;

        return this.isNavigationDisplayed().then(function(isMenuDisplayed) {

            //if menu is not displayed, check if there is a hamburger element and click it
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
     * @returns {webdriver.promise.Promise<boolean>}
     */
    isNavigationHamburgerDisplayed() {
        return this.element_navigationHamburger.isDisplayed();
    }

    /**
     * Checks if the navigation is displayed
     *
     * @returns {Promise<boolean>}
     */
    isNavigationDisplayed() {
        var strPath = LEFTNAVIGATION_XPATH_NAVIGATION + "/../../..";

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
        var strPathMenu = LEFTNAVIGATION_XPATH_NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]";
        return SeleniumWaitHelper.getElementWhenDisplayed(this.webDriver, By.xpath(strPathMenu));
    }

    /**
     * Checks if the module in the navigation is already opened
     *
     * @param {string} strMenuName
     * @returns {Promise<boolean>}
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
                var strPathToLinks = LEFTNAVIGATION_XPATH_NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]/../..//li[a[contains(concat(' ', @class, ' '), ' adminnavi ')]]";
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
            var strPathToLinks = LEFTNAVIGATION_XPATH_NAVIGATION + "//*[contains(concat(' ', @class, ' '), ' panel-heading ')]/a[contains(text(), '" + strMenuName + "')]/../..//li[a[contains(concat(' ', @class, ' '), ' adminnavi ')]][" + intLinkPosition + "]";
            return SeleniumWaitHelper.getElementWhenDisplayed(context.webDriver, By.xpath(strPathToLinks));
        });
    }

}


/** @type {LeftNavigation} */
module.exports = LeftNavigation;
