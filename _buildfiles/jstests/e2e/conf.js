
var ScreenShotReporter = require('protractor-screenshot-reporter');

exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    baseUrl: 'http://127.0.0.1:8080',
    specs: [
        'install-spec.js',
        'login-spec.js',
        '../../temp/kajona/core*/module_*/tests/selenium/*-spec.js',
        '../../temp/kajona/files/extract/module_*/tests/selenium/*-spec.js'
    ],
    capabilities: {
        browserName: 'chrome',
        chromeOptions: {
            args: ['--no-sandbox']
        }
    },
    jasmineNodeOpts: {
        defaultTimeoutInterval: 480000 // 8 minutes
    },
    plugins: [{
        package: 'protractor-console-plugin',
        failOnWarning: false,
        failOnError: true
    }],
    onPrepare: function() {
        var basePath = __dirname + '/../../temp/kajona/core/_buildfiles/jstests/selenium';

        // "relativePath" - path, relative to "basePath" variable

        // If your entity files have suffixes - you can also keep them here
        // not to mention them in test files every time
        global.requireHelper = function (relativePath) {
            return require(basePath + relativePath);
        };

        jasmine.getEnv().addReporter(new ScreenShotReporter({
            baseDirectory: '../build/screenshots',
            pathBuilder: function(spec, descriptions, results, capabilities){
                var fileName = descriptions.reverse().join("_");
                var name = '';
                for (var i = 0; i < fileName.length; i++) {
                    if (fileName.charAt(i).match(/^[A-z0-9_]$/)) {
                        name+= fileName.charAt(i);
                    } else if (fileName.charAt(i) == ' ') {
                        name+= '_';
                    }
                }
                return name;
            }
        }));
    }
};
