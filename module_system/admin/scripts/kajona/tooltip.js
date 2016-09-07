
//common tooltips
define(['jquery', 'qtip'], function (jq, qtip) {
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
                            KAJONA.admin.loader.loadFile('/core/module_tags/admin/scripts/tags.js', function() {
                                KAJONA.admin.tags.loadTagTooltipContent($(api.elements.content).find('div').data('systemid'), "", $(api.elements.content).find('div').attr('id'));
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
