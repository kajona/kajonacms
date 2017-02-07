/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Common tooltips
 *
 * @module tooltip
 */
define('tooltip', ['jquery', 'qtip'], function ($, qtip) {
    return /** @alias module:tooltip */ {
        initTooltip : function() {
            $('*[rel=tooltip][title!=""]').qtip({
                position: {
                    viewport: $(window)
                },
                style: {
                    classes: 'qtip-bootstrap'
                }
            });

            //tag tooltips
            $('*[rel=tagtooltip][title!=""]').each( function() {
                $(this).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-bootstrap'
                    },
                    content: {
                        text: $(this).attr("title")+"<div id='tags_"+$(this).data('systemid')+"' data-systemid='"+$(this).data('systemid')+"'></div>"
                    },
                    events: {
                        render: function(event, api) {
                            require(['tags'], function(tags) {
                                tags.loadTagTooltipContent($(api.elements.content).find('div').data('systemid'), "", $(api.elements.content).find('div').attr('id'));
                            })
                        }
                    }
                });
            })
        },

        addTooltip : function(objElement, strText) {
            if(strText) {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-bootstrap'
                    },
                    content : {
                        text: strText
                    }
                });
            }
            else {
                $(objElement).qtip({
                    position: {
                        viewport: $(window)
                    },
                    style: {
                        classes: 'qtip-bootstrap'
                    }
                });
            }
        },

        removeTooltip : function(objElement) {
            $(objElement).qtip('hide');
        }
    };
});
