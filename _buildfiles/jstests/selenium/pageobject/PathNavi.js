"use strict";

/**
 * require statements
 */
var SeleniumUtil = require('../util/SeleniumUtil.js');

/**
 *
 */
class PathNavi {

    /**
     *
     * @param {WebElementPromise} elemMainContent
     */
    constructor(elemMainContent) {

        this._PATHCONTAINER = "div.pathNaviContainer";
        this._BREADCRUMP = "ul.breadcrumb";

        /** @type {WebElementPromise} */
        this._elemPathNavi = elemMainContent.findElement(By.css(this._PATHCONTAINER));

        /** @type {WebElementPromise} */
        this._element_breadCrumb = this._elemPathNavi.findElement(By.css(this._BREADCRUMP));

        this._initElements();
        this._initObjects();
    }

    _initElements() {
    }

    _initObjects() {
    }
}

/** @type {PathNavi} */
module.exports = PathNavi;
