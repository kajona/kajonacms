
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');

describe("kajona.js", function() {

    beforeEach(function() {
    });

    it("test is touch device", function() {
        expect(KAJONA.util.isTouchDevice()).toBe(0);
    });

    it("test in array", function() {
        expect(KAJONA.util.inArray("foo", ["bar", "foo"])).toBe(true);
        expect(KAJONA.util.inArray("baz", ["bar", "foo"])).toBe(false);
    });

});
