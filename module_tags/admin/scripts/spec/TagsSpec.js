
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');
include('../../../core/module_tags/admin/scripts/tags.js');

describe("tags.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.tags.createFavorite).toBe("function");
        expect(typeof KAJONA.admin.tags.saveTag).toBe("function");
        expect(typeof KAJONA.admin.tags.reloadTagList).toBe("function");
        expect(typeof KAJONA.admin.tags.removeTag).toBe("function");
        expect(typeof KAJONA.admin.tags.loadTagTooltipContent).toBe("function");
    });

});
