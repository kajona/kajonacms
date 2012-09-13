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
                self._renderItem(ul, item);
            });

            ul.append('<li class="detailedResults"><a href="#">View detailed search results</a></li>');
            ul.addClass('dropdown-menu');
            ul.addClass('search-dropdown-menu');
        },
        _renderItem: function (ul, item) {
            return $('<li class="clearfix"></li>')
                .data('item.autocomplete', item)
                .append('<a>' + '<img src="'+item.icon+'" alt="" class="pull-left">' + item.description + '</a>')
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
                    query: request.term,
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
        }
    });





    // init popovers & tooltips
    $('#content a[rel=popover]').popover();

    KAJONA.admin.tooltip.initTooltip();

    KAJONA.admin.contextMenu.showElementMenu = function() {};

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
});