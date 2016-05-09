"use strict";

/**
 * require statements
 */
var MainContent = require('../../pageobject/MainContent.js');
var ListRow = require('../../pageobject/list/ListRow');
var ListBatchActionRow = require('../../pageobject/list/ListBatchActionRow');
var ListPagination = require('../../pageobject/list/ListPagination');

/**
 *
 */
class List extends MainContent {

    constructor() {
        super();



        this._initElements();
        this._initObjects();
    }

    _initElements() {
        super._initElements();

        this._CSS_LIST_ROOT = ".table.admintable";
        this._CSS_LIST_ROWS = "tbody > tr:not([data-systemid='batchActionSwitch'])";
        this._CSS_LIST_BATCHACTIONROW ="tbody > tr[data-systemid='batchActionSwitch']";
        this._CSS_LIST_PAGINATION = ".pager";

        /** @type {WebElementPromise}*/
        this._elementList = this.element_mainContent.findElement(By.css(this._CSS_LIST_ROOT));

        /** @type {!webdriver.promise.Promise.<!Array.<!webdriver.WebElement>>}*/
        this._elementsListRows = this._elementList.findElements(By.css(this._CSS_LIST_ROWS));

        /** @type {WebElementPromise}*/
        this._elementListBatchActionRow = this._elementList.findElement(By.css(this._CSS_LIST_BATCHACTIONROW));

        /** @type {WebElementPromise}*/
        this._elementPageination = this.element_mainContent.findElement(By.css(this._CSS_LIST_PAGINATION));
    }

    _initObjects() {
        super._initObjects();

        /** @type Promise<ListRow[]>*/
        this._arrListRows = this._createListRows();

        /** @type Promise<ListBatchActionRow>*/
        this._listBatchActionRow = this._createBatchActionRow();

        /** @type Promise<ListPagination>*/
        this._listPagination = this._createPagination();
    }

    /**
     *
     * @returns {Promise<ListRow[]>}
     */
    _createListRows() {
        return this._elementsListRows.then(function(arrElemRows) {
            let arrListRows = [];
            for(let i = 0; i<arrElemRows.length; i++) {
                arrListRows.push(new ListRow(arrElemRows[i]));
            }
            return arrListRows;
        });
    }

    /**
     *
     * @returns {ListBatchActionRow}
     */
    _createBatchActionRow() {
        return new ListBatchActionRow(this._elementListBatchActionRow);
    }


    /**
     *
     * @returns {ListPagination}
     */
    _createPagination() {
        return new ListPagination(this._elementPageination);
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
}

/** @type {List} */
module.exports = List;
