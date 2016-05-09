"use strict";

/**
 * require statements
 */
var MainContent = require('../MainContent.js');
var ListRow = require('./ListRow.js');
var ListBatchActionRow = require('./ListBatchActionRow.js');
var ListPagination = require('./ListPagination.js');
var Constants = require('../Constants.js');

/**
 *
 */
class List extends MainContent {

    constructor() {
        super();

        /** @type {Promise<ListRow[]>} */
        this._arrListRows = this._createListRows();

        /** @type {ListBatchActionRow} */
        this._listBatchActionRow = new ListBatchActionRow(this.elementList);

        /** @type {ListPagination} */
        this._listPagination = new ListPagination(this.elementList);
    }

    /**
     *
     * @returns {WebElementPromise|!webdriver.WebElement}
     */
    get elementList () {
        return this.webDriver.findElement(By.css(Constants.LIST_CSS_ROOT));
    }

    /**
     *
     * @returns {webdriver.promise.Promise<WebElement[]>}
     */
    get elementsListRows () {
        return this.elementList.findElements(By.css(Constants.LIST_CSS_ROWS));
    }

    /**
     *
     * @returns {ListPagination}
     */
    getPagination() {
        return this._listPagination;
    }


    /**
     *
     * @returns {ListRow[]}
     */
    getArrListRows() {
        return this._arrListRows;
    }


    /**
     *
     * @returns {ListBatchActionRow}
     */
    getBatchActionRow() {
        return this._listBatchActionRow;
    }

    /**
     *
     * @returns {Promise<ListRow[]>}
     */
    _createListRows() {
        return this.elementsListRows.then(function(arrElemRows) {
            let arrListRows = [];
            for(let i = 0; i<arrElemRows.length; i++) {
                arrListRows.push(new ListRow(arrElemRows[i]));
            }
            return arrListRows;
        });
    }
}

/** @type {List} */
module.exports = List;
