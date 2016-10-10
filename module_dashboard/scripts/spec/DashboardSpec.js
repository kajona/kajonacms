

//add additional resolving paths
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.dashboard = "module_dashboard/scripts/kajona/dashboard";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var dashboard = requirejs("dashboard");
describe("dashboard", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof dashboard.removeWidget).toBe("function");
        expect(typeof dashboard.init).toBe("function");
    });

});

