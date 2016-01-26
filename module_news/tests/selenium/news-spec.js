
describe('module_news', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=news&action=listNewsAndCategories');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('News');
    });

});
