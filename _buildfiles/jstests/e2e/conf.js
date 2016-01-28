
var ScreenShotReporter = require('protractor-screenshot-reporter');

exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    specs: [
        'install-spec.js',
        'login-spec.js',
        '../../temp/kajona/core*/module_*/tests/selenium/*-spec.js',
        '../../temp/kajona/files/extract/module_*/tests/selenium/*-spec.js'
    ],
    jasmineNodeOpts: {
        defaultTimeoutInterval: 300000 // 5 minutes
    },
    plugins: [{
        path: '../node_modules/protractor/plugins/console/index.js',
        failOnWarning: false,
        failOnError: true
    }],
    onPrepare: function() {
        jasmine.getEnv().addReporter(new ScreenShotReporter({
            baseDirectory: '../../build/screenshots'
        }));
    }
};
