"use strict";

var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('installation', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test installation', function() {
        // wait max 5 minutes for the installation
        browser.manage().timeouts().pageLoadTimeout(60000 * 5);

        SeleniumUtil.gotToUrl('installer.php');

        browser.driver.findElement(by.css('.btn-primary')).click();

        // db settings
        browser.driver.findElement(by.id('hostname')).sendKeys('localhost');
        browser.driver.findElement(by.id('username')).sendKeys('kajona');
        browser.driver.findElement(by.id('password')).sendKeys('kajona');
        browser.driver.findElement(by.id('dbname')).sendKeys('autotest');
        // default is "kajona_"
        //browser.driver.findElement(by.id('dbprefix')).sendKeys('');
        browser.driver.findElement(by.css('option[value="sqlite3"]')).click();

        browser.driver.findElement(by.css('.savechanges')).click();

        // create new admin user
        browser.driver.findElement(by.id('username')).sendKeys('test');
        browser.driver.findElement(by.id('password')).sendKeys('test123');
        browser.driver.findElement(by.id('email')).sendKeys('test@test.com');

        browser.driver.findElement(by.css('.savechanges')).click();

        // start the installation this takes some time
        browser.driver.findElement(by.css('.savechanges')).click();

        // wait for the installation
        browser.driver.wait(function() {
            return browser.driver.getCurrentUrl().then(function(url) {
                return /finish/.test(url);
            });
        }, 60000 * 5);

        // now we must have a success message
        expect(browser.driver.findElement(by.css('.alert-success')).getText()).toMatch('Herzlichen Glückwunsch!');

        // this is required so that our installation sets all needed settings i.e. turn nice urls / ssl off etc.
        browser.get('http://127.0.0.1:8080/setupSeleniumConfig.php');
    });

});
