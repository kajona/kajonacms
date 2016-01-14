
exports.config = {
    seleniumAddress: 'http://localhost:4444/wd/hub',
    specs: [
        'install-spec.js',
        'login-spec.js',
        '../../../../core*/module_*/tests/selenium/*-spec.js'
    ],
    jasmineNodeOpts: {
        defaultTimeoutInterval: 300000 // 5 minutes
    },
    plugins: [{
        path: '../node_modules/protractor/plugins/console/index.js',
        failOnWarning: false,
        failOnError: true
    }]
};
