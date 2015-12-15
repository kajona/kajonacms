
describe('login', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test login', function() {
        browser.get('https://localhost/agp-core/');

        browser.driver.findElement(by.id('name')).sendKeys('test');
        browser.driver.findElement(by.id('passwort')).sendKeys('test123');
        browser.driver.findElement(by.css('[name="Submit"]')).click();

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual("Übersicht");
    });

});
