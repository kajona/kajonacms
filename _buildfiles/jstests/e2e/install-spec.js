
var fs = require('fs');

describe('installation', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test installation', function() {
        // wait max 5 minutes for the installation
        browser.manage().timeouts().pageLoadTimeout(60000 * 5);

        browser.get('http://127.0.0.1:8080/installer.php');

        browser.driver.findElement(by.css('.btn-primary')).click();

        browser.takeScreenshot().then(function(png) {
            var stream = fs.createWriteStream("installer.png");
            stream.write(new Buffer(png, 'base64'));
            stream.end();
        });

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
        browser.driver.findElement(by.linkText('Automatische Installation')).click();

        // this is required so that our installation sets all needed settings i.e. turn nice urls / ssl off etc.
        browser.get('http://127.0.0.1:8080/setupSeleniumConfig.php');
    });

});
