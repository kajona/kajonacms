
/**
 * Module to handle the general access to the module navigation
 *
 * @module moduleNavigation
 */
define('moduleNavigation', ['jquery', 'ajax'], function ($, ajax) {

    return /** @alias module:moduleNavigation */ {

        setModuleActive : function (strModule) {
            var $moduleNavigation = $('#moduleNavigation');
            $moduleNavigation.find('a.active').removeClass('active');
            $moduleNavigation.find('.linkcontainer.active').removeClass('active');

            if($('.panel-combined .collapse[data-kajona-module="'+strModule+'"]').length != 0) {
                //is combined
                $moduleNavigation.find('.panel .linkcontainer').addClass('active');
            } else {
                //default: not combined
                $("a[data-kajona-module='" + strModule + "']").addClass('active');
            }
        },

        loadNavigation : function(strAspect) {
            if (!strAspect) {
                strAspect = "";
            }
            ajax.loadUrlToElement("#moduleNavigation", '/xml.php?admin=1&module=v4skin&action=getBackendNavi&aspect='+(strAspect ? strAspect : ""));
        }

    }

});
