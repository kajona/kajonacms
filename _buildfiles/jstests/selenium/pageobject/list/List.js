"use strict";

/**
 * require statements
 */
const MainContent = requireHelper('/pageobject/MainContent.js');
const ListRow = requireHelper('/pageobject/list/ListRow.js');
const ListBatchActionRow = requireHelper('/pageobject/list/ListBatchActionRow.js');
const ListPagination = requireHelper('/pageobject/list/ListPagination.js');


/** Constants */
const LIST = by.css(".table.admintable");
const LIST_ROWS = by.css("tbody > tr:not([data-systemid='batchActionSwitch'])");


/**
 *
 */
class List extends MainContent {

    constructor() {
        super();

        /** @type {webdriver.promise.Promise<ListRow[]>} */
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
        return this.webDriver.findElement(LIST);
    }

    /**
     *
     * @returns {webdriver.promise.Promise<WebElement[]>}
     */
    get elementsListRows () {
        return this.elementList.findElements(LIST_ROWS);
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
     * @returns {webdriver.promise.Promise<ListRow[]>}
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
     * @returns {webdriver.promise.Promise<ListRow[]>}
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
