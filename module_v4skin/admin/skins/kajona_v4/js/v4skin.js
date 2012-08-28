$(function () {

    $.widget('custom.catcomplete', $.ui.autocomplete, {
        _renderMenu: function(ul, items) {
            var self = this;
            var currentCategory = '';

            $.each(items, function(index, item) {
                if (item.module != currentCategory) {
                    ul.append('<li class="ui-autocomplete-category"><h3>' + item.module + '</h3></li>');
                    currentCategory = item.module;
                }
                self._renderItem(ul, item);
            });

            ul.append('<li class="detailedResults"><a href="#">View detailed search results</a></li>');
            ul.addClass('dropdown-menu');
            ul.addClass('search-dropdown-menu');
        },
        _renderItem: function (ul, item) {
            return $('<li></li>')
                .data('item.autocomplete', item)
                .append('<a>' + '<img src="'+item.icon+'" alt="" class="pull-left"><h4 class="pull-left">' + item.systemid + '</h4><br>' + item.description + '</a>')
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






    //sidebar responsive
    $('.nav-collapse').on('show', function () {
        var collapsible = $(this);
        window.setTimeout(function () {
            collapsible.css({
                overflow: 'visible',
                height: 'auto'
            });
        }, 500);
    });

    $('.nav-collapse').on('hide', function () {
        $(this).css('overflow', '');
    });




    $('#myModal1').on('show', function () {
        var $modal = $(this);
        var $progressbar = $modal.find('.progress > .bar');
        var progress = 0;

        var interval = window.setInterval(function () {
            progress += 10;
            $progressbar.css('width', progress + '%');

            if (progress >= 100) {
                $modal.modal('hide');

                window.clearInterval(interval);
                $progressbar.css('width', '0%');
            }
        }, 1000);

    });


    // insert demo thumbnails
    var $thumb = $('.gallery li').first();
    for (var i = 2; i < 12; i++) {
        var $newThumb = $thumb.clone();
        $newThumb.find('.number').html(i);
        $('.gallery').append($newThumb);
    }

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