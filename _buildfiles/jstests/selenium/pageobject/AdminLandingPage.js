"use strict";

/**
 * require statements
 */
const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
const AdminBasePage = requireHelper('/pageobject/base/AdminBasePage.js');

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
