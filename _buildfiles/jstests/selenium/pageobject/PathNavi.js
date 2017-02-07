"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');



/** Constants */
const PATHCONTAINER = by.css("div.pathNaviContainer");
const BREADCRUMP = by.css("ul.breadcrumb");

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
        return this.webDriver.findElement(PATHCONTAINER);
    }

    /**
     *
     */
    get element_breadCrumb() {
        this.elemPathNavi.findElement(BREADCRUMP);
    }

}

/** @type {PathNavi} */
module.exports = PathNavi;
