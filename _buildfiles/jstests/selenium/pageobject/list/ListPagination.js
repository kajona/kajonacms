"use strict";

/**
 * require statements
 */
var SeleniumUtil = require('../../util/SeleniumUtil.js');


/**
 *
 */
class ListPagination {

    /**
     *
     * @param {WebElementPromise} elementPagination
     */
    constructor(elementPagination) {

        this._CSS_LIST_PAGINATION_PAGELINKS = "li[data-kajona-pagenum]";
        this._CSS_LIST_PAGINATION_TOTALCOUNT = "li:last-child";
        //this._CSS_ADMIN_TABLE_PAGER_PAGE_NEXT = "li:nth-last-of-type(2)";//TODO to be defined

        /** @type {WebElementPromise}*/
        this._elementPagination = elementPagination;

        /** @type {!webdriver.promise.Promise.<!Array.<!webdriver.WebElement>>}*/
        this._elementPageinationLinks = this._elementPagination.findElements(By.css(this._CSS_LIST_PAGINATION_PAGELINKS));

        /** @type {WebElementPromise}*/
        this._elementPageinationTotal = this._elementPagination.findElement(By.css(this._CSS_LIST_PAGINATION_TOTALCOUNT));
    }



    /**
     * Gets the number of entries from the pagination.
     *
     * @returns {Promise<integer>}
     */
    getIntPaginationTotalNumber() {
        return this._elementPageinationTotal
            .then(function (element) {
                return element.findElement(By.css("span"));
            })
            .then(function (elementSpan) {
                return elementSpan.getText();
            })
            .then(function (strTotalText) {
                let arrSplit = strTotalText.split(":");
                return parseInt(arrSplit[1].trim());
            });
    }
}

/** @type {ListPagination} */
module.exports = ListPagination;
