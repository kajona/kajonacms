"use strict";

/**
 * require statements
 */
var BasePage = requireHelper('/pageobject/base/BasePage.js');
var Constants = requireHelper('/pageobject/Constants.js');

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

    createObject() {
        return this.elementBatchActionRow.findElement(By.css("td.actions a")).click();
    }
}

/** @type {ListBatchActionRow} */
module.exports = ListBatchActionRow;
