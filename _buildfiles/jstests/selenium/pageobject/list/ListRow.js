"use strict";

/**
 * require statements
 */
let BasePage = requireHelper('/pageobject/base/BasePage.js');
let Constants = requireHelper('/pageobject/Constants.js');


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


    /**
     * Returns all action icons of the row
     *
     * @return {webdriver.promise.Promise<WebElement[]>}
     */
    getArrActionIcons() {
        return this._elementRow.findElements(By.css(Constants.LIST_CSS_ACTIONICON));
    }

}

/** @type {ListRow} */
module.exports = ListRow;
