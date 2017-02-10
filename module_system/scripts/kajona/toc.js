/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Appends an table of contents navigation under the main navigation sidebar. The index contains all elements which
 * match the given selector. The text of the element gets used as link in the navigation. Sets also the fitting id to
 * each element.
 *
 * bootstrap is loaded to ensure affix() is present at time of calling
 *
 * @module toc
 */
define("toc", ['jquery', 'util', 'bootstrap'], function ($, util, bootstrap) {


    var arrIdMap = [];

    /**
     * Internal helper to build an id for a given dom node
     *
     * @param $node
     * @returns {string}
     */
    getSecureId = function($node) {
        var id = '';
        if($node.attr('id')) {
            id = $node.attr('id');
        }
        else {
            id = $node.text().replace(/(?!\w)[\x00-\xC0]/g, "-");
            var newId = id;
            var intI = 0;
            while(util.inArray(newId, arrIdMap)) {
                newId = id+"_"+(intI++);
            }
            id = newId;
            arrIdMap.push(id);
            $node.attr('id', id);
        }

        return id;
    };


    return /** @alias module:toc */ {

        /**
         * Renders the table of contents
         * @param selector
         */
        render: function(selector){
            if(!$('.sidebar-nav').length) {
                return;
            }

            //handled before?
            if($('#toc-navigation ul').length > 0) {
                return;
            }

            // create the navigation
            var html = '';

            $(selector).each(function () {
                var id = getSecureId($(this));
                html += '<li><a href="#' + id + '">' + $(this).text() + '</a></li>';
            });

            // append the element only if it is not already appended
            $('.sidebar-nav').append($('<div id="toc-navigation" class="toc-navigation-panel" role="navigation">').append($('<ul class="nav">').html(html)));

            // affix toc navigation
            $('#toc-navigation').affix({
                offset: {
                    top: $('#toc-navigation').position().top + 30
                }
            });

            // scroll spy
            $('body').scrollspy({
                target: '#toc-navigation',
                offset: 60
            });

            // resize toc navigation to main navigation
            $(window).resize(function() {
                $('#toc-navigation').css('width', $('#moduleNavigation').width()+15);
                $('#toc-navigation').css('max-height', $(window).height()-60);
            });
            $('#toc-navigation').css('width', $('#moduleNavigation').width()+15);
            $('#toc-navigation').css('max-height', $(window).height()-60);
        },

        /**
         * Removes a single entry from the toc
         * @param id
         */
        removeEntry : function(id) {
            $("a[href=#"+id+"]").parent("li").remove();
        }
    };

});
