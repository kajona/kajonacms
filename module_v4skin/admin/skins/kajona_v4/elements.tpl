/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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

                KAJONA.admin.ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1+intPagingOffset);
            }
            oldPos = 0;
        },
        delay: KAJONA.util.isTouchDevice() ? 500 : 0
    });
    $('.grid > ul.sortable > li[data-systemid!=""] > div.thumbnail ').css("cursor", "move");
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
    $(function() {

        var bitMoveToTree = false;
        %%jsInject%%

        var oldPos = null;

        $('#%%listid%%_prev').sortable({
            over: function(event, ui) {
                $(ui.placeholder).hide();
            },
            receive: function(event, ui) {
                $(ui.placeholder).hide();
                var intCurPage = $("#%%listid%%").attr("data-kajona-pagenum");
                var intElementsPerPage = $("#%%listid%%").attr("data-kajona-elementsperpage");

                if(intCurPage > 1) {
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), (intElementsPerPage*(intCurPage-1)), null, function(data, status, jqXHR) {
                        if(status == 'success') {
                            location.reload();
                        }
                        else {
                            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
                        }
                    }, '%%targetModule%%');
                }
                else {
                    ui.sender.sortable("cancel");
                }
            }
        });

        $('#%%listid%%_next').sortable({
            over: function(event, ui) {
                $(ui.placeholder).hide();
            },
            receive: function(event, ui) {
                $(ui.placeholder).hide();
                var intCurPage = $("#%%listid%%").attr("data-kajona-pagenum");
                var intElementsPerPage = $("#%%listid%%").attr("data-kajona-elementsperpage");
                var intOnPage = $('#%%listid%% tbody:has(tr[data-systemid!=""])').length + 1;

                if(intOnPage == intElementsPerPage) {
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), (intElementsPerPage*intCurPage+1), null, function(data, status, jqXHR) {
                        if(status == 'success') {
                            location.reload();
                        }
                        else {
                            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
                        }
                    }, '%%targetModule%%');
                }
                else {
                    ui.sender.sortable("cancel");
                }
            }
        });

        $('#%%listid%%').sortable({
            items: 'tbody:has(tr[data-systemid!=""])',
            handle: 'td.listsorthandle',
            cursor: 'move',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            placeholder: 'group_move_placeholder',
            connectWith: '.divPageTarget',
            start: function(event, ui) {

                if($("#%%listid%%").attr("data-kajona-pagenum") > 1)
                    $('#%%listid%%_prev').css("display", "block");

                if($('#%%listid%% tbody:has(tr[data-systemid!=""])').length == $("#%%listid%%").attr("data-kajona-elementsperpage"))
                    $('#%%listid%%_next').css("display", "block");

                oldPos = ui.item.index();
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index() && !ui.item.parent().is('div') ) {
                    var intOffset = 1;
                    //see, if there are nodes not being sortable - would lead to another offset
                    $('#%%listid%% > tbody').each(function(index) {
                        if($(this).find('tr').data('systemid') == "")
                            intOffset--;
                        if($(this).find('tr').data('systemid') == ui.item.find('tr').data('systemid'))
                            return false;
                    });

                    //calc the page-offset
                    var intCurPage = $("#%%listid%%").attr("data-kajona-pagenum");
                    var intElementsPerPage = $("#%%listid%%").attr("data-kajona-elementsperpage");

                    var intPagingOffset = 0;
                    if(intCurPage > 1 && intElementsPerPage > 0)
                        intPagingOffset = (intCurPage*intElementsPerPage)-intElementsPerPage;

                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), ui.item.index()+intOffset+intPagingOffset, null, null, '%%targetModule%%');
                }
                oldPos = 0;
                $('div.divPageTarget').css("display", "none");
            },
            delay: KAJONA.util.isTouchDevice() ? 500 : 0
        });

        $('#%%listid%% > tbody:has(tr[data-systemid!=""]) > tr').each(function(index) {
            $(this).find("td.listsorthandle").css('cursor', 'move').append("<i class='fa fa-arrows-v'></i>");
            KAJONA.admin.tooltip.addTooltip($(this).find("td.listsorthandle"), "[lang,commons_sort_vertical,system]");

            if(bitMoveToTree) {
                $(this).find("td.treedrag").css('cursor', 'move').addClass("jstree-draggable").append("<i class='fa fa-arrows-h' data-systemid='"+$(this).closest("tr").data("systemid")+"'></i>");
                KAJONA.admin.tooltip.addTooltip($(this).find("td.treedrag"), "[lang,commons_sort_totree,system]");
            }
        });
    });
</script>
<style>.group_move_placeholder { display: table-row; } </style>

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
    <input type="checkbox" name="kj_cb_%%systemid%%" id="kj_cb_%%systemid%%" onchange="KAJONA.admin.lists.updateToolbar();">
</generallist_checkbox>

<generallist>
    <tbody class="%%cssaddon%%">
        <tr data-systemid="%%listitemid%%">
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
        <tr data-systemid="%%listitemid%%">
            <td rowspan="2" class="treedrag"></td>
            <td rowspan="2" class="listsorthandle"></td>
            <td rowspan="2" class="listcheckbox">%%checkbox%%</td>
            <td rowspan="2" class="listimage">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
        <tr>
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
    </div>
</div>
<script type="text/javascript">
    $("#kj_cb_batchActionSwitch").on('click', function() { KAJONA.admin.lists.toggleAllFields(); KAJONA.admin.lists.updateToolbar(); });
    KAJONA.admin.lists.strConfirm = '[lang,commons_batchaction_confirm,pages]';
    KAJONA.admin.lists.strDialogTitle = '[lang,commons_batchaction_title,pages]';
    KAJONA.admin.lists.strDialogStart = '[lang,commons_start,pages]';
    KAJONA.admin.lists.updateToolbar();
</script>
</batchactions_wrapper>

<batchactions_entry>
    <a href="#" onclick="KAJONA.admin.lists.triggerAction('%%title%%', '%%targeturl%%');return false;" title="%%title%%" rel="tooltip">%%icon%%</a>
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
</datalist_footer>

One Column in a row (header record) - the header, the content, the footer
<datalist_column_head_header>
	<tr>
</datalist_column_head_header>

<datalist_column_head>
    <th class="%%class%%">%%value%%</th>
</datalist_column_head>

<datalist_column_head_footer>
	</tr>
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
<form name="%%name%%" id="%%name%%" method="post" action="%%action%%" enctype="%%enctype%%" onsubmit="%%onsubmit%%" class="form-horizontal">
    <script type="text/javascript">$(function() { KAJONA.admin.forms.initForm('%%name%%');  KAJONA.admin.forms.changeLabel = '[lang,commons_form_entry_changed,system]';   KAJONA.admin.forms.changeConfirmation = '[lang,commons_form_entry_changed_conf,system]'; } );</script>
</form_start>

<form_close>
</form>
</form_close>

Dropdown
<input_dropdown>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <select data-placeholder="%%dataplaceholder%%" name="%%name%%" id="%%name%%" class="form-control %%class%%" %%disabled%% %%addons%%>%%options%%</select>
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
            <div class="btn-group" data-toggle="buttons">
                %%options%%
            </div>
        </div>
    </div>
</input_toggle_buttonbar>

<input_toggle_buttonbar_button>
    <label class="btn btn-primary %%btnclass%%">
        <input type="checkbox" name="%%name%%[]" value="%%key%%" %%disabled%% %%addons%%> %%value%%
    </label>
</input_toggle_buttonbar_button>

<input_toggle_buttonbar_button_selected>
    <label class="btn btn-primary active %%btnclass%%">
        <input type="checkbox" name="%%name%%[]" value="%%key%%" checked="checked" %%disabled%% %%addons%%> %%value%%
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
        KAJONA.admin.loader.loadFile("/core/module_v4skin/admin/skins/kajona_v4/js/bootstrap-switch.min.js", function() {
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
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control" %%readonly%%>
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
            </span>
        </div>
    </div>
</input_upload>

Upload-Field for multiple files with progress bar
<input_upload_multiple>

    <div id="%%name%%">
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
                <div class="alert alert-info">
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

        <table class="table admintable table-striped-tbody files"></table>
    </div>

<script type="text/javascript">

    KAJONA.admin.loader.loadFile([
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/css/jquery.fileupload.css",
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/css/jquery.fileupload-ui.css",
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/load-image.min.js",
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/canvas-to-blob.min.js",
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.iframe-transport.js",
        "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload.js"
    ], function() {
        KAJONA.admin.loader.loadFile([
            "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-process.js"
        ], function() {
            KAJONA.admin.loader.loadFile([
                "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-image.js",
                "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-audio.js",
                "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-video.js",
                "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-validate.js"
            ], function() {
                KAJONA.admin.loader.loadFile([
                    "/core/module_mediamanager/admin/scripts/jquery-fileupload/js/jquery.fileupload-ui.js"
                ], function() {

                    var filesToUpload = 0;
                    $('#%%name%%').fileupload({
                        url: '_webpath_/xml.php?admin=1&module=mediamanager&action=fileUpload',
                        dataType: 'json',
                        autoUpload: false,
                        paramName : '%%name%%',
                        filesContainer: $('table.files'),
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
            });
        });
    });

</script>


</input_upload_multiple>

Regular Submit-Button
<input_submit>
    <div class="form-group">
        <label class="col-sm-3 control-label"></label>
        <div class="col-sm-6">
            <button type="submit" class="btn btn-default savechanges %%class%%" name="%%name%%" value="%%value%%" %%disabled%% %%eventhandler%%>
                <span class="btn-text">%%value%%</span>
                <span class="statusicon"></span>
            </button>
        </div>
    </div>
</input_submit>

An easy date-selector
If you want to use the js-date-picker, leave %%calendarCommands%% at the end of the section
in addition, a container for the calendar is needed. Use %%calendarContainerId%% as an identifier.
<input_date_simple>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>
        <div class="col-sm-6">
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
                <input id="%%calendarId%%" name="%%calendarId%%" class="form-control %%class%%" size="16" type="text" value="%%valuePlain%%">
            </div>
            <script>
                KAJONA.admin.loader.loadFile(["_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/bootstrap-datepicker.js"], function() {
                    var arrSecondFiles = ["_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/locales/bootstrap-datepicker.%%calendarLang%%.js"];
                    if('%%calendarLang%%' == 'en')
                        arrSecondFiles = [];
                    KAJONA.admin.loader.loadFile(arrSecondFiles, function() {
                        var format = '%%dateFormat%%';
                        format = format.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yyyy');
                        $('#%%calendarId%%').datepicker({
                            format: format,
                            weekStart: 1,
                            autoclose: true,
                            language: '%%calendarLang%%',
                            todayHighlight: true
                        });

                        if($('#%%calendarId%%').is(':focus')) {
                            $('#%%calendarId%%').blur();
                            $('#%%calendarId%%').focus();
                        }

                    }, true);
                }, true);
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
                <input id="%%calendarId%%" name="%%calendarId%%" class="form-control" size="16" type="text" value="%%valuePlain%%">
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
            KAJONA.admin.loader.loadFile(["_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/bootstrap-datepicker.js"], function() {
                KAJONA.admin.loader.loadFile(["_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/locales/bootstrap-datepicker.%%calendarLang%%.js"], function() {
                    var format = '%%dateFormat%%';
                    format = format.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yyyy');
                    $('#%%calendarId%%').datepicker({
                        format: format,
                        weekStart: 1,
                        autoclose: true,
                        language: '%%calendarLang%%',
                        todayHighlight: true
                    });

                    if($('#%%calendarId%%').is(':focus')) {
                        $('#%%calendarId%%').blur();
                        $('#%%calendarId%%').focus();
                    }

                }, true);
            }, true);
        </script>
    </div>
</input_datetime_simple>

<input_objectlist>
    <div class="form-group">
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
        <td>%%displayName%% <input type="hidden" name="%%name%%[]" value="%%value%%" /></td>
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
        KAJONA.admin.loader.loadFile(["_webpath_/core/module_system/admin/scripts/jquerytag/jquery.caret.min.js"], function(){
            KAJONA.admin.loader.loadFile("_webpath_/core/module_system/admin/scripts/jquerytag/jquery.tag-editor.min.js", function(){
                $("#%%name%%").tagEditor({
                    initialTags: %%values%%,
                    forceLowercase: false,
                    onChange: %%onChange%%
                });
            }, true);
        }, true);
    </script>
</input_tageditor>

<input_container>
    <div class="form-group">
        <label for="%%name%%" class="col-sm-3 control-label">%%title%%</label>

        <div class="col-sm-6 inputText">
            <div id="%%name%%" class="inputContainer %%class%%">
                %%elements%%
            </div>
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
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="form-control %%class%%" %%readonly%%>
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
    <div class="form-group">
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

---------------------------------------------------------------------------------------------------------
-- MISC ELEMENTS ----------------------------------------------------------------------------------------
Used to fold elements / hide/unhide elements
<layout_folder>
<div id="%%id%%" class="contentFolder %%display%%">%%content%%</div>
</layout_folder>

A precent-beam to illustrate proportions
<percent_beam>
    <div class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="%%percent%%" aria-valuemin="0" aria-valuemax="100" style="width: %%percent%%%;">%%percent%%%</div>
    </div>
</percent_beam>

A fieldset to structure logical sections
<misc_fieldset>
<fieldset class="%%class%%"><legend>%%title%%</legend><div>%%content%%</div></fieldset>
</misc_fieldset>

<graph_container>
<div class="graphBox">%%imgsrc%%</div>
</graph_container>


<iframe_container>
    <div id="%%iframeid%%_loading" class="loadingContainer loadingContainerBackground"></div>
    <iframe src="%%iframesrc%%" id="%%iframeid%%" class="seamless" width="100%" height="100%" frameborder="0" seamless ></iframe>

    <script type='text/javascript'>
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
    </script>
</iframe_container>


<tabbed_content_wrapper>
    <ul class="nav nav-tabs" id="">
        %%tabheader%%
    </ul>

    <div class="tab-content %%classaddon%%">
        %%tabcontent%%
    </div>
</tabbed_content_wrapper>

<tabbed_content_tabheader>
    <li class="%%classaddon%%"><a href="" data-target="#%%tabid%%" data-toggle="tab">%%tabtitle%%</a></li>
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
    if($('#loginError > p').html() == "")
        $('#loginError').remove();

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

    if(%%renderMessages%%) {
        $(function() {
            KAJONA.v4skin.properties.messaging = {
                notification_title : '[lang,messaging_notification_title,messaging]',
                notification_body : '[lang,messaging_notification_body,messaging]',
                show_all : '[lang,action_show_all,messaging]'
            };
            KAJONA.v4skin.messaging.pollMessages()
        });
    }
    else {
        $('#messagingShortlist').closest("li").hide();
    }

    if(%%renderTags%%) {
        $(function() {
            KAJONA.v4skin.properties.tags.show_all = '[lang,action_show_all,tags]';
            KAJONA.v4skin.initTagMenu();
        });
    }
    else {
        $('#tagsSubemenu').closest("li").hide();
    }
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
        $(document).ready(function(){
            KAJONA.admin.renderTocNavigation("%%selector%%");
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
<h2 class="%%class%%">%%text%%</h2>
</headline_form>

---------------------------------------------------------------------------------------------------------
-- RIGHTS MANAGEMENT ------------------------------------------------------------------------------------

The following sections specify the layout of the rights-mgmt

<rights_form_header>
    <div>
        %%desc%% %%record%% <br />
        <a href="javascript:KAJONA.admin.permissions.toggleEmtpyRows('[lang,permissions_toggle_visible,system]', '[lang,permissions_toggle_hidden,system]');" id="rowToggleLink" class="rowsVisible">[lang,permissions_toggle_visible,system]</a><br /><br />
    </div>
    <div id="responseContainer">
    </div>
</rights_form_header>

<rights_form_form>
<table class="table admintable table-striped">
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
	%%rows%%
</table>
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
                    <input name="%%name%%" type="checkbox" id="%%name%%" value="1" onclick="this.blur();" onchange="KAJONA.admin.permissions.checkRightMatrix();" %%checked%% />
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

<path_container>
    <ul class="breadcrumb">
        %%pathnavi%%
    </ul>
</path_container>

<path_entry>
    <li class="pathentry">
        %%pathlink%%
    </li>
</path_entry>

---------------------------------------------------------------------------------------------------------
-- CONTENT TOOLBARS -------------------------------------------------------------------------------------

Toolbar, prominent in the layout. Rendered to switch between action.
<contentToolbar_wrapper>
<div class="navbar navbar-default contentToolbar">
    <div class="navbar-inner ">
        <ul class="nav navbar-nav">%%entries%%</ul>
    </div>
</div>
</contentToolbar_wrapper>

<contentToolbar_entry>
<li>%%entry%%</li>
</contentToolbar_entry>

<contentToolbar_entry_active>
<li class="active">%%entry%%</li>
</contentToolbar_entry_active>


Toolbar for the current record, rendered to quick-access the actions of the current record.
<contentActionToolbar_wrapper>
<div class="actionToolbar pull-right">%%content%%</div>
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

<pe_basic_data>
    <!-- KAJONA_BUILD_LESS_START -->
    <link href="_skinwebpath_/less/bootstrap_pe.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_skinwebpath_/less/less.min.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->
</pe_basic_data>

The following section is the toolbar of the portaleditor, displayed at top of the page.
The following placeholders are provided by the system:
pe_status_page, pe_status_status, pe_status_autor, pe_status_time
pe_status_page_val, pe_status_status_val, pe_status_autor_val, pe_status_time_val
pe_iconbar, pe_disable
<pe_toolbar>



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

	<script type="text/javascript">
		var peDialog;
		KAJONA.admin.lang["pe_dialog_close_warning"] = "[lang,pe_dialog_close_warning,pages]";
        KAJONA.portal.loader.loadFile([
            "_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/bootstrap.min.js",
            "_webpath_/core/module_v4skin/admin/skins/kajona_v4/js/kajona_dialog.js"
        ], function() {
		    peDialog = new KAJONA.admin.ModalDialog('peDialog', 0, true, true);
		    delDialog = new KAJONA.admin.ModalDialog('delDialog', 1, false, false);
		}, true);
	</script>

    <div id="peToolbar" style="display: none;">
		<div class="info">
			<table>
				<tbody>
		            <tr>
			            <td rowspan="2" style="width: 100%; text-align: center; vertical-align: middle;">%%pe_iconbar%%</td>
		                <td class="key" style="vertical-align: bottom;">[lang,pe_status_page,pages]</td>
		                <td class="value" style="vertical-align: bottom;">%%pe_status_page_val%%</td>
		                <td class="key" style="vertical-align: bottom;">[lang,pe_status_time,pages]</td>
		                <td class="value" style="vertical-align: bottom;">%%pe_status_time_val%%</td>
		                <td rowspan="2" style="text-align: right; vertical-align: top;">%%pe_disable%%</td>
		            </tr>
		            <tr>
		                <td class="key" style="vertical-align: top;">[lang,pe_status_status,pages]</td>
		                <td class="value" style="vertical-align: top;">%%pe_status_status_val%%</td>
		                <td class="key" style="vertical-align: top;">[lang,pe_status_autor,pages]</td>
		                <td class="value" style="vertical-align: top;">%%pe_status_autor_val%%</td>
		            </tr>
	            </tbody>
	        </table>
		</div>
    </div>
    <div id="peToolbarSpacer"></div>
</pe_toolbar>

<pe_actionToolbar>
<div class="peElementWrapper" data-systemid="%%systemid%%" data-element="%%elementname%%">
    <div class="peElementActions" style="display: none;">
        <div class="actions">
            %%actionlinks%%
        </div>
    </div>
    %%content%%
</div>
</pe_actionToolbar>

Possible placeholders: %%link_complete%%, %%name%%, %%href%%
<pe_actionToolbar_link>
%%link_complete%%
</pe_actionToolbar_link>

Code to add single elements to portaleditors new element menu (will be inserted in pe_actionNewWrapper)
<pe_actionNew>
    <li ><a href="#" onclick="KAJONA.admin.portaleditor.openDialog('%%elementHref%%')">%%elementName%%</a></li>
</pe_actionNew>

Displays the new element button
<pe_actionNewWrapper>
    <div id="menuContainer_%%placeholder%%" class="dropdown">
        <i class="peNewButton fa fa-plus-circle" role="button" data-toggle="dropdown" title="%%label%% &quot;%%placeholderName%%&quot;" rel="tooltip"></i>
        <div class="dropdown-menu peContextMenu" role="menu">
            <ul >
                %%contentElements%%
            </ul>
        </div>
    </div>
</pe_actionNewWrapper>

Displays the new element button
<pe_placeholderWrapper>
    <div class="pePlaceholderWrapper" data-placeholder="%%placeholder%%">%%content%%</div>
</pe_placeholderWrapper>


<pe_inactiveElement>
    <div class="peInactiveElement">%%title%%</div>
</pe_inactiveElement>

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
</language_switch>

---------------------------------------------------------------------------------------------------------
-- QUICK HELP -------------------------------------------------------------------------------------------

<quickhelp>
    <script>
        $(function () {
            $('#moduleTitle').popover({
                title: '%%title%%',
                content: '%%text%%',
                placement: 'bottom',
                trigger: 'hover',
                html: true
            }).css("cursor", "help");
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
        KAJONA.admin.loader.loadFile('/core/module_dashboard/admin/scripts/dashboard.js', function() {
            KAJONA.admin.dashboard.init();
        });
    </script>
</dashboard_wrapper>

---------------------------------------------------------------------------------------------------------
-- TREE VIEW --------------------------------------------------------------------------------------------

<tree>
    <div id="%%treeId%%" class="treeDiv"></div>
    <script type="text/javascript">
        KAJONA.admin.loader.loadFile([
            "/core/module_system/admin/scripts/jstree/jquery.jstree.js",
            "/core/module_system/admin/scripts/jstree/jquery.hotkeys.js"
        ], function() {

            //create a valid tree config - drag n drop enabled, sorting enabled
            var check_move = function(m) { return false; };
            if('%%orderingEnabled%%' == 'true') {
                check_move = function(m) {

                    if(m.o.attr("draggable") === "false")
                        return false;

                    var p = this._get_parent(m.o);
                    if(!p) return false;
                    p = p == -1 ? this.get_container() : p;
                    if(p === m.np) return true;
                    if(p[0] && m.np[0] && p[0] === m.np[0]) return true;
                    return false;
                };
            }

            if('%%hierarchialSortEnabled%%' == 'true') {
                check_move = function(m) {
                    if(m.o.attr("draggable") === "false")
                        return false;
                    return true;
                };
            };

            $('#%%treeId%%').jstree({

                "json_data" : {
                    "ajax" : {
                        "url" : "%%loadNodeDataUrl%%",
                        "data" : function (n) {
                            return {
                                "systemid" : n.attr ? n.attr("systemid") : '%%rootNodeSystemid%%',
                                "rootnode" : '%%rootNodeSystemid%%'
                            };
                        }
                    }
                },
                "crrm" : {
                    "move" : {
                        "check_move" : check_move
                    }
                },
                "types" : {
                    "default" : {
                        "renamable" : "none"
                    }
                },
                "dnd" : {
                    "drop_finish" : function () {
                    },
                    "drag_check" : function (data) {

                        var draggedId = $(data.o).data("systemid");
                        var targetId = $(data.r).attr("systemid");

                        //validate, if the drag-node is the same as the target
                        if(draggedId == targetId)
                            return false;

                        //node already an existing parent node?
                        var arrParent = $("#"+targetId).closest("li[systemid='"+draggedId+"']");
                        if(arrParent.length != 0) {
                            return false;
                        }

                        return {
                            after : false,
                            before : false,
                            inside : true
                        };
                    },
                    "drag_finish" : function (data) {

                        var draggedId = $(data.o).data("systemid");
                        var targetId = $(data.r).attr("systemid");

                        var arrParent = $("#"+targetId).closest("li[systemid='"+draggedId+"']");
                        if(arrParent.length != 0) {
                            location.reload();
                            return false;
                        }

                        //save new parent to backend
                        KAJONA.admin.ajax.genericAjaxCall("system", "setPrevid", draggedId+"&prevId="+targetId, function() {
                            location.reload();
                        });

                    }
                },
                /*"dnd" : {
                    "drag_check" : function (data) { return false; },
                    "drop_target" : false,
                    "drag_target" : false
                },*/
                "themes" : {
                    "url" : "_webpath_/core/module_system/admin/scripts/jstree/themes/default/style.css",
                    "icons" : false
                },
                "core" : {
                    "initially_open" : [ %%treeviewExpanders%% ],
                    "html_titles" : true
                },
                "plugins" : [ "themes","json_data","ui","dnd","crrm","types" ]
            })
            //TODO: Hotkeys removed. currently theres no way of preventing a node-renaming, e.g. by pressing f2
            .bind("select_node.jstree", function (event, data) {
                if(data.rslt.obj.attr("link")) {
                    document.location.href=data.rslt.obj.attr("link");
                }
            })
            .bind("load_node.jstree", function(e, data) {
                KAJONA.admin.tooltip.addTooltip('#'+this.id+" a span");
            })
            .bind("rename_node.jstree", function (NODE, REF_NODE) {
                // Do your operation
            })
            .bind("move_node.jstree", function (e, data) {
                data.rslt.o.each(function (i) {

                    var prevId = (data.rslt.cr === -1 ? '%%rootNodeSystemid%%' : data.rslt.np.attr("id"));
                    var systemid = $(this).attr("id");
                    var pos = (data.rslt.cp + i +1)
                    KAJONA.admin.ajax.genericAjaxCall("system", "setPrevid", systemid+"&prevId="+prevId, function() {
                        KAJONA.admin.ajax.setAbsolutePosition(systemid, pos, null, function() {
                            location.reload();
                        });
                    });

                });
            });
        });
    </script>
</tree>


Checkbox tree which shows an structure
<tree_checkbox>
    <div id="%%treeId%%" class="treeDiv"></div>
    <script type="text/javascript">
        KAJONA.admin.loader.loadFile([
            "/core/module_system/admin/scripts/jstree/jquery.jstree.js",
            "/core/module_system/admin/scripts/jstree/jquery.hotkeys.js"
        ], function() {

            $('#%%treeId%%').jstree({
                json_data: {
                    ajax: {
                        url: "%%loadNodeDataUrl%%",
                        data: function (n) {
                            return {
                                "systemid" : n.attr ? n.attr("systemid") : '%%rootNodeSystemid%%',
                                "rootnode" : '%%rootNodeSystemid%%'
                            };
                        }
                    }
                },
                themes: {
                    url: "_webpath_/core/module_system/admin/scripts/jstree/themes/default/style.css",
                    icons: false
                },
                core: {
                    //"initially_open" : [ %%treeviewExpanders%% ],
                    html_titles: true
                },
                checkbox: {
                    two_state: true,
                    checked_parent_open: false
                },
                plugins: [ "themes","json_data","checkbox" ]
            }).bind("loaded.jstree", function (event, data) {
                if(typeof KAJONA.v4skin.getCheckboxTreeSelectionFromParent === 'function') {
                    var arrSystemIds = KAJONA.v4skin.getCheckboxTreeSelectionFromParent();
                    for(var i = 0; i < arrSystemIds.length; i++) {
                        if($('#' + arrSystemIds[i]).length > 0) {
                            $(this).jstree('change_state', [$('#' + arrSystemIds[i]).find('.jstree-checkbox:first').get(0), true]);
                        }
                    }
                }
            }).bind("open_node.jstree close_node.jstree", function (event, data) {
                if(typeof KAJONA.v4skin.getCheckboxTreeSelectionFromParent === 'function') {
                    var arrSystemIds = KAJONA.v4skin.getCheckboxTreeSelectionFromParent();
                    // go only through the child elements of the loaded node
                    if(data.rslt.obj.length > 0) {
                        data.rslt.obj.find('ul > li').each(function() {
                            if($.inArray($(this).attr('systemid'), arrSystemIds) !== -1) {
                                $(event.target).jstree('change_state', [$('#' + $(this).attr('systemid')).find('.jstree-checkbox:first').get(0), true]);
                            }
                        });
                    }
                }
            });

        });
    </script>
</tree_checkbox>


<treeview>
    <table width="100%" cellpadding="3">
        <tr>
            <td valign="top" width="250" >
                <div class="treeViewWrapper">
                    %%treeContent%%
                </div>
            </td>
            <td valign="top" style="border-left: 1px solid #cccccc;">
                %%sideContent%%
            </td>
        </tr>
    </table>
</treeview>


<treeview_modal>
    <div class="treeViewWrapper" style="overflow:auto;">
        %%treeContent%%
    </div>
</treeview_modal>


The tag-wrapper is the section used to surround the list of tag.
Please make sure that the containers' id is named tagsWrapper_%%targetSystemid%%,
otherwise the JavaScript will fail!
<tags_wrapper>
    <div id="tagsLoading_%%targetSystemid%%" class="loadingContainer"></div>
    <div id="tagsWrapper_%%targetSystemid%%"></div>
    <script type="text/javascript">
        KAJONA.admin.loader.loadFile('/core/module_tags/admin/scripts/tags.js', function() {
            KAJONA.admin.tags.reloadTagList('%%targetSystemid%%', '%%attribute%%');
        });
    </script>
</tags_wrapper>

<tags_tag>
    <span class="label label-default">%%tagname%%</span>
    <script type="text/javascript">KAJONA.admin.tooltip.addTooltip('#icon_%%strTagId%%');</script>
</tags_tag>

<tags_tag_delete>
    <span class="label label-default taglabel">%%tagname%% <a href="javascript:KAJONA.admin.tags.removeTag('%%strTagId%%', '%%strTargetSystemid%%', '%%strAttribute%%');"> %%strDelete%%</a> %%strFavorite%%</span>
    <script type="text/javascript">KAJONA.admin.tooltip.addTooltip($(".taglabel [rel='tooltip']"));</script>
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
    <select onchange="window.location.replace(this.value);">
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
    <div class="%%class%%" id="event_%%systemid%%" onmouseover="KAJONA.admin.dashboardCalendar.eventMouseOver('%%highlightid%%')" onmouseout="KAJONA.admin.dashboardCalendar.eventMouseOut('%%highlightid%%')">
        %%content%%
    </div>
</calendar_event>

---------------------------------------------------------------------------------------------------------
-- MENU -------------------------------------------------------------------------------------------------
<contextmenu_wrapper>
    <div class="dropdown-menu generalContextMenu" role="menu">
        <ul>
            %%entries%%
        </ul>
    </div>
</contextmenu_wrapper>

<contextmenu_entry>
    <li ><a href="%%elementLink%%">%%elementName%%</a></li>
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
            <script type="text/javascript">
                $('.dropdown-menu .dropdown-submenu a').click(function (e) {
                    e.stopPropagation();
                });
            </script>
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
        %%level%%
</sitemap_wrapper>

<sitemap_module_wrapper>
    <div class="panel panel-default">
        <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#moduleNavigation" href="#%%systemid%%">
                %%moduleName%%
            </a>
        </div>
        <div id="%%systemid%%" class="panel-collapse collapse">
            <div class="panel-body">
                <ul>%%actions%%</ul>
            </div>
        </div>
    </div>
</sitemap_module_wrapper>

<sitemap_module_wrapper_active>
    <div class="panel panel-default">
        <div class="panel-heading">
            <a class="active" data-toggle="collapse" data-parent="#moduleNavigation" href="#%%systemid%%">
                %%moduleName%%
            </a>
        </div>
        <div id="%%systemid%%" class="panel-collapse collapse in">
            <div class="panel-body">
                <ul>%%actions%%</ul>
            </div>
        </div>
    </div>
</sitemap_module_wrapper_active>

<sitemap_action_entry>
    <li>%%action%%</li>
</sitemap_action_entry>

<sitemap_divider_entry>
<li class="divider"></li>
</sitemap_divider_entry>


