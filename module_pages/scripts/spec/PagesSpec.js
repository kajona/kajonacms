
include('../../../core/module_system/scripts/loader.js');
include('../../../core/module_system/scripts/kajona.js');
include('../../../core/module_pages/scripts/pages.js');

describe("pages.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.pages.initBlockSort).toBe("function");
    });

});
