"use strict";

var LoginPage = require('../page_object/LoginPage.js');
var AdminLandingPage = require('../page_object/AdminLandingPage.js');
var SeleniumWaitHelper = require('../util/SeleniumWaitHelper.js');

describe('clickallmodulelinks', function () {
    beforeEach(function () {
        browser.ignoreSynchronization = true;
    });

    it('test clickallmodulelinks', function () {
        browser.get('index.php?admin=1');

        var loginPage = new LoginPage(browser.driver);
        var ladingPage = loginPage.login("artemeonadmin", "admin0815");
        var strMenuName = "CIM";

        ladingPage.then(function(adminLandingPage) {
            adminLandingPage.leftNavigation.getNavigationModuleLinks(strMenuName).then(function(arrElements) {
                for(let i = 1; i<= arrElements.length; i++) {
                    let adminPage = new AdminLandingPage(browser.driver);
                    adminPage.leftNavigation.getModuleMenuLink(strMenuName, i).then(function(element) {
                        element.click();
                    });
                }
            }).then(function() {
                let adminPage = new AdminLandingPage(browser.driver);
                adminPage.topMenu.logout();
            });
        });

        // adminLandingPage.leftNavigation.openNavigationModule(strMenuName);



        // check whether login was successful
        // expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");


    });

});
