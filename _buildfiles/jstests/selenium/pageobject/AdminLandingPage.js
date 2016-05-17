"use strict";

/**
 * require statements
 */
var AdminBasePage = requireHelper('/pageobject/base/AdminBasePage.js');

/**
 * 
 */
class AdminLandingPage extends AdminBasePage {

    constructor() {
        super();
    }
}

/** @type {AdminLandingPage} */
module.exports = AdminLandingPage;
