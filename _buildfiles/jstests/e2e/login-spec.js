"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
const LoginPage = requireHelper('/pageobject/LoginPage.js');

describe('login', function () {

    it('test setupSeleniumConfig.php', function() {
        // this is required so that our installation sets all needed settings i.e. turn nice urls / ssl off etc.
        SeleniumUtil.gotToUrl("setupSeleniumConfig.php");
    });

    it('test login', function () {
        LoginPage.getPage()
            .then(function (loginPage) {
                return loginPage.login("test", "test123");
            });
    });
});
