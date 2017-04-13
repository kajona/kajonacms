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
    plugins: [
    {
        package: 'protractor-screenshoter-plugin',
        screenshotPath: '../build/screenshots',
        clearFoldersBeforeTest: true
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

        // returning the promise makes protractor wait for the reporter config before executing tests
        return global.browser.getProcessedConfig().then(function (config) {
            //it is ok to be empty
        });
    }
};
