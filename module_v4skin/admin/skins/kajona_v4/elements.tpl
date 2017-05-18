/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$											*
********************************************************************************************************/

This skin-file is used for the Kajona v4 admin skin and can be used as a sample file to create
your own cool skin. Just modify the sections you'd like to. Don't forget the css file and the basic
templates!


---------------------------------------------------------------------------------------------------------
-- GRID ELEMENTS ----------------------------------------------------------------------------------------

<grid_header>
<div class="grid" data-kajona-pagenum="%%curPage%%" data-kajona-elementsperpage="%%elementsPerPage%%">
    <ul class="thumbnails gallery %%sortable%%">
</grid_header>

<grid_footer>
    </ul>
</div>
<script type="text/javascript">
require(["jquery", "ajax", "util"], function($, ajax, util) {
    $(function() {
        $('.grid > ul.sortable').sortable( {
            items: 'li[data-systemid!=""]',
            handle: 'div.thumbnail',
            cursor: 'move',
            start: function(event, ui) {
                oldPos = ui.item.index()
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index()) {

                    //calc the page-offset
                    var intCurPage = $(this).parent(".grid").attr("data-kajona-pagenum");
                    var intElementsPerPage = $(this).parent(".grid").attr("data-kajona-elementsperpage");

                    var intPagingOffset = 0;
                    if(intCurPage > 1 && intElementsPerPage > 0)
                        intPagingOffset = (intCurPage*intElementsPerPage)-intElementsPerPage;

                    ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1+intPagingOffset);
                }
                oldPos = 0;
            },
            delay: util.isTouchDevice() ? 500 : 0
        });
        $('.grid > ul.sortable > li[data-systemid!=""] > div.thumbnail ').css("cursor", "move");
    });
});
</script>
</grid_footer>

<grid_entry>
<li class="col-md-3 col-xs-3 %%cssaddon%%" data-systemid="%%systemid%%" >
    <div class="thumbnail" %%clickaction%% >
        <h5 >%%title%%</h5>
        <div class="contentWrapper" style="background: url(%%image%%) center no-repeat; background-size: cover;">
            <div class="metainfo">
                <div>%%info%%</div>
                <div>%%subtitle%%</div>
            </div>
        </div>
        <div class="actions">
            %%actions%%
        </div>
    </div>
</li>
</grid_entry>

---------------------------------------------------------------------------------------------------------
-- LIST ELEMENTS ----------------------------------------------------------------------------------------

Optional Element to start a list
<list_header>
<table class="table admintable table-striped-tbody">
</list_header>

Header to use when creating drag n dropable lists. places an id an loads the needed js-scripts in the
background using the ajaxHelper.
Loads the script-helper and adds the table to the drag-n-dropable tables getting parsed later
<dragable_list_header>
<script type="text/javascript">

require(['listSortable'], function(sortManager) {
    sortManager.init('%%listid%%', '%%targetModule%%', %%bitMoveToTree%%);
}) ;


</script>
<div id='%%listid%%_prev' class='alert alert-info divPageTarget'>[lang,commons_list_sort_prev,system]</div>
<table id="%%listid%%" class="table admintable table-striped-tbody" data-kajona-pagenum="%%curPage%%" data-kajona-elementsperpage="%%elementsPerPage%%">

</dragable_list_header>

Optional Element to close a list
<list_footer>
</table>
</list_footer>

<dragable_list_footer>
</table>
<div id='%%listid%%_next' class='alert alert-info divPageTarget'>[lang,commons_list_sort_next,system]</div>
</dragable_list_footer>


The general list will replace all other list types in the future.
It is responsible for rendering the different admin-lists.
Currently, there are two modes: with and without a description.

<generallist_checkbox>
    <input type="checkbox" name="kj_cb_%%systemid%%" id="kj_cb_%%systemid%%" onchange="require('lists').updateToolbar();">
</generallist_checkbox>

<generallist>
    <tbody class="%%cssaddon%%">
        <tr data-systemid="%%listitemid%%" data-deleted="%%deleted%%">
            <td class="treedrag"></td>
            <td class="listsorthandle"></td>
            <td class="listcheckbox">%%checkbox%%</td>
            <td class="listimage">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
    </tbody>
</generallist>


<generallist_desc>
    <tbody class="generalListSet %%cssaddon%%">
        <tr data-systemid="%%listitemid%%" data-deleted="%%deleted%%">
            <td class="treedrag"></td>
            <td class="listsorthandle"></td>
            <td class="listcheckbox">%%checkbox%%</td>
            <td class="listimage">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
        <tr>
            <td colspan="4" class="description"></td>
            <td colspan="3" class="description">%%description%%</td>
        </tr>
    </tbody>
</generallist_desc>



<batchactions_wrapper>
<div class="batchActionsWrapper">
    %%entries%%
    <div class="batchActionsProgress" style="display: none;">
        <h5 class="progresstitle"></h5>
        <span class="batch_progressed">0</span> / <span class="total">0</span>
        <div class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div>
        </div>
        <div class="batchaction_messages">
            <ul class="batchaction_messages_list"></ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    require(["jquery", "lists"], function($, lists) {
        $("#kj_cb_batchActionSwitch").on('click', function() { lists.toggleAllFields(); lists.updateToolbar(); });
        lists.strConfirm = '[lang,commons_batchaction_confirm,pages]';
        lists.strDialogTitle = '[lang,commons_batchaction_title,pages]';
        lists.strDialogStart = '[lang,commons_start,pages]';
        lists.updateToolbar();
    });
</script>
</batchactions_wrapper>

<batchactions_entry>
    <a href="#" onclick="%%onclick%% return false;" title="%%title%%" rel="tooltip">%%icon%%</a>
</batchactions_entry>

Divider to split up a page in logical sections
<divider>
<hr />
</divider>

data list header. Used to open a table to print data
<datalist_header>
<table class="table table-striped table-condensed kajona-data-table %%cssaddon%%">
</datalist_header>

<datalist_header_tbody>
    <table class="table table-striped-tbody table-condensed kajona-data-table %%cssaddon%%">
</datalist_header_tbody>

data list footer. at the bottom of the datatable
<datalist_footer>
    </table>
    <script type="text/javascript">
        require(["jquery-floatThread"], function() {
            $('table.kajona-data-table:not(.kajona-data-table-ignore-floatthread)').floatThead({
                scrollingTop: $("body.dialogBody").size() > 0 ? 0 : 70,
                useAbsolutePositioning: true
            });
        });
    </script>
</datalist_footer>

One Column in a row (header record) - the header, the content, the footer
<datalist_column_head_header>
    <thead><tr>
</datalist_column_head_header>

<datalist_column_head>
    <th class="%%class%%" %%addons%%>%%value%%</th>
</datalist_column_head>

<datalist_column_head_footer>
    </tr></thead>
</datalist_column_head_footer>

One Column in a row (data record) - the header, the content, the footer, providing the option of two styles
<datalist_column_header>
	<tr data-systemid="%%systemid%%">
</datalist_column_header>

<datalist_column_header_tbody>
    <tbody>
    <tr data-systemid="%%systemid%%">
</datalist_column_header_tbody>

<datalist_column>
    <td class="%%class%%">%%value%%</td>
</datalist_column>

<datalist_column_footer>
	</tr>
</datalist_column_footer>

<datalist_column_footer_tbody>
    </tr>
    </tbody>
</datalist_column_footer_tbody>




---------------------------------------------------------------------------------------------------------
-- ACTION ELEMENTS --------------------------------------------------------------------------------------

Element containing one button / action, multiple put together, e.g. to edit or delete a record.
To avoid side-effects, no line-break in this case -> not needed by default, but in classics-style!
<list_button><span class="listButton">%%content%%</span></list_button>

---------------------------------------------------------------------------------------------------------
-- FORM ELEMENTS ----------------------------------------------------------------------------------------

<form_start>
<form name="%%name%%" id="%%name%%" method="%%method%%" action="%%action%%" enctype="%%enctype%%" onsubmit="%%onsubmit%%" class="form-horizontal">
    <script type="text/javascript">
        require(["forms"], function(forms) {
            $(function() {
                forms.initForm('%%name%%');
                forms.changeLabel = '[lang,commons_form_entry_changed,system]';
                forms.changeConfirmation = '[lang,commons_form_entry_changed_conf,system]';
            });
        });
    </script>
</form_start>

<form_close>
</form>
</form_close>

Dropdown
<input_dropdown>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <select data-placeholder="%%dataplaceholder%%" name="%%name%%" id="%%name%%" class="form-control %%class%%" %%disabled%% %%addons%% data-kajona-instantsave="%%instantEditor%%" >%%options%%</select>
        </div>
        <div class="col-sm-2 form-opener">
            %%opener%%
        </div>
    </div>
</input_dropdown>

<input_dropdown_row>
<option value="%%key%%">%%value%%</option>
</input_dropdown_row>

<input_dropdown_row_selected>
<option value="%%key%%" selected="selected">%%value%%</option>
</input_dropdown_row_selected>


Multiselect
<input_multiselect>
    <div class="form-group">
        <label for="%%name%%[]" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <select size="7" name="%%name%%[]" id="%%name%%" class="form-control %%class%%" multiple="multiple" %%disabled%% %%addons%%>%%options%%</select>
        </div>
    </div>
</input_multiselect>

<input_multiselect_row>
    <option value="%%key%%">%%value%%</option>
</input_multiselect_row>

<input_multiselect_row_selected>
    <option value="%%key%%" selected="selected">%%value%%</option>
</input_multiselect_row_selected>

Toggle Button-Bar
<input_toggle_buttonbar>
    <div class="form-group">
        <label for="%%name%%[]" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <div class="btn-group buttonbar" data-toggle="buttons">
                %%options%%
            </div>
        </div>
    </div>
</input_toggle_buttonbar>

<input_toggle_buttonbar_button>
    <label class="btn btn-primary %%btnclass%%">
        <input type="%%type%%" name="%%name%%[]" value="%%key%%" %%disabled%% %%addons%%> %%value%%
    </label>
</input_toggle_buttonbar_button>

<input_toggle_buttonbar_button_selected>
    <label class="btn btn-primary active %%btnclass%%">
        <input type="%%type%%" name="%%name%%[]" value="%%key%%" checked="checked" %%disabled%% %%addons%%> %%value%%
    </label>
</input_toggle_buttonbar_button_selected>


Radiogroup
<input_radiogroup>
    <div class="form-group %%class%%">
        <label class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            %%radios%%
        </div>
    </div>
</input_radiogroup>


<input_radiogroup_row>
    <div class="radio">
        <label>
            <input type="radio" name="%%name%%" id="%%name%%_%%key%%" value="%%key%%" class="%%class%%" %%checked%% %%disabled%%>
            %%value%%
        </label>
    </div>
</input_radiogroup_row>


Checkbox
<input_checkbox>
<div class="form-group">
    <label for="%%name%%" class="col-sm-3 control-label"></label>
    <div class="col-sm-6">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="%%name%%" value="checked" id="%%name%%" class="%%class%%" %%checked%% %%readonly%%>
                %%title%%
            </label>
        </div>
    </div>
</div>
</input_checkbox>

Toggle_On_Off (using bootstrap-switch.org)
<input_on_off_switch>
    <script type="text/javascript">
        require(["bootstrap-switch"], function(){
            window.setTimeout(function() {
                var divId = '%%name%%';
                divId = '#' + divId.replace( /(:|\.|\[|\])/g, "\\$1" );
                $(divId).bootstrapSwitch();
                $(divId).on('switchChange.bootstrapSwitch', function (event, state) {
                    %%onSwitchJSCallback%%
                });

            }, 200);
        });
    </script>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="%%name%%">%%title%%</label>
        <div class="col-sm-6">
            <div id="div_%%name%%" class="" >
                <input type="checkbox" name="%%name%%" value="checked" id="%%name%%" class="%%class%%" %%checked%% %%readonly%% data-size="small" data-on-text="<i class='fa fa-check fa-white' ></i>" data-off-text="<i class='fa fa-times'></i>">
            </div>
        </div>
    </div>
</input_on_off_switch>

Regular Hidden-Field
<input_hidden>
	<input name="%%name%%" value="%%value%%" type="hidden" id="%%name%%">
</input_hidden>

Regular Text-Field
<input_text>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6 %%class%%">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control" %%readonly%% data-kajona-instantsave="%%instantEditor%%">
        </div>
        <div class="col-sm-2 form-opener">
            %%opener%%
        </div>
    </div>
</input_text>

Textarea
<input_textarea>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6 %%class%%">
            <textarea name="%%name%%" id="%%name%%" class="form-control" rows="%%numberOfRows%%" %%readonly%%>%%value%%</textarea>
        </div>
    </div>
</input_textarea>

Regular Password-Field
<input_password>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6 %%class%%">
            <input type="password" autocomplete="off" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control" %%readonly%%>
        </div>
    </div>
</input_password>

Upload-Field
<input_upload>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <input type="file" name="%%name%%" id="%%name%%" class="form-control %%class%%">
            <span class="help-block">
                %%maxSize%%
                <a href="%%fileHref%%">%%fileName%%</a>
            </span>
        </div>
    </div>
</input_upload>

Download-Field
<input_upload_disabled>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <div class="form-control %%class%%">
                <a href="%%fileHref%%" id="%%name%%">%%fileName%%</a>
            </div>
        </div>
    </div>
</input_upload_disabled>

Upload-Field for multiple files with progress bar
<input_upload_multiple>

    <div id="%%name%%" class="fileupload-wrapper">
            <div class="fileupload-buttonbar">

                <span class="btn btn-default fileinput-button">
                    <i class="fa fa-plus-square"></i>
                    <span>[lang,mediamanager_upload,mediamanager]</span>
                    <input type="file" name="%%name%%" multiple>
                </span>

                <button type="submit" class="btn btn-default start" style="display: none;">
                    <i class="fa fa-upload"></i>
                    <span>[lang,upload_multiple_uploadFiles,mediamanager]</span>
                </button>

                <button type="reset" class="btn btn-default cancel" style="display: none;">
                    <i class="fa fa-ban"></i>
                    <span>[lang,upload_multiple_cancel,mediamanager]</span>
                </button>

                <span class="fileupload-process"></span>
                <div class="alert alert-info" id="drop-%%uploadId%%">
                    [lang,upload_dropArea,mediamanager]<br />
                     %%allowedExtensions%%
                </div>
            </div>

            <div class="fileupload-progress" style="display: none;">

                <div class="progress" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;"></div>
                </div>

                <div class="progress-extended">&nbsp;</div>
            </div>

        <table class="table admintable table-striped-tbody files" id="files-%%uploadId%%"></table>
    </div>

<script type="text/javascript">
    require(['tmpl'], function() {
        require(['jquery', 'jquery.iframe-transport', 'jquery.fileupload', 'jquery.fileupload-process', 'jquery.fileupload-ui'], function($) {
            var filesToUpload = 0;
            $('#%%name%%').fileupload({
                url: '_webpath_/xml.php?admin=1&module=mediamanager&action=fileUpload',
                dataType: 'json',
                dropZone: $('#%%name%%'),
                pasteZone: $(document),
                autoUpload: false,
                paramName : '%%name%%',
                filesContainer: $('#files-%%uploadId%%'),
                formData: [
                    {name: 'systemid', value: '%%mediamanagerRepoId%%'},
                    {name: 'inputElement', value : '%%name%%'},
                    {name: 'jsonResponse', value : 'true'}
                ],
                messages: {
                    maxNumberOfFiles: 'Maximum number of files exceeded',
                    acceptFileTypes: "[lang,upload_fehler_filter,mediamanager]",
                    maxFileSize: "[lang,upload_multiple_errorFilesize,mediamanager]",
                    minFileSize: 'File is too small'
                },
                maxFileSize: %%maxFileSize%%,
                acceptFileTypes: %%acceptFileTypes%%,
                uploadTemplateId: null,
                downloadTemplateId: null,
                uploadTemplate: function (o) {
                    var rows = $();
                    $.each(o.files, function (index, file) {
                        var row = $('<tbody class="template-upload fade"><tr>' +
                                    '<td><span class="preview"></span></td>' +
                                    '<td><p class="name"></p>' +
                                    '<div class="error"></div>' +
                                    '</td>' +
                                    '<td><p class="size"></p>' +
                                    '<div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div>' +
                                    '</td>' +
                                    '<td>' +
                                    (!index && !o.options.autoUpload ?
                                            '<button class="btn start " disabled style="display: none;">Start</button>' : '') +
                                    (!index ? '<button class="btn cancel ">[lang,upload_multiple_cancel,mediamanager]</button>' : '') +
                                    '</td>' +
                                    '</tr></tbody>');
                        row.find('.name').text(file.name);
                        row.find('.size').text(o.formatFileSize(file.size));
                        if (file.error) {
                            row.find('.error').text(file.error);
                        }
                        rows = rows.add(row);
                    });
                    return rows;
                }
            })
            .bind('fileuploadadded', function (e, data) {
                $(this).find('.fileupload-buttonbar button.start').css('display', '');
                $(this).find('.fileupload-buttonbar button.cancel').css('display', '');
                $(this).find('.fileupload-progress').css('display', '');
                filesToUpload++;
            })
            .bind('fileuploadfail', function (e, data) {
                filesToUpload--;
                $(this).trigger('kajonahideelements');
            })
            .bind('fileuploaddone', function (e, data) {
                filesToUpload--;
                $(this).trigger('kajonahideelements');
            })
            .bind('fileuploadstop', function (e) {
                $(this).trigger('kajonahideelements');
                document.location.reload();
            })
            .bind('kajonahideelements', function() {
                if(filesToUpload == 0) {
                    $(this).find('.fileupload-buttonbar button.start').css('display', 'none');
                    $(this).find('.fileupload-buttonbar button.cancel').css('display', 'none');
                    $(this).find('.fileupload-progress').css('display', 'none');
                }
            });
        });

        $(document).bind('dragover', function (e) {
            var dropZone = $('#%%name%%'),
                timeout = window.dropZoneTimeout;
            if (!timeout) {
                dropZone.addClass('in');

            } else {
                clearTimeout(timeout);
            }
            var found = false,
                node = e.target;
            do {
                if (node === dropZone[0]) {
                    found = true;
                    break;
                }
                node = node.parentNode;
            } while (node != null);
            if (found) {
                dropZone.addClass('hover');
                $('#drop-%%uploadId%%').removeClass('alert-info').addClass('alert-success');
            } else {
                dropZone.removeClass('hover');
                $('#drop-%%uploadId%%').addClass('alert-info').removeClass('alert-success');
            }
            window.dropZoneTimeout = setTimeout(function () {
                window.dropZoneTimeout = null;
                dropZone.removeClass('in hover');
                $('#drop-%%uploadId%%').addClass('alert-info').removeClass('alert-success');
            }, 100);
        });
    });
</script>


    </style>


</input_upload_multiple>

Regular Submit-Button
<input_submit>
        <button type="submit" class="btn btn-default savechanges %%class%%" name="%%name%%" value="%%value%%" %%disabled%% %%eventhandler%%>
            <span class="btn-text">%%value%%</span>
            <span class="statusicon"></span>
        </button>
</input_submit>


<input_submit_wrapper>
    <div class="form-group">
        <label class="col-sm-3 control-label"></label>
        <div class="col-sm-6">
            %%button%%
        </div>
    </div>
</input_submit_wrapper>


An easy date-selector
If you want to use the js-date-picker, leave %%calendarCommands%% at the end of the section
in addition, a container for the calendar is needed. Use %%calendarContainerId%% as an identifier.
<input_date_simple>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                <input id="%%calendarId%%" name="%%calendarId%%" class="form-control %%class%%" size="16" type="text" value="%%valuePlain%%" %%readonly%%>
            </div>
            <script>
                require(["bootstrap-datepicker"], function() {
                    require(["bootstrap-datepicker-%%calendarLang%%", "util"], function(datepicker, util){
                        $('#%%calendarId%%').datepicker({
                            format: util.transformDateFormat('%%dateFormat%%', "bootstrap-datepicker"),
                            weekStart: 1,
                            autoclose: true,
                            language: '%%calendarLang%%',
                            todayHighlight: true,
                            container: '#content',
                            todayBtn: "linked",
                            daysOfWeekHighlighted: "0,6",
                            calendarWeeks: true
                        });

                        if($('#%%calendarId%%').is(':focus')) {
                            $('#%%calendarId%%').blur();
                            $('#%%calendarId%%').focus();
                        }
                    });
                });
            </script>
        </div>
    </div>

</input_date_simple>

<input_datetime_simple>

    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-2">
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                <input id="%%calendarId%%" name="%%calendarId%%" class="form-control" size="16" type="text" value="%%valuePlain%%" %%readonly%%>
            </div>
        </div>

        <div class="col-sm-2 form-inline">
            <div class="form-group">

                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                    <input name="%%titleHour%%" id="%%titleHour%%" type="text" class="form-control %%class%%" size="2" maxlength="2" value="%%valueHour%%" />
                </div>
                <input name="%%titleMin%%" id="%%titleMin%%" type="text" class="form-control %%class%%" size="2" maxlength="2" value="%%valueMin%%" />
            </div>
        </div>
        <div class="col-sm-1">
        </div>
        <script>
            require(["bootstrap-datepicker"], function() {
                require(["bootstrap-datepicker-%%calendarLang%%", "util"], function(datepicker, util){
                    $('#%%calendarId%%').datepicker({
                        format: util.transformDateFormat('%%dateFormat%%', "bootstrap-datepicker"),
                        weekStart: 1,
                        autoclose: true,
                        language: '%%calendarLang%%',
                        todayHighlight: true,
                        container: '#content',
                        todayBtn: "linked",
                        daysOfWeekHighlighted: "0,6",
                        calendarWeeks: true
                    });

                    if($('#%%calendarId%%').is(':focus')) {
                        $('#%%calendarId%%').blur();
                        $('#%%calendarId%%').focus();
                    }
                });
            });
        </script>
    </div>
</input_datetime_simple>

<input_objectlist>
    <div class="form-group form-list">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText">
            <table id="%%name%%" data-name="%%name%%" class="table table-striped form-control">
                <colgroup>
                    <col width="20" />
                    <col width="*" />
                    <col width="20" />
                </colgroup>
                <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td class="icon-cell"></td>
                </tr>
                </tfoot>
                <tbody>
                %%table%%
                </tbody>
            </table>
        </div>
        <div class="col-sm-2 form-opener">
            %%addLink%%
        </div>
    </div>
</input_objectlist>

<input_objectlist_row>
    <tr>
        <td class="listimage">%%icon%%</td>
        <td><div class="smaller">%%path%%</div> %%displayName%% <input type="hidden" name="%%name%%[]" value="%%value%%" /></td>
        <td class="icon-cell">%%removeLink%%</td>
    </tr>
</input_objectlist_row>

<input_tageditor>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText inputTagEditor">
            <input type="text" id="%%name%%" data-name="%%name%%" style="display:none" />
        </div>
    </div>
    <script type="text/javascript">
        require(["jquery", "jquerytageditor"], function($){
            var onChange = %%onChange%%;
            $("#%%name%%").tagEditor({
                initialTags: %%values%%,
                forceLowercase: false,
                onChange: onChange
            });

            onChange("#%%name%%", $("#%%name%%").tagEditor('getTags')[0].editor, %%values%%);
        });
    </script>
</input_tageditor>

<input_objecttags>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText inputTagEditor" id="tageditor_%%name%%">
            <input type="text" id="%%name%%" data-name="%%name%%" style="display:none" class="form-control" />
            <div id="%%name%%-list">%%data%%</div>
        </div>
    </div>
    <script type="text/javascript">
        require(["tagEditor"], function(tagEditor) {
            tagEditor.init('%%name%%', '%%source%%', %%values%%, %%onChange%%);
        });
    </script>
</input_objecttags>

<input_container>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText">
            <div id="%%name%%" class="inputContainer %%class%%">
                %%elements%%
            </div>
        </div>

        <div class="col-sm-2 form-opener">
            %%opener%%
        </div>
    </div>
</input_container>

<input_container_row>
    <div class="inputContainerPanel">%%element%%</div>
</input_container_row>

A page-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_pageselector-tag and make sure, that you
have a surrounding div with class "ac_container" and a div with id "%%name%%_container" and class
"ac_results" inside the "ac_container", to generate a resultlist
<input_pageselector>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control %%class%%" %%readonly%% data-kajona-instantsave="%%instantEditor%%" >
        </div>
        <div class="col-sm-2 form-opener">
            %%opener%%
            %%ajaxScript%%
        </div>
    </div>
</input_pageselector>

<input_userselector>
<div class="form-group">
    <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

    <div class="col-sm-6">
        <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control %%class%%" %%readonly%% >
        <input type="hidden" id="%%name%%_id" name="%%name%%_id" value="%%value_id%%" />
    </div>
    <div class="col-sm-2 form-opener">
        %%opener%%
        %%ajaxScript%%
    </div>
</div>
</input_userselector>

A list of checkbox or radio input elements
<input_checkboxarray>
    <div class="form-group form-list">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText">
            <div id="%%name%%" class="inputContainer %%class%%">
                %%elements%%
            </div>
        </div>
    </div>
</input_checkboxarray>

<input_checkboxarray_checkbox>
    <div class="%%type%%%%inline%%">
        <label><input type="%%type%%" name="%%name%%" value="%%value%%" %%checked%% %%readonly%% /> %%title%%</label>
    </div>
</input_checkboxarray_checkbox>

A list of checkbox for object elements
<input_checkboxarrayobjectlist>
    <div class="form-group form-checkboxarraylist form-list">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText">
            <div id="%%name%%" class="inputContainer">
                %%elements%%
            </div>
        </div>
        <div class="col-sm-2 form-opener">
            %%addLink%%
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"></label>
        <div class="col-sm-6">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="checkAll_%%name%%" id="checkAll_%%name%%" %%readonly%%>
                    [lang,commons_select_all,system]
                </label>
            </div>
        </div>
    </div>

    <script type='text/javascript'>
        require(["jquery"], function($) {
            $("input:checkbox[name='checkAll_%%name%%']").on('change', function() {
                var checkBoxes = $("input:checkbox[name^='%%name%%']");
                checkBoxes.prop('checked', $("input:checkbox[name='checkAll_%%name%%']").prop('checked'));
            });
        });
    </script>
</input_checkboxarrayobjectlist>

<input_checkboxarrayobjectlist_row>
    <tbody>
        <tr data-systemid="%%systemid%%">
            <td class="listcheckbox"><input type="checkbox" name="%%name%%[%%systemid%%]" data-systemid="%%systemid%%" %%checked%% %%readonly%%></td>
            <td class="listimage">%%icon%%</td>
            <td class="title">
                <div class="small text-muted">%%path%%</div>
                %%title%%
            </td>
        </tr>
    </tbody>
</input_checkboxarrayobjectlist_row>

---------------------------------------------------------------------------------------------------------
-- MISC ELEMENTS ----------------------------------------------------------------------------------------
Used to fold elements / hide/unhide elements
<layout_folder>
<div id="%%id%%" class="contentFolder %%display%%">%%content%%</div>
</layout_folder>

A precent-beam to illustrate proportions
<percent_beam>
    <div class="progress">
        <div class="progress-bar %%animationClass%% active" role="progressbar" aria-valuenow="%%percent%%" aria-valuemin="0" aria-valuemax="100" style="width: %%percent%%%;">%%percent%%%</div>
    </div>
</percent_beam>

A fieldset to structure logical sections
<misc_fieldset>
<fieldset class="%%class%%" data-systemid="%%systemid%%"><legend>%%title%%</legend><div>%%content%%</div></fieldset>
</misc_fieldset>

<graph_container>
<div class="graphBox">%%imgsrc%%</div>
</graph_container>


<iframe_container>
    <div id="%%iframeid%%_loading" class="loadingContainer loadingContainerBackground"></div>
    <iframe src="%%iframesrc%%" id="%%iframeid%%" class="seamless" width="100%" height="100%" frameborder="0" seamless ></iframe>

    <script type='text/javascript'>
        require(["jquery"], function($) {
            $(document).ready(function(){
                var frame = $('iframe#%%iframeid%%');
                frame.load(function() {
                    $('.tab-content.fullHeight iframe').each(function() {

                        var frame = document.getElementById('%%iframeid%%');
                        innerDoc = (frame.contentDocument) ?
                            frame.contentDocument : frame.contentWindow.document;

                        var intHeight = (innerDoc.body.scrollHeight + 10);

                        if($(this).height() < intHeight) {
                            $(this).height(intHeight);
                        }
                    });
                });

            });
        });
    </script>
</iframe_container>


<tabbed_content_wrapper>
    <ul class="nav nav-tabs" id="%%id%%">
        %%tabheader%%
    </ul>

    <div class="tab-content %%classaddon%%">
        %%tabcontent%%
    </div>
</tabbed_content_wrapper>

<tabbed_content_tabheader>
    <li class="%%classaddon%%"><a href="" data-target="#%%tabid%%" data-href="%%href%%" data-toggle="tab">%%tabtitle%%</a></li>
</tabbed_content_tabheader>

<tabbed_content_tabcontent>
    <div class="tab-pane fade %%classaddon%%" id="%%tabid%%" role="tabpanel">
        %%tabcontent%%
    </div>
</tabbed_content_tabcontent>



---------------------------------------------------------------------------------------------------------
-- SPECIAL SECTIONS -------------------------------------------------------------------------------------

The login-Form is being displayed, when the user has to log in.
Needed Elements: %%error%%, %%form%%
<login_form>
<div class="alert alert-danger" id="loginError">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <p>%%error%%</p>
</div>
%%form%%
<script type="text/javascript">
	if (navigator.cookieEnabled == false) {
	    document.getElementById("loginError").innerHTML = "%%loginCookiesInfo%%";
	}
    require(["jquery"], function($) {
        if ($('#loginError > p').html() == "") {
            $('#loginError').remove();
        }
    });
</script>
<noscript><div class="alert alert-danger">%%loginJsInfo%%</div></noscript>
</login_form>

Part to display the login status, user is logged in
<logout_form>
<div class="dropdown userNotificationsDropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-user" id="icon-user"><span class="badge badge-info" id="userNotificationsCount">-</span></i> <span class="username">%%name%%</span>
    </a>
    <ul class="dropdown-menu generalContextMenu" role="menu">
        <li class="dropdown-submenu">
            <a tabindex="-1" href="#"><i class='fa fa-envelope'></i> [lang,modul_titel,messaging]</a>
            <ul class="dropdown-menu sub-menu" id="messagingShortlist"></ul>
        </li>

        <!-- messages will be inserted here -->
        <li class="divider"></li>
        <li class="dropdown-submenu">
            <a tabindex="-1" href="#"><i class='fa fa-tag'></i> [lang,modul_titel,tags]</a>
            <ul class="dropdown-menu sub-menu" id="tagsSubemenu"></ul>
        </li>
        <li class="divider"></li>
        <li><a href="%%dashboard%%"><i class='fa fa-home'></i> %%dashboardTitle%%</a></li>
        <li class="divider"></li>
        <li><a href="#" onclick="window.print();"><i class='fa fa-print'></i> %%printTitle%%</a></li>
        <li class="divider"></li>
        <li><a href="%%profile%%"><i class='fa fa-user'></i> %%profileTitle%%</a></li>
        <li class="divider"></li>
        <li><a href="%%logout%%"><i class="fa fa-power-off"></i> %%logoutTitle%%</a></li>
    </ul>
</div>
<script type="text/javascript">
    require(['jquery', 'v4skin'], function($, v4skin){
        if(%%renderMessages%%) {
            $(function() {
                v4skin.messaging.properties = {
                    notification_title : '[lang,messaging_notification_title,messaging]',
                    notification_body : '[lang,messaging_notification_body,messaging]',
                    show_all : '[lang,action_show_all,messaging]'
                };
                v4skin.messaging.pollMessages()
            });
        }
        else {
            $('#messagingShortlist').closest("li").hide();
        }

        if(%%renderTags%%) {
            $(function() {
                v4skin.properties.tags.show_all = '[lang,action_show_all,tags]';
                v4skin.initTagMenu();
            });
        }
        else {
            $('#tagsSubemenu').closest("li").hide();
        }
    });
</script>
</logout_form>

Shown, wherever the attention of the user is needed
<warning_box>
    <div class="alert %%class%%">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        %%content%%
    </div>
</warning_box>

Renders a toc navigation
<toc_navigation>
    <script type="text/javascript">
        require(['jquery', 'toc'], function($, toc) {
            $(document).ready(function(){
                toc.render("%%selector%%");
            });
        });
    </script>
</toc_navigation>

Used to print plain text
<text_row>
<p class="%%class%%">%%text%%</p>
</text_row>

Used to print plaintext in a form
<text_row_form>
    <div class="form-group">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <span class="help-block %%class%%">%%text%%</span>
        </div>
    </div>
</text_row_form>

Used to print headline in a form
<headline_form>
    <%%level%% class="%%class%%">%%text%%</%%level%%>
</headline_form>

---------------------------------------------------------------------------------------------------------
-- RIGHTS MANAGEMENT ------------------------------------------------------------------------------------

The following sections specify the layout of the rights-mgmt

<rights_form_header>
    <div>
        %%desc%% %%record%% <br />
        <a href="javascript:require('permissions').toggleEmtpyRows('[lang,permissions_toggle_visible,system]', '[lang,permissions_toggle_hidden,system]', '#rightsForm tr');" id="rowToggleLink" class="rowsVisible">[lang,permissions_toggle_visible,system]</a><br /><br />
    </div>
    <div id="responseContainer">
    </div>
</rights_form_header>

<rights_form_form>
    <table class="table admintable table-striped kajona-data-table">
        <thead>
        <tr class="">
            <th>&nbsp;</th>
            <th>%%title0%%</th>
            <th>%%title1%%</th>
            <th>%%title2%%</th>
            <th>%%title3%%</th>
            <th>%%title4%%</th>
            <th>%%title5%%</th>
            <th>%%title6%%</th>
            <th>%%title7%%</th>
            <th>%%title8%%</th>
            <th>%%title9%%</th>
        </tr>
        </thead>
        %%rows%%
    </table>
    <script type="text/javascript">
        require(["jquery-floatThread"], function() {
            $('table.kajona-data-table').floatThead({
                scrollingTop: $("body.dialogBody").size() > 0 ? 0 : 70,
                useAbsolutePositioning: true
            });
        });
    </script>
    %%inherit%%
</rights_form_form>

<rights_form_row>
	<tr>
		<td>%%group%%</td>
		<td>%%box0%%</td>
		<td>%%box1%%</td>
		<td>%%box2%%</td>
		<td>%%box3%%</td>
		<td>%%box4%%</td>
		<td>%%box5%%</td>
		<td>%%box6%%</td>
		<td>%%box7%%</td>
		<td>%%box8%%</td>
		<td>%%box9%%</td>
	</tr>
</rights_form_row>


<rights_form_inherit>
<div class="form-group">
    <label class="col-sm-3 control-label" for="%%name%%"></label>
    <div class="col-sm-6">
        <div class="checkbox">
            <label>
                    <input name="%%name%%" type="checkbox" id="%%name%%" value="1" onclick="this.blur();" onchange="require('permissions').checkRightMatrix();" %%checked%% />
                %%title%%
            </label>
        </div>
    </div>
</div>
</rights_form_inherit>


---------------------------------------------------------------------------------------------------------
-- WYSIWYG EDITOR ---------------------------------------------------------------------------------------

NOTE: This section not just defines the layout, it also inits the WYSIWYG editor. Change settings with care!

The textarea field to replace by the editor. If the editor can't be loaded, a plain textfield is shown instead
<wysiwyg_ckeditor>
<div class="form-group">
    <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
    <div class="col-sm-6">
        <textarea name="%%name%%" id="%%name%%" class="form-control inputWysiwyg" data-kajona-editorid="%%editorid%%">%%content%%</textarea></div><br />
    </div>
</wysiwyg_ckeditor>

A few settings to customize the editor. They are added right into the CKEditor configuration.
Please refer to the CKEditor documentation to see what's possible here
<wysiwyg_ckeditor_inits>
    resize_minWidth : 640,
    filebrowserWindowWidth : 400,
    filebrowserWindowHeight : 500,
    filebrowserImageWindowWidth : 400,
    filebrowserImageWindowWindowHeight : 500,
</wysiwyg_ckeditor_inits>

---------------------------------------------------------------------------------------------------------
-- PATH NAVIGATION --------------------------------------------------------------------------------------

The following sections are used to display the path-navigations, e.g. used by the navigation module

<path_entry>
  <script type="text/javascript"> require(['breadcrumb'], function(breadcrumb) { breadcrumb.appendLinkToPathNavigation('%%pathlink%%') }); </script>
</path_entry>

---------------------------------------------------------------------------------------------------------
-- CONTENT TOOLBARS -------------------------------------------------------------------------------------

Toolbar, prominent in the layout. Rendered to switch between action.
<contentToolbar_wrapper>
    <script type="text/javascript"> require(['contentToolbar'], function(contentToolbar) { %%entries%% }); </script>
</contentToolbar_wrapper>

<contentToolbar_entry>
    contentToolbar.registerContentToolbarEntry(new contentToolbar.Entry('%%entry%%', '%%identifier%%', %%active%%));
</contentToolbar_entry>


Toolbar for the current record, rendered to quick-access the actions of the current record.
<contentActionToolbar_wrapper>
<div class="actionToolbar pull-right">%%content%%</div>
<script type="text/javascript"> require(['contentToolbar'], function(contentToolbar) { contentToolbar.showBar(); }); </script>
</contentActionToolbar_wrapper>

---------------------------------------------------------------------------------------------------------
-- ERROR HANDLING ---------------------------------------------------------------------------------------

<error_container>
    <div class="alert alert-danger">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        <h4 class="alert-heading">%%errorintro%%</h4>
        <ul>
            %%errorrows%%
        </ul>
    </div>
</error_container>

<error_row>
    <li>%%field_errortext%%</li>
</error_row>

---------------------------------------------------------------------------------------------------------
-- PREFORMATTED -----------------------------------------------------------------------------------------

Used to print pre-formatted text, e.g. log-file contents
<preformatted>
    <pre class="code pre-scrollable">%%pretext%%</pre>
</preformatted>

---------------------------------------------------------------------------------------------------------
-- PORTALEDITOR -----------------------------------------------------------------------------------------

The following section is the toolbar of the portaleditor, displayed at top of the page.
The following placeholders are provided by the system:
pe_status_page, pe_status_status, pe_status_autor, pe_status_time
pe_status_page_val, pe_status_status_val, pe_status_autor_val, pe_status_time_val
pe_iconbar, pe_disable
<pe_toolbar>

    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/bootstrap_pe.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_webpath_/[webpath,module_v4skin]/admin/skins/kajona_v4/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->


    <div class="kajona-pe-wrap">
        <div class="modal fade" id="peDialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div id="folderviewDialog_loading" class="peLoadingContainer loadingContainerBackground"></div>
                    <div class="modal-body" id="peDialog_content">
                        <!-- filled by js -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kajona-pe-wrap">
        <div class="modal fade" id="delDialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h3 id="delDialog_title"><!-- filled by js --></h3>
                    </div>
                    <div class="modal-body" id="delDialog_content">
                        <!-- filled by js -->
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-default" data-dismiss="modal" id="delDialog_cancelButton">[lang,dialog_cancelButton,system]</a>
                        <a href="#" class="btn btn-default btn-primary" id="delDialog_confirmButton">confirm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</pe_toolbar>




---------------------------------------------------------------------------------------------------------
-- LANGUAGES --------------------------------------------------------------------------------------------

A single button, represents one language. Put together in the language-switch
<language_switch_button>
    <option value="%%languageKey%%">%%languageName%%</option>
</language_switch_button>

A button for the active language
<language_switch_button_active>
    <option value="%%languageKey%%" selected="selected">%%languageName%%</option>
</language_switch_button_active>

The language switch surrounds the buttons
<language_switch>
    <select id="languageChooser" onchange="%%onchangehandler%%">%%languagebuttons%%</select>
    <script type="text/javascript">require(['switchLanguage']);</script>
</language_switch>

---------------------------------------------------------------------------------------------------------
-- QUICK HELP -------------------------------------------------------------------------------------------

<quickhelp>
    <script type="text/javascript">
        require(['quickhelp', 'bootstrap'], function(quickhelp) {
            quickhelp.setQuickhelp('%%title%%', '%%text%%');
        });
    </script>
</quickhelp>

<quickhelp_button>
</quickhelp_button>

---------------------------------------------------------------------------------------------------------
-- PAGEVIEW ---------------------------------------------------------------------------------------------

<pageview_body>
    <div class="pager">
        <ul class="pagination">
            %%linkBackward%%
            %%pageList%%
            %%linkForward%%
            <li><span>%%nrOfElementsText%% %%nrOfElements%%</span></li>
        </ul>
    </div>
</pageview_body>

<pageview_link_forward>
<li>
    <a href="%%href%%">%%linkText%% &raquo;</a>
</li>
</pageview_link_forward>

<pageview_link_backward>
<li>
    <a href="%%href%%">&laquo; %%linkText%%</a>
</li>
</pageview_link_backward>

<pageview_page_list>
%%pageListItems%%
</pageview_page_list>

<pageview_list_item>
    <li data-kajona-pagenum="%%pageNr%%">
        <a href="%%href%%">%%pageNr%%</a>
    </li>
</pageview_list_item>

<pageview_list_item_active>
    <li data-kajona-pagenum="%%pageNr%%" >
        <a href="%%href%%" class="active">%%pageNr%%</a>
    </li>
</pageview_list_item_active>

---------------------------------------------------------------------------------------------------------
-- WIDGETS / DASHBOAORD  --------------------------------------------------------------------------------
<adminwidget_widget>
    <div class="well well-sm">
        <h2 class="">%%widget_name%%</h2>
        <div class="adminwidgetactions pull-right">%%widget_edit%% %%widget_delete%%</div>
        <div class="additionalNameContent">%%widget_name_additional_content%%</div>
        <div class="contentSeparator"></div>
        <div class="content loadingContainer">
            %%widget_content%%
        </div>
    </div>
</adminwidget_widget>

<dashboard_column_header>
	<div id="%%column_id%%" class="col-md-4 adminwidgetColumn" data-sortable-handle="h2">
</dashboard_column_header>

<dashboard_column_footer>
	</div>
</dashboard_column_footer>

<dashboard_encloser>
	<div class="dbEntry" data-systemid="%%entryid%%">%%content%%</div>
</dashboard_encloser>

<adminwidget_text>
<div>%%text%%</div>
</adminwidget_text>

<adminwidget_separator>
&nbsp;<br />
</adminwidget_separator>

<dashboard_wrapper>
    <div class="row dashBoard">%%entries%%</div>
    <script type="text/javascript">
        require(['dashboard'], function(dashboard){
            dashboard.init();
        });
    </script>
</dashboard_wrapper>

---------------------------------------------------------------------------------------------------------
-- TREE VIEW --------------------------------------------------------------------------------------------

<tree>
    <div id="%%treeId%%" class="treeDiv"></div>
    <script type="text/javascript">
        require(["tree", "loader"], function(tree, loader){

            loader.loadFile(["/core/module_system/scripts/jstree3/dist/themes/default/style.min.css"]);

            tree.toggleInitial('%%treeId%%');

            var jsTree = new tree.jstree();
            jsTree.loadNodeDataUrl = "%%loadNodeDataUrl%%";
            jsTree.rootNodeSystemid = '%%rootNodeSystemid%%';
            jsTree.treeConfig = %%treeConfig%%;
            jsTree.treeId = '%%treeId%%';
            jsTree.treeviewExpanders = %%treeviewExpanders%%;
            jsTree.initiallySelectedNodes = %%initiallySelectedNodes%%;

            jsTree.initTree();
        });
    </script>
</tree>

<treeview>
    <div class="row">
        <div class="col-md-4 treeViewColumn" data-kajona-treeid="%%treeId%%" >
            <div class="treeViewWrapper">
                %%treeContent%%
            </div>
        </div>
        <div class="col-md-8 treeViewContent" data-kajona-treeid="%%treeId%%">
            <div class=""><a href="#" onclick="require('tree').toggleTreeView('%%treeId%%');" title="[lang,treeviewtoggle,system]" rel="tooltip"><i class="fa fa-bars"></i></a></div>
            %%sideContent%%
        </div>
    </div>
</treeview>

The tag-wrapper is the section used to surround the list of tag.
Please make sure that the containers' id is named tagsWrapper_%%targetSystemid%%,
otherwise the JavaScript will fail!
<tags_wrapper>
    <div id="tagsLoading_%%targetSystemid%%" class="loadingContainer"></div>
    <div id="tagsWrapper_%%targetSystemid%%"></div>
    <script type="text/javascript">
        require(["tags"], function(tags) {
            tags.reloadTagList('%%targetSystemid%%', '%%attribute%%');
        });
    </script>
</tags_wrapper>

<tags_tag>
    <span class="label label-default">%%tagname%%</span>
    <script type="text/javascript">
        require(["tooltip"], function(tooltip) {
            tooltip.addTooltip('#icon_%%strTagId%%');
        });
    </script>
</tags_tag>

<tags_tag_delete>
    <span class="label label-default taglabel">%%tagname%% <a href="javascript:require('tags').removeTag('%%strTagId%%', '%%strTargetSystemid%%', '%%strAttribute%%');"> %%strDelete%%</a> %%strFavorite%%</span>
    <script type="text/javascript">
        require(["tooltip"], function(tooltip) {
            tooltip.addTooltip($(".taglabel [rel='tooltip']"));
        });
    </script>
</tags_tag_delete>


A tag-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_tagselector-tag.
<input_tagselector>

    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control %%class%%">
            %%opener%%
        </div>
    </div>

%%ajaxScript%%
</input_tagselector>

The aspect chooser is shown in cases more than one aspect is defined in the system-module.
It containes a list of aspects and provides the possibility to switch the different aspects.
<aspect_chooser>
    <select onchange="require('moduleNavigation').loadNavigation(this.value);">
        %%options%%
    </select>
</aspect_chooser>

<aspect_chooser_entry>
    <option value="%%value%%" %%selected%%>%%name%%</option>
</aspect_chooser_entry>

<tooltip_text>
    <span title="%%tooltip%%" rel="tooltip">%%text%%</span>
</tooltip_text>


---------------------------------------------------------------------------------------------------------
-- CALENDAR ---------------------------------------------------------------------------------------------

<calendar_legend>
    <div class="calendarLegend">%%entries%%</div>
</calendar_legend>

<calendar_legend_entry>
    <div class="%%class%% calendarLegendEntry">%%name%%</div>
</calendar_legend_entry>

<calendar_filter>
    <div id="calendarFilter">
        <form action="%%action%%" method="post">
            <input type="hidden" name="doCalendarFilter" value="set" />
        %%entries%%
        </form>
    </div>
</calendar_filter>

<calendar_filter_entry>
    <div><input type="checkbox" id="%%filterid%%" name="%%filterid%%" onchange="this.form.submit();" %%checked%% /><label for="%%filterid%%">%%filtername%%</label></div>
</calendar_filter_entry>

<calendar_pager>
    <table class="calendarPager">
        <tr>
            <td width="20%" style="text-align: left;">%%backwards%%</td>
            <td width="60%" style="text-align: center; font-weight: bold;">%%center%%</td>
            <td width="20%" style="text-align: right;">%%forwards%%</td>
        </tr>
    </table>
</calendar_pager>

<calendar_wrapper>
    <table class="calendar">%%content%%</table>
</calendar_wrapper>

<calendar_container>
<div id="%%containerid%%"><div class="loadingContainer"></div></div>
</calendar_container>

<calendar_header_row>
    <tr >%%entries%%</tr>
</calendar_header_row>

<calendar_header_entry>
    <td width="14%">%%name%%</td>
</calendar_header_entry>

<calendar_row>
    <tr>%%entries%%</tr>
</calendar_row>

<calendar_entry>
    <td class="%%class%%">
        <div class="calendarHeader">%%date%%</div>
        <div>
            %%content%%
        </div>
    </td>
</calendar_entry>

<calendar_event>
    <div class="%%class%%" id="event_%%systemid%%" onmouseover="require('dashboardCalendar').eventMouseOver('%%highlightid%%')" onmouseout="require('dashboardCalendar').eventMouseOut('%%highlightid%%')">
        %%content%%
    </div>
</calendar_event>

---------------------------------------------------------------------------------------------------------
-- MENU -------------------------------------------------------------------------------------------------
<contextmenu_wrapper>
    <div class="dropdown-menu generalContextMenu %%ddclass%%" role="menu">
        <ul>
            %%entries%%
        </ul>
    </div>
    <script type="text/javascript">
        require(['jquery'], function($) {
            $('.dropdown-menu .dropdown-submenu a').click(function (e) {
                e.stopPropagation();
            });
        });
    </script>
</contextmenu_wrapper>

<contextmenu_entry>
    <li ><a href="%%elementLink%%" onclick="%%elementAction%%">%%elementName%%</a></li>
</contextmenu_entry>

<contextmenu_entry_full>
    <li >%%elementFullEntry%%</li>
</contextmenu_entry_full>

<contextmenu_divider_entry>
    <li class="divider"></li>
</contextmenu_divider_entry>

<contextmenu_submenucontainer_entry>
    <li class="dropdown-submenu" >
        <a href="%%elementLink%%" tabindex="-1">%%elementName%%</a>
        <ul class="dropdown-menu">
            %%entries%%
        </ul>
    </li>
</contextmenu_submenucontainer_entry>

<contextmenu_submenucontainer_entry_full>
    <li class="dropdown-submenu" >
        %%elementFullEntry%%
        <ul class="dropdown-menu">
            %%entries%%
        </ul>
    </li>
</contextmenu_submenucontainer_entry_full>


---------------------------------------------------------------------------------------------------------
-- BACKEND NAVIGATION -----------------------------------------------------------------------------------

<sitemap_wrapper>
      <div class="nav-header">
            %%aspectToggle%%
            Kajona V5
      </div>
    %%level%%
</sitemap_wrapper>


<sitemap_aspect_wrapper>
<div data-kajona-aspectid='%%aspectId%%' id="%%aspectId%%" class='%%class%% aspect-container panel-group'>
%%aspectContent%%
</div>

</sitemap_aspect_wrapper>

<sitemap_combined_entry_header>
    <a data-toggle="collapse" data-parent="#%%aspectId%%" href="#menu_%%systemid%%%%aspectId%%" rel="tooltip" title="%%moduleName%%" data-kajona-module="%%moduleTitle%%">
        <i class="fa %%faicon%%"></i>
    </a>
</sitemap_combined_entry_header>

<sitemap_combined_entry_body>
    <div id="menu_%%systemid%%%%aspectId%%" class="panel-collapse collapse" data-kajona-module="%%moduleTitle%%">
        <div class="panel-body">
            <ul>%%actions%%</ul>
        </div>
    </div>
</sitemap_combined_entry_body>


<sitemap_combined_entry_wrapper>
    <div class="panel panel-default panel-combined">
        <div class="panel-heading">
            <span class="linkcontainer ">
                %%combined_header%%
            </span>
        </div>
        %%combined_body%%
    </div>
</sitemap_combined_entry_wrapper>


<sitemap_module_wrapper>
    <div class="panel panel-default">
        <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#%%aspectId%%" href="#menu_%%systemid%%%%aspectId%%" data-kajona-module="%%moduleTitle%%" >
                %%moduleName%%
            </a>
        </div>
        <div id="menu_%%systemid%%%%aspectId%%" class="panel-collapse collapse" data-kajona-module="%%moduleTitle%%">
            <div class="panel-body">
                <ul>%%actions%%</ul>
            </div>
        </div>
    </div>
</sitemap_module_wrapper>


<sitemap_action_entry>
    <li>%%action%%</li>
</sitemap_action_entry>

<sitemap_divider_entry>
<li class="divider"></li>
</sitemap_divider_entry>

<changelog_heatmap>
    <div class="chart-navigation pull-left"><a href="#" onclick="require('changelog').loadPrevYear();return false;"><i class="kj-icon fa fa-arrow-left"></i></a></div>
    <div class="chart-navigation pull-right"><a href="#" onclick="require('changelog').loadNextYear();return false;"><i class="kj-icon fa fa-arrow-right"></i></a></div>
    <div id='changelogTimeline' style='text-align:center;'></div>

    <script type="text/javascript">
        require(['changelog', 'moment', 'loader', 'util'], function(changelog, moment, loader, util){
            loader.loadFile(['/core/module_system/scripts/d3/calendar-heatmap.css']);

            changelog.lang = %%strLang%%;
            changelog.systemId = "%%strSystemId%%";
            changelog.format = util.transformDateFormat('%%strDateFormat%%', "momentjs");
            changelog.now = moment().endOf('day').toDate();
            changelog.yearAgo = moment().startOf('day').subtract(1, 'year').toDate();
            changelog.selectColumn("right");
            changelog.loadChartData();

            changelog.loadDate("%%strSystemId%%", "%%strLeftDate%%", "left", function(){
                changelog.loadDate("%%strSystemId%%", "%%strRightDate%%", "right", changelog.compareTable);
            });
        });
    </script>
</changelog_heatmap>
