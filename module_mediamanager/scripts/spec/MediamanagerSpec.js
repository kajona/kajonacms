
module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');

var modulePaths = global.kajonaPaths;
modulePaths.mediamanager = "module_mediamanager/scripts/kajona/mediamanager";
modulePaths.imageeditor = "module_mediamanager/scripts/kajona/imageeditor";

requirejs.config({
    nodeRequire: require,
    baseUrl : __dirname+"/../../../",
    paths : modulePaths
});


var mediamanager = requirejs("mediamanager");
var imageEditor = requirejs("imageeditor");

describe("mediamanager", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof mediamanager.createFolder).toBe("function");
        expect(typeof imageEditor.showRealSize).toBe("function");
        expect(typeof imageEditor.showPreview).toBe("function");
        expect(typeof imageEditor.showCropping).toBe("function");
        expect(typeof imageEditor.hideCropping).toBe("function");
        expect(typeof imageEditor.saveCropping).toBe("function");
        expect(typeof imageEditor.saveCroppingToBackend).toBe("function");
        expect(typeof imageEditor.rotate).toBe("function");
    });

});
