"use strict";

/**
 * require statements
 */
var BasePage = requireHelper('/pageobject/base/BasePage.js');
var Constants = requireHelper('/pageobject/Constants.js');


/**
 *
 */
class ListRow extends BasePage {

    /**
     *
     * @param {WebElementPromise} elementRow
     */
    constructor(elementRow) {
        super();

        this._elementRow = elementRow;
    }
}

/** @type {ListRow} */
module.exports = ListRow;
