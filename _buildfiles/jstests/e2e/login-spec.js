
describe('setup', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test login', function() {
        browser.get('http://127.0.0.1:8080/admin');

        browser.driver.findElement(by.id('name')).sendKeys('test');
        browser.driver.findElement(by.id('passwort')).sendKeys('test123');
        browser.driver.findElement(by.css('.savechanges')).click();

        // check whether login was successful
        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");
    });

});
