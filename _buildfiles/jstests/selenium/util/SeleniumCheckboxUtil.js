"use strict";

/**
 * 
 */
class SeleniumCheckboxUtil {

    /**
     *
     * Checks if the given checkbox is checked
     *
     * @param {webdriver.WebElement} elementChkBox - The checkbox element
     *
     * @returns {webdriver.promise.Promise<boolean>}
     */
    static isChecked(elementChkBox) {
        return elementChkBox.getAttribute("checked").then(function(value) {
            return value == "true";
        });
    }

    /**
     * Checks a checkbox
     *
     * @param elementChkBox
     * @returns {Promise<void>|Promise<null>}
     */
    static checkCheckbox(elementChkBox) {

        return SeleniumCheckboxUtil.isChecked(elementChkBox).then(function(isChecked) {
            if(isChecked) {
                return null;
            }
            return elementChkBox.click();
        });


    }

    /**
     * Unchecks a checkbox
     *
     * @param elementChkBox
     * @returns {Promise<void>|Promise<null>}
     */
    static uncheckCheckbox(elementChkBox) {
        return SeleniumCheckboxUtil.isChecked(elementChkBox).then(function(isChecked) {
            if(isChecked) {
                return elementChkBox.click();
            }
            return null;
        });
    }
}

module.exports = SeleniumCheckboxUtil;


