"use strict";

var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_faqs', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=faqs&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('FAQ');
    });

});
