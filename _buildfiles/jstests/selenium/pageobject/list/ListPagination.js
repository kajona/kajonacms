"use strict";

/**
 * require statements
 */
const BasePage = requireHelper('/pageobject/base/BasePage.js');

/** Constants */
const PAGINATION = by.xpath("following-sibling::div[@class='pager'][1]");
const PAGELINKS = by.css("li[data-kajona-pagenum]");
const TOTALCOUNT = by.css("li:last-child");

/**
 *
 */
class ListPagination extends BasePage {

    /**
     *
     * @param {WebElementPromise} elementList
     */
    constructor(elementList) {
        super();

        this._elementList = elementList;
    }

    /**
     * Gets the first sibling of the of the list element with a class .pager
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get elementPagination() {
        return this._elementList.findElement(PAGINATION);
    }

    /**
     *
     * @returns {webdriver.promise.Promise<WebElement[]>}
     */
    get elementPageinationLinks() {
        return this.elementPagination.findElements(PAGELINKS);
    }

    /**
     *
     * @returns {WebElementPromise}
     */
    get elementPageinationTotal() {
        return this.elementPagination.findElement(TOTALCOUNT);
    }

    /**
     * Gets the number of entries from the pagination.
     *
     * @returns {Promise<integer>}
     */
    getIntPaginationTotalNumber() {
        return this.elementPageinationTotal.findElement(By.css("span")).getText()
            .then(function (strTotalText) {
                let arrSplit = strTotalText.split(":");
                return parseInt(arrSplit[1].trim());
            });
    }
}

/** @type {ListPagination} */
module.exports = ListPagination;
