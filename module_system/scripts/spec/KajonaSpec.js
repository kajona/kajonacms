

module.paths.unshift(__dirname+"/../../../_buildfiles/jstests/node_modules");
var requirejs = require('requirejs');
var util = requirejs("util");

describe("kajona", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof util.getElementFromOpener).toBe("function");
        expect(typeof util.evalScript).toBe("function");
        expect(typeof util.isTouchDevice).toBe("function");
        expect(typeof util.inArray).toBe("function");
        expect(typeof util.fold).toBe("function");
    });

    it("test is touch device", function() {
        expect(util.isTouchDevice()).toBe(0);
    });

    it("test in array", function() {
        expect(util.inArray("foo", ["bar", "foo"])).toBe(true);
        expect(util.inArray("baz", ["bar", "foo"])).toBe(false);
    });

});
