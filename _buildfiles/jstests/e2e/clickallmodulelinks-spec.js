"use strict";

const LoginPage = require('../selenium/pageobject/LoginPage.js');
const AdminLandingPage = require('../selenium/pageobject/AdminLandingPage.js');

describe('clickallmodulelinks', function () {

    it('test clickallmodulelinks', function () {
        const loginPage = LoginPage.getPage();
        const strMenuName = "CIM";

        loginPage
            .then(function (p) {
                return p.login("admin", "admin");
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


    });

});
