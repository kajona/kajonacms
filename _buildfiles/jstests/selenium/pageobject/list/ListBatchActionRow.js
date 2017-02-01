"use strict";

/**
 * require statements
 */
let BasePage = requireHelper('/pageobject/base/BasePage.js');
let Constants = requireHelper('/pageobject/Constants.js');

/**
 *
 */
class ListBatchActionRow extends BasePage {

    /**
     * 
     * @param {WebElementPromise} elementList
     */
    constructor(elementList) {
        super();

        this._elementList = elementList;
    }

    get elementBatchActionRow() {
        return this._elementList.findElement(By.css(Constants.LIST_CSS_BATCHACTIONROW));
    }

    /**
     * Presses the (+) Button at the end of a list
     *
     * @returns {webdriver.promise.Promise<void>|!webdriver.promise.Promise.<void>}
     */
    createObject() {
        return this.elementBatchActionRow.findElement(By.css("td.actions a")).click();
    }
}

/** @type {ListBatchActionRow} */
module.exports = ListBatchActionRow;
