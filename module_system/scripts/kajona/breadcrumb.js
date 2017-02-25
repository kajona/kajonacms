
/**
 * Module to handle the general access to the breadcrumb.
 * Provides an API to add entries or to reset the bar
 *
 * @module breadcrumb
 */
define('breadcrumb', ['jquery'], function ($) {

    var $objBreadcrumb = $("div.pathNaviContainer ul.breadcrumb");


    var getUnusedSpace = function(intMaxWidth) {
        var intTotalUnused = 0;
        $(".pathNaviContainer  .breadcrumb  li.pathentry").each(function() {
            var $li = $(this);
            if($li.width() < intMaxWidth) {
                intTotalUnused += (intMaxWidth - $li.width());
            }
        });

        return intTotalUnused;
    };


    var updatePathNavigationEllipsis = function() {

        var $arrPathLIs = $(".pathNaviContainer  .breadcrumb  li.pathentry");

        //first run: get the number of entries and a first styling
        var intEntries = ($arrPathLIs.length);
        var intWidth = $objBreadcrumb.width();
        var intMaxWidth = Math.ceil(intWidth/intEntries);

        $arrPathLIs.css("max-width", intMaxWidth);

        //second run: calc the remaining x-space
        var intTotalUnused = getUnusedSpace(intMaxWidth);

        if(intTotalUnused > intMaxWidth) {
            intMaxWidth = Math.ceil(intWidth/ (intEntries - (Math.floor(intTotalUnused / intMaxWidth)) ));
            $arrPathLIs.css("max-width", intMaxWidth);
        }

    };

    return /** @alias module:breadcrumb */ {

        updateEllipsis : function() {
            updatePathNavigationEllipsis();
        },

        appendLinkToPathNavigation : function(strLinkContent) {
            var link = $("<li class='pathentry'></li>").append(strLinkContent+"&nbsp;");
            $objBreadcrumb.append(link);
            updatePathNavigationEllipsis();
        },

        resetBar : function() {
            $objBreadcrumb.find("li.pathentry:not(.home)").remove();
        }

    }

});
