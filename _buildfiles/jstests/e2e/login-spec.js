"use strict";

var LoginPage = requireHelper('/pageobject/LoginPage.js');

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
                expect(landingPage.contentTopBar.getTitle()).toEqual("Übersicht");
            });
    });
});
