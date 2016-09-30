
include('../../../core/module_system/scripts/loader.js');
include('../../../core/module_system/scripts/kajona.js');
include('../../../core/module_dashboard/scripts/dashboard.js');

describe("dashboard.js", function() {

    beforeEach(function() {
    });

    it("test functions available", function() {
        expect(typeof KAJONA.admin.dashboard.removeWidget).toBe("function");
        expect(typeof KAJONA.admin.dashboard.init).toBe("function");
    });

});
