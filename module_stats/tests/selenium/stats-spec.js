"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_stats', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=stats&action=list');
    });

});
