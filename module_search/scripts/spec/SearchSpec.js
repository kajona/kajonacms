
include('../../../core/module_system/scripts/loader.js');
include('../../../core/module_system/scripts/kajona.js');
include('../../../core/module_search/scripts/search.js');

describe("search.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.search.triggerFullSearch).toBe("function");
    });

});
