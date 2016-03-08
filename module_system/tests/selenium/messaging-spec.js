
describe('module_messaging', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('index.php?admin=1&module=messaging&action=list');

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Nachrichten');

        browser.driver.findElement(by.css('.fa-plus-circle')).click();
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
        browser.get('index.php?admin=1&module=messaging&action=config');

        // check the default values
        browser.driver.wait(protractor.until.elementLocated(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')), 5000);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled')).getAttribute('checked')).not.toBe(null);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).getAttribute('checked')).toBe(null);

        browser.driver.wait(protractor.until.elementLocated(by.css('.bootstrap-switch-id-Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')), 5000);

        // click the enable button
        browser.driver.findElement(by.css('.bootstrap-switch-id-Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).click();

        // refresh
        browser.get('index.php?admin=1&module=messaging&action=config');

        // and revalidate if the ajax request worked as specified
        browser.driver.wait(protractor.until.elementLocated(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')), 5000);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled')).getAttribute('checked')).not.toBe(null);
        expect(browser.driver.findElement(by.id('Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).getAttribute('checked')).not.toBe(null);
    });

});
