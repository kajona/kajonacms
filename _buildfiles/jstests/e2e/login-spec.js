"use strict";

var LoginPage = require('../page_object/LoginPage.js');

describe('login', function () {
    beforeEach(function () {
        browser.ignoreSynchronization = true;
    });

    it('test login', function () {
        browser.get('index.php?admin=1');

        var loginPage = new LoginPage(browser.driver);
        loginPage.login("artemeonadmin", "admin0815");

        // check whether login was successful
        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");
    });

});
