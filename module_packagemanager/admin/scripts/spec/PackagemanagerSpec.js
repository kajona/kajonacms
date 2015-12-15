
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');
include('../../../core/module_packagemanager/admin/scripts/packagemanager.js');

describe("packagemanager.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.packagemanager.addPackageToTest).toBe("function");
        expect(typeof KAJONA.admin.packagemanager.triggerUpdateCheck).toBe("function");
    });

});
