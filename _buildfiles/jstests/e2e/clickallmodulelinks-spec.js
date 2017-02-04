"use strict";

var LoginPage = require('../selenium/pageobject/LoginPage.js');
var AdminLandingPage = require('../selenium/pageobject/AdminLandingPage.js');
var SeleniumWaitHelper = require('../selenium/util/SeleniumWaitHelper.js');

describe('clickallmodulelinks', function () {

    it('test clickallmodulelinks', function () {
        var loginPage = LoginPage.getPage();
        var strMenuName = "CIM";

        loginPage
            .then(function (p) {
                return p.login("artemeonadmin", "admin0815");
            })
            .then(function (adminLandingPage) {
                adminLandingPage.leftNavigation.getNavigationModuleLinks(strMenuName).then(function (arrElements) {
                    for (let i = 1; i <= arrElements.length; i++) {
                        let adminPage = new AdminLandingPage();
                        adminPage.leftNavigation.getModuleMenuLink(strMenuName, i).then(function (element) {
                            element.click();
                        });
                    }
                });
            })
            .then(function () {
                let adminPage = new AdminLandingPage();
                adminPage.topMenu.logout();
            });

        // adminLandingPage.leftNavigation.openNavigationModule(strMenuName);


        // check whether login was successful
        // expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");


    });

});
