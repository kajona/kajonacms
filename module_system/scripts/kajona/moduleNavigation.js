
/**
 * Module to handle the general access to the module navigation
 *
 * @module moduleNavigation
 */
define('moduleNavigation', ['jquery'], function ($) {

    var $objNavigation = $("#moduleNavigation");



    return /** @alias module:moduleNavigation */ {

        setModuleActive : function (strModule) {
            console.debug('set active: '+strModule);


            //default module. not combined
            $('#moduleNavigation a.active').removeClass('active');

            //is combined
            if($('.panel-combined .collapse[data-kajona-module="'+strModule+'"]').length != 0) {
                $('#moduleNavigation .panel .linkcontainer').addClass('active');
            } else {
                $("a[data-kajona-module='" + strModule + "']").addClass('active');
            }


                $(".collapse").not('[data-kajona-module="' + strModule + '"]').collapse('hide');
                $('.collapse[data-kajona-module="' + strModule + '"]').collapse('show');


        }

    }

});
