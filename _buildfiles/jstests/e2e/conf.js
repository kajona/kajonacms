var ScreenShotReporter = require('protractor-screenshot-reporter');

exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    baseUrl: 'https://localhost',//set dynamically in onPrepare
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
    onPrepare: function () {

        /** base path of the selenium */
        const strBasePath = __dirname + '/../../temp/kajona/core/_buildfiles/jstests/selenium';

        /** If you are testing against a non-angular site - set ignoreSynchronization setting to true */
        browser.ignoreSynchronization = true;


        /**add requireHelper to global variable */
        global.requireHelper = function (relativePath) {// "relativePath" - path, relative to "basePath" variable
            return require(strBasePath + relativePath);
        };

        /** Set baseUrl dynamically */
        const path = require('path');
        const strPathToProject = path.join(__dirname, "/../../../../");//path to project folder
        const strProjectName = path.basename(strPathToProject);//determine project folder name
        browser.baseUrl = browser.baseUrl + "/" + strProjectName + "/core/_buildfiles/temp/kajona";

        /** jasmine screenshot reporter */
        jasmine.getEnv().addReporter(new ScreenShotReporter({
            baseDirectory: '../build/screenshots',
            pathBuilder: function (spec, descriptions, results, capabilities) {
                var fileName = descriptions.reverse().join("_");
                var name = '';
                for (var i = 0; i < fileName.length; i++) {
                    if (fileName.charAt(i).match(/^[A-z0-9_]$/)) {
                        name += fileName.charAt(i);
                    } else if (fileName.charAt(i) == ' ') {
                        name += '_';
                    }
                }
                return name;
            }
        }));
    }
};
