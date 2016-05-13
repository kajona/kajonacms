"use strict";

/**
 * require statements
 */
var BasePage = requireHelper('/pageobject/base/BasePage.js');
var Constants = requireHelper('/pageobject/Constants.js');

/**
 *
 */
class PathNavi extends BasePage {

    /**
     *
     */
    constructor() {
        super();
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get elemPathNavi() {
        return this.webDriver.findElement(By.css(Constants.PATHNAVI_CSS_PATHCONTAINER));
    }

    /**
     *
     */
    get element_breadCrumb() {
        this.elemPathNavi.findElement(By.css(Constants.PATHNAVI_CSS_BREADCRUMP));
    }

}

/** @type {PathNavi} */
module.exports = PathNavi;
