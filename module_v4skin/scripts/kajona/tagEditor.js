
/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * @module tagEditor
 */
define(["jquery", "jquerytageditor", "v4skin", "workingIndicator", "forms"], function($, tagEditor, v4skin, workingIndicator, forms) {


    return /** @alias module:tagEditor */ {

        /**
         * initializes the tag-editor for a given input element
         * @param strElementId
         * @param strSource
         * @param initialTags
         * @param onChange
         */
        init: function(strElementId, strSource, initialTags, onChange) {

            var objConfig = new v4skin.defaultAutoComplete();

            objConfig.search = function(event, ui) {
                if (event.target.value.length < 2) {
                    event.stopPropagation();
                    return false;
                }
                $(this).closest('ul.tag-editor').parent().find('.loading-feedback').html('<i class="fa fa-spinner fa-spin"></i>');
                workingIndicator.getInstance().start();
            };

            objConfig.response = function(event, ui) {
                $(this).closest('ul.tag-editor').parent().find('.loading-feedback').html('');
                workingIndicator.getInstance().stop();
            };

            objConfig.select = function(event, ui) {
                var found = false;
                $("#"+strElementId+"-list").find('input').each(function(){
                    if ($(this).val() == ui.item.systemid) {
                        found = true;
                    }
                });
                if (!found) {
                    $("#"+strElementId+"-list").append('<input type="hidden" name="'+strElementId+'_id[]" value="' + ui.item.systemid + '" data-title="' + ui.item.title + '" />');
                }
            };

            objConfig.create = function(event, ui) {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item){
                    return $('<li></li>')
                        .data('ui-autocomplete-item', item)
                        .append('<div class=\'ui-autocomplete-item\'>' + item.icon + item.title + '</div>')
                        .appendTo(ul);
                };
            };

            objConfig.source = function(request, response) {
                $.ajax({
                    url: strSource,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        filter: request.term
                    },
                    success: function(resp) {
                        if (resp) {
                            // replace commas
                            for (var i = 0; i < resp.length; i++) {
                                resp[i].title = resp[i].title.replace(/\,/g, '');
                                resp[i].value = resp[i].value.replace(/\,/g, '');
                            }
                        }
                        response.call(this, resp);
                    }
                });
            };

            var $objInput = $("#"+strElementId);
            $objInput.tagEditor({
                initialTags: initialTags,
                forceLowercase: false,
                autocomplete: objConfig,
                onChange: onChange,
                beforeTagSave: function(field, editor, tags, tag, val){
                    var found = false;
                    $("#"+strElementId+"-list").find('input').each(function(){
                        if ($(this).data('title') == val) {
                            found = true;
                        }
                    });
                    if (!found) {
                        return false;
                    }
                },
                beforeTagDelete: function(field, editor, tags, val){
                    $("#"+strElementId+"-list").find('input').each(function(){
                        if ($(this).data('title') == val) {
                            $(this).remove();
                        }
                    });
                }
            });
            $objInput.parent().find('ul.tag-editor').after("<span class='form-control-feedback loading-feedback' style='right: 15px;'><i class='fa fa-keyboard-o'></i></span>");

            forms.addMandatoryRenderingCallback(function() {
                if($objInput.hasClass('mandatoryFormElement')) {
                    $objInput.parent().find('ul.tag-editor').addClass('mandatoryFormElement');
                }
            });

            //hightlight current input
            $('#tageditor_'+strElementId+' .tag-editor').on("click", function () {
                $('#tageditor_'+strElementId).find('ul.tag-editor').addClass("active");
            });

            //set all othter inactive
            $('.tag-editor').on("click", function (e, el) {
                var objOuter = $(this);
                $(".tag-editor.active").each(function() {
                    if($(this).closest('.inputTagEditor').attr('id') != objOuter.closest('.inputTagEditor').attr('id')) {
                        $(this).removeClass('active');
                    }
                });
            });

            //general outer click
            $("*:not(.tag-editor)").on("click", function () {
                if ($(".tag-editor").hasClass("active")) {
                    $(".tag-editor").removeClass("active");
                }
            });


        }
    };


});




