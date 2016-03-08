
describe('module_workflows', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('index.php?admin=1&module=workflows&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Workflows');
    });

});
