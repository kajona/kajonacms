
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.mediamanager = "module_mediamanager/scripts/kajona/mediamanager";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var mediamanager = requirejs("mediamanager");

describe("mediamanager", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof mediamanager.createFolder).toBe("function");
        expect(typeof mediamanager.imageEditor.showRealSize).toBe("function");
        expect(typeof mediamanager.imageEditor.showPreview).toBe("function");
        expect(typeof mediamanager.imageEditor.showCropping).toBe("function");
        expect(typeof mediamanager.imageEditor.hideCropping).toBe("function");
        expect(typeof mediamanager.imageEditor.saveCropping).toBe("function");
        expect(typeof mediamanager.imageEditor.saveCroppingToBackend).toBe("function");
        expect(typeof mediamanager.imageEditor.rotate).toBe("function");
    });

});
