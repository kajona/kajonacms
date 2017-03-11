/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * @module instantSave
 *
 * systemid#strPropertyName
 */
define(['jquery', 'ajax'], function ($, ajax) {

    saveChangeHandler = function () {

        var $objChanged = $(this);
        var keySplitted = $objChanged.data('kajona-instantsave').split('#');

        $objChanged.addClass('peSaving');
        var objStatusIndicator = new SaveIndicator($objChanged);

        objStatusIndicator.showProgress();
        ajax.genericAjaxCall("system", "updateObjectProperty", keySplitted[0]+"&property="+keySplitted[1]+"&value="+$objChanged.val(), null,
            function() {
                objStatusIndicator.addClass('peSaved');
                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000)
            },
            function() {
                $editable.addClass('peFailed');
                objStatusIndicator.addClass('peFailed');

                window.setTimeout(function () {
                    objStatusIndicator.hide();
                }, 5000)
            }
        );

    };

    /**
     * The saveIndicator is used to show a working-indicator associated with a ui element.
     * currently the indicator may represent various states:
     * - showProgress showing the indicator
     * - addClass adding a class, e.g. to indicate a new status
     * - hide destroying the indicator completely
     * @param $objSourceElement
     */
    SaveIndicator = function($objSourceElement) {

        var objDiv = null;
        var objSourceElement = $objSourceElement;

        this.showProgress = function () {

            objDiv = $('<div>').addClass('peProgressIndicator peSaving');
            $('body').append(objDiv);
            objDiv.css('left', objSourceElement.offset().left+objSourceElement.width()).css('top', objSourceElement.offset().top);
        };

        this.addClass = function(strClass) {

            objDiv.addClass(strClass);
        };

        this.hide = function() {
            objSourceElement.removeClass('peFailed');
            objDiv.remove();
            objDiv = null;
        };
    };

    scanElements = function() {
        $('[data-kajona-instantsave]').each(function(key, value) {
            $(this).on('change', saveChangeHandler);
        });
    };

    return {

    };
});
