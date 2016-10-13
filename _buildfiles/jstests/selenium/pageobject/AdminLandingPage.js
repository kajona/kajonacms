"use strict";

/**
 * require statements
 */
var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
var AdminBasePage = requireHelper('/pageobject/base/AdminBasePage.js');

/**
 * 
 */
class AdminLandingPage extends AdminBasePage {

    constructor() {
        super();
    }

    /**
     *
     * @returns {Promise<AdminLandingPage>}
     */
    static getPage() {
        return SeleniumUtil.gotToUrl("index.php?admin=1").then(function () {
            return new AdminLandingPage();
        });
    }
}

/** @type {AdminLandingPage} */
module.exports = AdminLandingPage;
