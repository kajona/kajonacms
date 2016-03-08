
describe('login', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test login', function() {
        browser.get('index.php?admin=1');

        browser.driver.findElement(by.id('name')).sendKeys('test');
        browser.driver.findElement(by.id('passwort')).sendKeys('test123');
        browser.driver.findElement(by.css('.savechanges')).click();

        // check whether login was successful
        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");
    });

});
