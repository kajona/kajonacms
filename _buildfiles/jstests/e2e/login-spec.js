"use strict";

var LoginPage = require('../selenium/pageobject/LoginPage.js');

describe('login', function () {
    beforeEach(function () {
        browser.ignoreSynchronization = true;
    });

    it('test login', function () {
        var loginPage = LoginPage.getPage();
        loginPage.login("test", "test123").then(function(landingPage){
            
            landingPage.mainContent.getMainContentTitle().then(function(strTitle) {
                // check whether login was successful
                expect(strTitle).toEqual("Übersicht");
            });
        });
    });

});
