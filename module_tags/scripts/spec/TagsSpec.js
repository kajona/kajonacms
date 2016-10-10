
//add additional resolving paths
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.tags = "module_tags/scripts/kajona/tags";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var tags = requirejs("tags");

describe("tags", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof tags.createFavorite).toBe("function");
        expect(typeof tags.saveTag).toBe("function");
        expect(typeof tags.reloadTagList).toBe("function");
        expect(typeof tags.removeTag).toBe("function");
        expect(typeof tags.loadTagTooltipContent).toBe("function");
    });

});
