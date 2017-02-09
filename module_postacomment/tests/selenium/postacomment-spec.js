"use strict";

const SeleniumUtil = requireHelper('/util/SeleniumUtil.js');

describe('module_postacomment', function() {

    it('test list', function() {
        SeleniumUtil.gotToUrl('index.php?admin=1&module=postacomment&action=list');
    });

});
