
describe('module_votings', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('index.php?admin=1&module=votings&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Votings');
    });

});
