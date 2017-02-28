"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('installation', function() {

    it('test installation', function() {
        let intTimeout = 60000 * 10;

        // wait max 5 minutes for the installation
        browser.manage().timeouts().pageLoadTimeout(intTimeout);

        SeleniumUtil.gotToUrl('installer.php');

        let webDriver = SeleniumUtil.getWebDriver();

        webDriver.findElement(By.css('.btn-primary')).click();

        // db settings
        webDriver.findElement(By.id('hostname')).sendKeys('localhost');
        webDriver.findElement(By.id('username')).sendKeys('kajona');
        webDriver.findElement(By.id('password')).sendKeys('kajona');
        webDriver.findElement(By.id('dbname')).sendKeys('autotest');
        // default is "kajona_"
        //webDriver.findElement(by.id('dbprefix')).sendKeys('');
        webDriver.findElement(By.css('option[value="sqlite3"]')).click();

        webDriver.findElement(By.css('.savechanges')).click();

        // create new admin user
        webDriver.findElement(By.id('username')).sendKeys('test');
        webDriver.findElement(By.id('password')).sendKeys('test123');
        webDriver.findElement(By.id('email')).sendKeys('test@test.com');

        webDriver.findElement(By.css('.savechanges')).click();

        // start the installation this takes some time
        webDriver.findElement(By.css('.savechanges')).click();

        // wait for the installation
        webDriver.wait(function() {
            return webDriver.getCurrentUrl().then(function(url) {
                return /finish/.test(url);
            });
        }, intTimeout);

        // now we must have a success message
        expect(webDriver.findElement(By.css('.alert-success')).getText()).toMatch('Herzlichen Glückwunsch!');
    });
});
