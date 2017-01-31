
//add additional resolving paths
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.pages = "module_pages/scripts/kajona/pages";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var pages = requirejs("pages");

describe("pages", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof pages.initBlockSort).toBe("function");
    });

});
