
describe('module_messaging', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('provides config page', function() {

        browser.manage().timeouts().pageLoadTimeout(1000);
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=messaging&action=config');

        //check the default values
        expect($('#Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled').getAttribute('checked')).not.toBe(null);
        expect($('#Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail').getAttribute('checked')).toBe(null);

        //click the enable button
        browser.driver.findElement(by.css('bootstrap-switch-id-Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail')).click();
        browser.manage().timeouts().pageLoadTimeout(2000);
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=messaging&action=config');

        //and revalidate if the ajax request worked as specified
        expect($('#Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_enabled').getAttribute('checked')).not.toBe(null);
        expect($('#Kajona-Packagemanager-System-Messageproviders-MessageproviderPackageupdate_bymail').getAttribute('checked')).not.toBe(null);


    });

});
