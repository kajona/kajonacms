"use strict";

/**
 * require statements
 */


/**
 *
 */
class ListBatchActionRow {

    /**
     * 
     * @param {WebElementPromise} elementBatchActionRow
     */
    constructor(elementBatchActionRow) {
        this._elementBatchActionRow = elementBatchActionRow;
    }

    canCreateObjects() {

    }

    createObject() {
        return this._elementBatchActionRow.then(function (elementRow) {
            elementRow.findElement(By.css("td.actions a")).click();
        });
    }
}

/** @type {ListBatchActionRow} */
module.exports = ListBatchActionRow;
