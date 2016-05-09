"use strict";

/**
 * require statements
 */
var BasePage = require('../base/BasePage.js');
var Constants = require('../Constants.js');


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
