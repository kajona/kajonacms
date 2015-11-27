
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');
include('../../../core/module_search/admin/scripts/search.js');

describe("search.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.search.switchFilterAllModules).toBe("function");
        expect(typeof KAJONA.admin.search.triggerFullSearch).toBe("function");
    });

});
