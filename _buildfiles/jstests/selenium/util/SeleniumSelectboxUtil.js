"use strict";

/**
 * 
 */
class SeleniumSelectboxUtil {


    /**
     *
     * Checks if the given value is selected in the select box
     *
     * @param {webdriver.WebElement} elementSelectBox - The selectbox element
     * @param {string} strValue - The checkbox element
     *
     * @returns {webdriver.promise.Promise<boolean>}
     */
    static isEmpty(elementSelectBox) {

    }

    /**
     *
     * Checks if the given value is selected in the select box
     *
     * @param {webdriver.WebElement} elementSelectBox - The selectbox element
     * @param {string} strValue - The checkbox element
     *
     * @returns {webdriver.promise.Promise<boolean>}
     */
    static isValueSelected(elementSelectBox, strValue) {
        return elementSelectBox.getAttribute("value").then(function(value) {
            return strValue == value;
        })
    }

    /**
     * Selects a value in the select box
     *
     * @param {webdriver.WebElement} elementSelectBox - The selectbox element
     * @param {string} strValue - The checkbox element
     *
     * @returns {Promise<void>|Promise<null>}
     */
    static selectByValue(elementSelectBox, strValue) {
        let strCss = "option[value='"+strValue+"']";//e.g. option[value='5']
        return elementSelectBox.findElement(By.css(strCss)).click();
    }

    /**
     * Selects an option in the select box by the given options element
     *
     * @param {webdriver.WebElement} elementSelectBox - The selectbox element
     * @param {webdriver.WebElement}  elementOption - The checkbox element
     *
     * @returns {Promise<void>|Promise<null>}
     */
    static selectByElementOption(elementSelectBox, elementOption) {
        return elementOption.click();
    }
}

module.exports = SeleniumSelectboxUtil;


