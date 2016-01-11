
describe('setup', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test installation', function() {
        // wait max 5 minutes for the installation
        browser.manage().timeouts().pageLoadTimeout(60000 * 5);

        browser.get('http://127.0.0.1:8080/installer.php');

        // configuration
        browser.driver.findElement(by.css('.btn-primary')).click();

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
