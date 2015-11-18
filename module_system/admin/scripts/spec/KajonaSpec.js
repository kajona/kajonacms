
include('../../../core/module_system/system/scripts/loader.js');
include('../../../core/module_system/admin/scripts/kajona.js');

describe("kajona.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.util.getElementFromOpener).toBe("function");
        expect(typeof KAJONA.util.evalScript).toBe("function");
        expect(typeof KAJONA.util.isTouchDevice).toBe("function");
        expect(typeof KAJONA.util.inArray).toBe("function");
        expect(typeof KAJONA.util.fold).toBe("function");
    });

    it("test is touch device", function() {
        expect(KAJONA.util.isTouchDevice()).toBe(0);
    });

    it("test in array", function() {
        expect(KAJONA.util.inArray("foo", ["bar", "foo"])).toBe(true);
        expect(KAJONA.util.inArray("baz", ["bar", "foo"])).toBe(false);
    });

});
