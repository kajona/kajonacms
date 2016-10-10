
//add additional resolving paths
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.packagemanager = "module_packagemanager/scripts/kajona/packagemanager";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var packagemanager = requirejs("packagemanager");

describe("packagemanager", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof packagemanager.addPackageToTest).toBe("function");
        expect(typeof packagemanager.triggerUpdateCheck).toBe("function");
    });

});
