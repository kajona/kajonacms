
var fs = require('fs');

describe('module_stats', function() {

    beforeEach(function() {
        browser.ignoreSynchronization = true;
    });

    it('test list', function() {
        browser.get('http://127.0.0.1:8080/index.php?admin=1&module=stats&action=list');

        browser.takeScreenshot().then(function(png) {
            var stream = fs.createWriteStream('debug.png');
            stream.write(new Buffer(png, 'base64'));
            stream.end();
        });

        expect(browser.driver.findElement(by.id('moduleTitle')).getText()).toEqual('Statistiken');
    });

});
