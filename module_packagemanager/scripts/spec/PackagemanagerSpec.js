
include('../../../core/module_system/scripts/loader.js');
include('../../../core/module_system/scripts/kajona.js');
include('../../../core/module_packagemanager/scripts/packagemanager.js');

describe("packagemanager.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.packagemanager.addPackageToTest).toBe("function");
        expect(typeof KAJONA.admin.packagemanager.triggerUpdateCheck).toBe("function");
    });

});
