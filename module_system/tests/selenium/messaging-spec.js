"use strict";

var SeleniumUtil = requireHelper('/util/SeleniumUtil.js');
var SeleniumWaitHelper = requireHelper('/util/SeleniumWaitHelper.js');


describe('module_messaging', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=messaging&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Nachrichten');

        element.all(by.css('.actions')).last().$('a').click();

        browser.driver.wait(protractor.until.elementLocated(By.id('folderviewDialog_iframe')), 5000);
        browser.driver.switchTo().frame(browser.driver.findElement(by.id('folderviewDialog_iframe')));

        // enter a new message to the form
        browser.driver.findElement(by.id('messaging_user')).sendKeys('test');

        // select user from autocomplete
        browser.driver.wait(protractor.until.elementLocated(by.css('.ui-autocomplete .ui-menu-item')), 5000);
        browser.driver.findElement(by.css('.ui-autocomplete .ui-menu-item')).click();

        browser.driver.findElement(by.id('messaging_title')).sendKeys('foo');
        browser.driver.findElement(by.id('messaging_body')).sendKeys('bar');
        browser.driver.findElement(by.css('button[type="submit"]')).click();

        expect(browser.driver.findElement(by.id('content')).getText()).toMatch('Die Nachricht wurde erfolgreich verschickt.');

        browser.driver.findElement(by.css('button[type="submit"]')).click();
        browser.driver.switchTo().defaultContent();
    });

    it('provides config page', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=messaging&action=config');

        // check the default values
        SeleniumWaitHelper.getElementWhenPresent(SeleniumUtil.getWebDriver(), By.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail'));
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled')).getAttribute('checked')).not.toBe(null);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).getAttribute('checked')).toBe(null);

        browser.driver.wait(protractor.until.elementLocated(by.css('.bootstrap-switch-id-Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')), 5000);

        // click the enable button
        browser.driver.findElement(by.css('.bootstrap-switch-id-Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).click();

        // refresh
        SeleniumUtil.gotToUrl('index.php?admin=1&module=messaging&action=config');

        // and revalidate if the ajax request worked as specified
        SeleniumWaitHelper.getElementWhenPresent(SeleniumUtil.getWebDriver(), By.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail'));
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled')).getAttribute('checked')).not.toBe(null);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).getAttribute('checked')).not.toBe(null);
    });

});
