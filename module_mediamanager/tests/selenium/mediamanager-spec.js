
describe('module_mediamanager', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=mediamanager&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Medien-Manager');
    });

});
