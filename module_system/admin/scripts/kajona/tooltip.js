
//common tooltips
define(['jquery', 'qtip', 'loader'], function ($, qtip, loader) {
    return {
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
                            // TODO problem if we load tags as dependency we get a circular reference
                            //tags.loadTagTooltipContent($(api.elements.content).find('div').data('systemid'), "", $(api.elements.content).find('div').attr('id'));
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
