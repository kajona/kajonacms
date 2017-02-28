"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
const LoginPage = requireHelper('/pageobject/LoginPage.js');

describe('login', function () {

    it('test login', function () {
        LoginPage.getPage()
            .then(function (loginPage) {
                return loginPage.login("test", "test123");
            });
    });
});
