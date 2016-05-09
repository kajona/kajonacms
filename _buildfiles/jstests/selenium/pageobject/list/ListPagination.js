"use strict";

/**
 * require statements
 */
var BasePage = require('../base/BasePage.js');
var Constants = require('../Constants.js');


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
        return this._elementList.findElement(By.xpath(Constants.LISTPAGINATION_XPATH_FIRSTPAGINATIONELEMENT));
    }

    /**
     *
     * @returns {webdriver.promise.Promise<WebElement[]>}
     */
    get elementPageinationLinks() {
        return this.elementPagination.findElements(By.css(Constants.LISTPAGINATION_CSS_PAGELINKS));
    }

    /**
     *
     * @returns {WebElementPromise}
     */
    get elementPageinationTotal() {
        return this.elementPagination.findElement(By.css(Constants.LISTPAGINATION_CSS_TOTALCOUNT));
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
