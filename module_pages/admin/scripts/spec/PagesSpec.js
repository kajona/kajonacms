
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');
include('../../../core/module_pages/admin/scripts/pages.js');

describe("pages.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.pages.initBlockSort).toBe("function");
    });

});
