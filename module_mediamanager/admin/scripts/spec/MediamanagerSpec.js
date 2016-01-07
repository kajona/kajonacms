
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');
include('../../../core/module_mediamanager/admin/scripts/mediamanager.js');

describe("mediamanager.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.mediamanager.createFolder).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.showRealSize).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.showPreview).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.showCropping).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.hideCropping).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.saveCropping).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.saveCroppingToBackend).toBe("function");
        expect(typeof KAJONA.admin.mediamanager.imageEditor.rotate).toBe("function");
    });

});
