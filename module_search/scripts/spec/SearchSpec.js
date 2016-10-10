
//add additional resolving paths
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.search = "module_search/scripts/kajona/search";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var search = requirejs("search");

describe("search", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof search.triggerFullSearch).toBe("function");
    });

});
