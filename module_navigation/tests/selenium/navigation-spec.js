"use strict";

var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_navigation', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=navigation&action=list').then(function() {
            expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Navigationen');
        });
    });

});
