//   (c) 2007-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$


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

        minLength: 2,
        delay: 700,

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
            $("#quickhelp").addClass("quickhelpTop");
            KAJONA.admin.scroll = "top";
        }
        else if(scroll <= 10 && KAJONA.admin.scroll != 'margin') {
            $("ul.breadcrumb").removeClass("breadcrumbTop");
            $("#quickhelp").removeClass("quickhelpTop");
            KAJONA.admin.scroll = "fixed";
        }


    });

    KAJONA.v4skin.breadcrumb.updatePathNavigationEllipsis();
    $(window).on("resize", function() {
        KAJONA.v4skin.breadcrumb.updatePathNavigationEllipsis();

    });

    //register desktop notifications for messaging
    //KAJONA.util.desktopNotification.grantPermissions();

    //init offacnvas menu
    $('[data-toggle="offcanvas"]').click(function () {
        $('.row-offcanvas').toggleClass('active')
    });

});

if (typeof KAJONA == "undefined") {
    alert('load kajona.js before!');
}

KAJONA.v4skin = {

    properties : {
        messaging : {},
        tags : {}
    },



    defaultAutoComplete : function() {
        this.minLength = 0;

        this.delay = 500;

        this.messages = {
            noResults: '',
            results: function() {return ''}
        };

        this.search = function(event, ui) {
            //If input field changes -> reset hidden id field
            var $objCur = $(this);
            if(!$objCur.is('[readonly]')) {
                if($('#'+$objCur.attr('id')+'_id')) {
                    $( '#'+$objCur.attr('id')+'_id' ).val( "" );
                }
            }

            //Formentry must have at least 2 charackters to trigger search.
            if(event.target.value.length < 2) {
                event.stopPropagation();
                return false;
            }
            $objCur.css('background-image', 'url('+KAJONA_WEBPATH+'/core/module_v4skin/admin/skins/kajona_v4/img/loading-small.gif)');
        };

        this.response = function(event, ui) {
            $(this).css('background-image', 'none');
        };

        this.focus = function(event, ui) {
            return false;
        };

        this.select = function( event, ui ) {
            if(ui.item) {
                var $objCur = $(this);
                $objCur.val( ui.item.title );
                if($('#'+$objCur.attr('id')+'_id')) {
                    $( '#'+$objCur.attr('id')+'_id' ).val( ui.item.systemid);
                }
            }
        };

        this.create = function( event, ui ) {
            var $objCur = $(this);
            $objCur.css('background-image', 'url('+KAJONA_WEBPATH+'/core/module_v4skin/admin/skins/kajona_v4/img/loading-small-still.gif)').css('background-repeat', 'no-repeat').css('background-position', 'right center');
        }
    },

    messaging : {

        bitFirstLoad : true,

        pollMessages : function() {
            KAJONA.admin.messaging.getRecentMessages(function (objResponse) {
                var $userNotificationsCount = $('#userNotificationsCount');
                var oldCount = $userNotificationsCount.text();
                $userNotificationsCount.text(objResponse.messageCount);
                if (objResponse.messageCount > 0) {
                    $userNotificationsCount.show();
                    if (oldCount != objResponse.messageCount) {
                        var strTitle = document.title.replace("(" + oldCount + ")", "");
                        document.title = "(" + objResponse.messageCount + ") " + strTitle;

                        //if (!KAJONA.v4skin.messaging.bitFirstLoad && oldCount < objResponse.messageCount) {
                        //    KAJONA.util.desktopNotification.showMessage(KAJONA.v4skin.properties.messaging.notification_title, KAJONA.v4skin.properties.messaging.notification_body, function () {
                        //        document.location.href = KAJONA_WEBPATH+'/index.php?admin=1&module=messaging';
                        //    });
                        //}
                    }

                } else {
                    $userNotificationsCount.hide();
                }

                $('#messagingShortlist').empty();
                $.each(objResponse.messages, function (index, item) {
                    if (item.unread == 0)
                        $('#messagingShortlist').append("<li><a href='" + item.details + "'><i class='fa fa-envelope'></i> <b>" + item.title + "</b></a></li>");
                    else
                        $('#messagingShortlist').append("<li><a href='" + item.details + "'><i class='fa fa-envelope'></i> " + item.title + "</a></li>");
                });
                $('#messagingShortlist').append("<li class='divider'></li><li><a href='"+KAJONA_WEBPATH+"/index.php?admin=1&module=messaging'><i class='fa fa-envelope'></i> " + KAJONA.v4skin.properties.messaging.show_all + "</a></li>");

                window.setTimeout("KAJONA.v4skin.messaging.pollMessages()", 20000);
                KAJONA.v4skin.messaging.bitFirstLoad = false;
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

KAJONA.v4skin.initTagMenu = function() {
    KAJONA.admin.ajax.genericAjaxCall("tags", "getFavoriteTags", "", function(data, status, jqXHR) {
        if(status == 'success') {
            $.each($.parseJSON(data), function(index, item) {
                $('#tagsSubemenu').append("<li><a href='"+item.url+"'><i class='fa fa-tag'></i> "+item.name+"</a></li>");
            });
            $('#tagsSubemenu').append("<li class='divider'></li><li><a href='"+KAJONA_WEBPATH+"/index.php?admin=1&module=tags'><i class='fa fa-tag'></i> "+KAJONA.v4skin.properties.tags.show_all+"</a></li>")
        }
    });
};

/**
 * Removes an object list row from the list
 *
 * @param el
 */
KAJONA.v4skin.removeObjectListItem = function(el){
    // remove all active tooltips
    KAJONA.admin.tooltip.removeTooltip(el);

    // remove element
    $(el).parent().parent().fadeOut(0, function(){
        $(this).remove();
    });
};

/**
 * Gets all items containd in the object list
 *
 * @param strElementName
 * @returns {Array}
 */
KAJONA.v4skin.getObjectListItems = function(strElementName) {
    var table = KAJONA.util.getElementFromOpener(strElementName);

    var arrItems = [];

    var tbody = table.find('tbody');
    if(tbody.length > 0) {
        // remove only elements which are in the arrAvailableIds array
        tbody.children().each(function(){
            var strId = $(this).find('input[type="hidden"]').val();
            arrItems.push(strId);
        });
    }

    return arrItems;
};

/**
 * Sets an array of items to an object list. We remove only elements which are available in the arrAvailableIds array
 *
 * @param strElementName  - name of the objectlist element
 * @param arrItems        - array with item of the following format {strSystemId: <systemid>, strDisplayName:<displayname>, strIcon:<icon>}
 * @param arrAvailableIds -
 */
KAJONA.v4skin.setObjectListItems = function(strElementName, arrItems, arrAvailableIds, strDeleteButton){
    var table = KAJONA.util.getElementFromOpener(strElementName);

    var tbody = table.find('tbody');
    if(tbody.length > 0) {
        // remove only elements which are in the arrAvailableIds array
        tbody.children().each(function(){
            var strId = $(this).find('input[type="hidden"]').val();
            if($.inArray(strId, arrAvailableIds) !== -1) {//if strId in array
                $(this).remove();
            }
        });

        // add new elements
        for(var i = 0; i < arrItems.length; i++) {
            var strEscapedTitle = $('<div></div>').text(arrItems[i].strDisplayName).html();
            var html = '';
            html+= '<tr>';
            html+= '    <td>' + arrItems[i].strIcon + '</td>';
            html+= '    <td>' + strEscapedTitle + ' <input type="hidden" name="' + strElementName + '[]" value="' + arrItems[i].strSystemId + '" /></td>';
            html+= '    <td class="icon-cell">';
            html+= '        <a href="#" onclick="KAJONA.v4skin.removeObjectListItem(this);return false">' + strDeleteButton + '</a>';
            html+= '    </td>';
            html+= '</tr>';

            tbody.append(html);
        }
    }
};

/**
 * We get the current tree selection from the iframe element and set the selection in the object list
 *
 * @param objIframeEl
 * @param strElementName
 */
KAJONA.v4skin.updateCheckboxTreeSelection = function(objIframeEl, strElementName, strDeleteButton){
    if(objIframeEl && objIframeEl.contentWindow) {
        var jstree = objIframeEl.contentWindow.$('.jstree');
        if(jstree.length > 0) {
            // we modify only the ids which are visible for the user all other ids stay untouched
            var arrAvailableIds = [];
            jstree.find('li').each(function(){
                arrAvailableIds.push($(this).attr('systemid'));
            });

            var arrEls = jstree.jstree('get_checked');
            var arrItems = [];
            for(var i = 0; i < arrEls.length; i++) {
                var el = $(arrEls[i]);
                var strSystemId = el.attr('id');
                var strDisplayName = el.text().trim();
                var strIcon = el.find('[rel="tooltip"]').html();

                arrItems.push({
                    strSystemId: strSystemId,
                    strDisplayName: strDisplayName,
                    strIcon: strIcon
                });
            }

            KAJONA.v4skin.setObjectListItems(strElementName, arrItems, arrAvailableIds, strDeleteButton);

            jsDialog_1.hide();
        }
    }
};

/**
 * Returns all systemids which are available in the object list. The name of the object list element name must be
 * available as GET parameter "element_name"
 *
 * @returns array
 */
KAJONA.v4skin.getCheckboxTreeSelectionFromParent = function(){
    if($('.jstree').length > 0) {
        // the query parameter contains the name of the form element where we insert the selected elements
        var strElementName = KAJONA.v4skin.getQueryParameter("element_name");
        var table = parent.$('#' + strElementName);
        var arrSystemIds = [];
        if(table.length > 0) {
            table.find('input[type="hidden"]').each(function(){
                arrSystemIds.push($(this).val());
            });
        }

        return arrSystemIds;
    }
};

/**
 * Extracts an query parameter from the location query string
 *
 * @param string name
 * @returns string
 */
KAJONA.v4skin.getQueryParameter = function(name) {
    var pos = location.search.indexOf("&" + name + "=");
    if(pos != -1) {
        var endPos = location.search.indexOf("&", pos + 1);
        if(endPos == -1) {
            return location.search.substr(pos + name.length + 2);
        }
        else {
            return location.search.substr(pos + name.length + 2, endPos - (pos + name.length + 2));
        }
    }
    return null;
};
