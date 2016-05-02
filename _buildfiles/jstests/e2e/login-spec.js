"use strict";

var LoginPage = require('../selenium/pageobject/LoginPage.js');

describe('login', function () {
    beforeEach(function () {
        browser.ignoreSynchronization = true;
    });

    it('test login', function () {
        LoginPage.getPage()
            .then(function (loginPage) {
                return loginPage.login("test", "test123");
            })
            .then(function (landingPage) {
                return landingPage.mainContent.getMainContentTitle();
            })
            .then(function (strTitle) {
                expect(strTitle).toEqual("Übersicht");
            });
    });
});
