"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_faqs', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=faqs&action=list');
    });

});
