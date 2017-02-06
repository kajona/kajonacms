"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_votings', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=votings&action=list');
    });

});
