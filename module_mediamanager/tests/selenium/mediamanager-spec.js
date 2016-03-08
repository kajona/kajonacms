
describe('module_mediamanager', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('index.php?admin=1&module=mediamanager&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Medien-Manager');
    });

});
