//   (c) 2007-2014 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$


$(function () {

    $.widget('custom.catcomplete', $.ui.autocomplete, {
        _renderMenu: function(ul, items) {
            var self = this;
            var currentCategory = '';

            $.each(items, function(index, item) {
                if (item.module != currentCategory) {
                    ul.append('<li class="ui-autocomplete-category"><h3 class="small">' + item.module + '</h3></li>');
                    currentCategory = item.module;
                }
                self._renderItemData(ul, item);
            });

            ul.append('<li class="detailedResults"><a href="#">'+searchExtendText+'</a></li>');
            ul.addClass('dropdown-menu');
            ul.addClass('search-dropdown-menu');

            ul.find('.detailedResults a').click(function () {
                $('.navbar-search').submit();
            });
        },
        _renderItemData: function (ul, item) {
            return $('<li class="clearfix"></li>')
                .data('ui-autocomplete-item', item)
                .append('<a>' + item.icon + item.description + '</a>')
                .appendTo(ul);
        }
    });

    $('#globalSearchInput').catcomplete({
        //source: '_skinwebpath_/search.json',
        source: function(request, response) {
            $.ajax({
                url: KAJONA_WEBPATH+'/xml.php?admin=1',
                type: 'POST',
                dataType: 'json',
                data: {
                    search_query: request.term,
                    module: 'search',
                    action: 'searchXml',
                    asJson: '1'
                },
                success: response
            });
        },
        select: function (event, ui) {
            if(ui.item) {
                document.location = ui.item.link;
            }
        },
        messages: {
            noResults: '',
            results: function() {}
        },
        search: function(event, ui) {
            $(this).css("background-image", "url("+KAJONA_WEBPATH+"/core/module_v4skin/admin/skins/kajona_v4/img/loading-small.gif)").css("background-repeat", "no-repeat").
            css("background-position", "right center");
        },
        response: function(event, ui) {
            $(this).css("background-image", "none");
        }
    });





    // init popovers & tooltips
    $('#content a[rel=popover]').popover();
    KAJONA.admin.tooltip.initTooltip();

    KAJONA.admin.statusDisplay.classOfMessageBox = "alert alert-info";
    KAJONA.admin.statusDisplay.classOfErrorBox = "alert alert-error";

    KAJONA.admin.scroll = null;
    $(window).scroll(function() {
        var scroll = $(this).scrollTop();
        if(scroll > 10 && KAJONA.admin.scroll != 'top') {
            $("ul.breadcrumb").addClass("breadcrumbTop");
            KAJONA.admin.scroll = "top";
        }
        else if(scroll <= 10 && KAJONA.admin.scroll != 'margin') {
            $("ul.breadcrumb").removeClass("breadcrumbTop");
            KAJONA.admin.scroll = "fixed";
        }


    });

    KAJONA.v4skin.breadcrumb.updatePathNavigationEllipsis();
    $(window).on("resize", function() {
        KAJONA.v4skin.breadcrumb.updatePathNavigationEllipsis();

    });

    //register desktop notifications for messaging
    KAJONA.util.desktopNotification.grantPermissions();

});

if (typeof KAJONA == "undefined") {
    alert('load kajona.js before!');
}

KAJONA.v4skin = {



    defaultAutoComplete : function() {

        this.minLength = 2;

        this.delay = KAJONA.util.isTouchDevice() ? 2000 : 0;

        this.messages = {
            noResults: '',
            results: function() {return ''}
        };

        this.search = function(event, ui) {
            $(this).css('background-image', 'url('+KAJONA_WEBPATH+'/core/module_v4skin/admin/skins/kajona_v4/img/loading-small.gif)');
        };

        this.response = function(event, ui) {
            $(this).css('background-image', 'none');
        };

        this.focus = function() {
            return false;
        };

        this.select = function( event, ui ) {
            if(ui.item) {
                var $objCur = $(this);
                $objCur.val( ui.item.title );
                if($('#'+$objCur.attr('id')+'_id')) {
                    $objCur.blur();
                    $( '#'+$objCur.attr('id')+'_id' ).val( ui.item.systemid);

                    //try to find the next save button
                    $objCur.closest("form").find("button[type='submit']").focus();
                }
            }

        };

        this.create = function( event, ui ) {
            var $objCur = $(this);
            $objCur.css('background-image', 'url('+KAJONA_WEBPATH+'/core/module_v4skin/admin/skins/kajona_v4/img/loading-small-still.gif)').css('background-repeat', 'no-repeat').css('background-position', 'right center');

            $('#'+$objCur.attr('id')).keypress(function(event) {
                if($('#'+$objCur.attr('id')+'_id')) {
                    $( '#'+$objCur.attr('id')+'_id' ).val( "" );
                }
            });
        }
    }
};

KAJONA.v4skin.breadcrumb = {
    updatePathNavigationEllipsis : function() {

        var $arrPathLIs = $(".pathNaviContainer  .breadcrumb  li.pathentry");
        var $objBreadcrumb = $(".pathNaviContainer  .breadcrumb");

        //first run: get the number of entries and a first styling
        var intEntries = ($arrPathLIs.length);
        var intWidth = $objBreadcrumb.width();
        var intMaxWidth = Math.ceil(intWidth/intEntries);

        $arrPathLIs.css("max-width", intMaxWidth);

        //second run: calc the remaining x-space
        var intTotalUnused = KAJONA.v4skin.breadcrumb.getUnusedSpace(intMaxWidth);

        if(intTotalUnused > intMaxWidth) {
            intMaxWidth = Math.ceil(intWidth/ (intEntries - (Math.floor(intTotalUnused / intMaxWidth)) ));
            $arrPathLIs.css("max-width", intMaxWidth);
        }

    },

    getUnusedSpace : function(intMaxWidth) {
        var intTotalUnused = 0;
        $(".pathNaviContainer  .breadcrumb  li.pathentry").each(function() {
            var $li = $(this);
            if($li.width() < intMaxWidth) {
                intTotalUnused += (intMaxWidth - $li.width());
            }
        });

        return intTotalUnused;
    },

    appendLinkToPathNavigation : function(strLinkContent) {
        var link = $("<li class='pathentry'></li>").append(strLinkContent+"&nbsp;");
        $("div.pathNaviContainer  ul.breadcrumb").append(link);
        KAJONA.v4skin.breadcrumb.updatePathNavigationEllipsis();
    }

};

