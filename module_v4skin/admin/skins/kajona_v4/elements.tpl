/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
<div class="grid">
    <ul class="thumbnails gallery sortable">
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
                 KAJONA.admin.ajax.setAbsolutePosition(ui.item.data('systemid'), ui.item.index()+1);
            }
            oldPos = 0;
        },
        delay: KAJONA.util.isTouchDevice() ? 2000 : 0
    });
});
</script>
</grid_footer>

<grid_entry>
<li class="span3" data-systemid="%%systemid%%" >
    <div class="thumbnail" >
        <h5 >%%title%%</h5>
        <div class="contentWrapper" style="background: url(%%image%%) center no-repeat; background-size: 100% 100%;">
            <div class="metainfo">
                <div>%%info%%</div>
                <div>%%subtitle%%</div>
            </div>
        </div>
            <!--<img src="%%image%%" alt="">-->


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
Loads the yui-script-helper and adds the table to the drag-n-dropable tables getting parsed later
<dragable_list_header>
<script type="text/javascript">
    $(function() {

        var bitMoveToTree = false;
        %%jsInject%%

        var oldPos = null;
        $('#%%listid%%').sortable( {
            items: 'tbody:has(tr[data-systemid!=""])',
            handle: 'td.listsorthandle',
            cursor: 'move',
            forcePlaceholderSize: true,
            forceHelperSize: true,
            placeholder: 'group_move_placeholder',
            start: function(event, ui) {
                oldPos = ui.item.index()
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index()) {
                    var intOffset = 1;
                    //see, of there are nodes not being sortable - would lead to another offset
                    $('#%%listid%% > tbody').each(function(index) {
                        if($(this).find('tr').data('systemid') == "")
                            intOffset--;
                        if($(this).find('tr').data('systemid') == ui.item.find('tr').data('systemid'))
                            return false;
                    });
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.find('tr').data('systemid'), ui.item.index()+intOffset, null, null, '%%targetModule%%');
                }
                oldPos = 0;
            },
            delay: KAJONA.util.isTouchDevice() ? 2000 : 0
        });
        $('#%%listid%% > tbody:has(tr[data-systemid!=""]) > tr').each(function(index) {
            $(this).find("td.listsorthandle").css('cursor', 'move').append("<i class='icon-resize-vertical'></i>");
            KAJONA.admin.tooltip.addTooltip($(this).find("td.listsorthandle"), "[lang,commons_sort_vertical,system]");

            if(bitMoveToTree) {
                $(this).find("td.treedrag").css('cursor', 'move').addClass("jstree-draggable").append("<i class='icon-resize-horizontal' data-systemid='"+$(this).closest("tr").data("systemid")+"'></i>");
                KAJONA.admin.tooltip.addTooltip($(this).find("td.treedrag"), "[lang,commons_sort_totree,system]");
            }
        });
    });
</script>
<style>.group_move_placeholder { display: table-row; } </style>
<table id="%%listid%%" class="table admintable table-striped-tbody">

</dragable_list_header>

Optional Element to close a list
<list_footer>
</table>
</list_footer>

<dragable_list_footer>
</table>
</dragable_list_footer>


The general list will replace all other list types in the future.
It is responsible for rendering the different admin-lists.
Currently, there are two modes: with and without a description.
<generallist_1>
    <tbody>
        <tr data-systemid="%%listitemid%%" class="generalListSet1">
            <td class="treedrag"></td>
            <td class="listsorthandle"></td>
            <td class="checkbox">%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
    </tbody>
</generallist_1>

<generallist_2>
    <tbody>
        <tr data-systemid="%%listitemid%%" class="generalListSet2">
            <td class="treedrag"></td>
            <td class="listsorthandle"></td>
            <td class="checkbox">%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
    </tbody>
</generallist_2>

<generallist_desc_1>
    <tbody class="generalListSet1">
        <tr data-systemid="%%listitemid%%">
            <td rowspan="2" class="treedrag"></td>
            <td rowspan="2" class="listsorthandle"></td>
            <td rowspan="2" class="checkbox">%%checkbox%%</td>
            <td rowspan="2" class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
        <tr>
            <td colspan="3" class="description">%%description%%</td>
        </tr>
    </tbody>
</generallist_desc_1>

<generallist_desc_2>
    <tbody class="generalListSet2">
        <tr data-systemid="%%listitemid%%">
            <td rowspan="2" class="treedrag"></td>
            <td rowspan="2" class="listsorthandle"></td>
            <td rowspan="2" class="checkbox">%%checkbox%%</td>
            <td rowspan="2" class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
        <tr>
            <td colspan="3" class="description">%%description%%</td>
        </tr>
    </tbody>
</generallist_desc_2>


Divider to split up a page in logical sections
<divider>
<hr />
</divider>

data list header. Used to open a table to print data
<datalist_header>
<table class="table table-striped table-condensed">
</datalist_header>

data list footer. at the bottom of the datatable
<datalist_footer>
</table>
</datalist_footer>

One Column in a row (header record) - the header, the content, the footer
<datalist_column_head_header>
	<tr class="adminListRow1">
</datalist_column_head_header>

<datalist_column_head>
    <th>%%value%%</th>
</datalist_column_head>

<datalist_column_head_footer>
	</tr>
</datalist_column_head_footer>

One Column in a row (data record) - the header, the content, the footer, providing the option of two styles
<datalist_column_header_1>
	<tr class="adminListRow1">
</datalist_column_header_1>

<datalist_column_1>
		<td class="dataTitle">%%value%%</td>
</datalist_column_1>

<datalist_column_footer_1>
	</tr>
</datalist_column_footer_1>

<datalist_column_header_2>
	<tr class="adminListRow2">
</datalist_column_header_2>

<datalist_column_2>
    <td class="dataValue">%%value%%</td>
</datalist_column_2>

<datalist_column_footer_2>
	</tr>
</datalist_column_footer_2>



---------------------------------------------------------------------------------------------------------
-- ACTION ELEMENTS --------------------------------------------------------------------------------------

Element containing one button / action, multiple put together, e.g. to edit or delete a record.
To avoid side-effects, no line-break in this case -> not needed by default, but in classics-style!
<list_button><span class="listButton">%%content%%</span></list_button>

---------------------------------------------------------------------------------------------------------
-- FORM ELEMENTS ----------------------------------------------------------------------------------------

<form_start>
<form name="%%name%%" id="%%name%%" method="post" action="%%action%%" enctype="%%enctype%%" onsubmit="%%onsubmit%%" class="form-horizontal">
</form_start>

<form_close>
</form>
</form_close>

Dropdown
<input_dropdown>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <select name="%%name%%" id="%%name%%" class="input-xlarge %%class%%" %%disabled%% %%addons%%>%%options%%</select>
        </div>
    </div>

    <script type="text/javascript">
    KAJONA.admin.loader.loadFile(["_skinwebpath_/js/chosen/chosen.jquery.min.js", "_skinwebpath_/js/chosen/chosen.css"], function() {
        var id = '#%%name%%'.replace("[", "\\[");
        var id = id.replace("]", "\\]");
        $(id).chosen();
    }, true);
    </script>
</input_dropdown>

<input_dropdown_row>
<option value="%%key%%">%%value%%</option>
</input_dropdown_row>

<input_dropdown_row_selected>
<option value="%%key%%" selected="selected">%%value%%</option>
</input_dropdown_row_selected>

Checkbox
<input_checkbox>
    <div class="control-group">
        <label for="%%name%%" class="control-label"></label>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="%%name%%" value="checked" id="%%name%%" %%checked%%>
                %%title%%
            </label>
        </div>
    </div>
</input_checkbox>

Regular Hidden-Field
<input_hidden>
	<input name="%%name%%" value="%%value%%" type="hidden" id="%%name%%">
</input_hidden>

Regular Text-Field
<input_text>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%" %%readonly%%>
            %%opener%%
        </div>
    </div>
</input_text>

Textarea
<input_textarea>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <textarea name="%%name%%" id="%%name%%" class="input-xlarge %%class%%" rows="4" %%readonly%%>%%value%%</textarea>
        </div>
    </div>
</input_textarea>

Regular Password-Field
<input_password>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input type="password" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%" %%readonly%%>
        </div>
    </div>
</input_password>

Upload-Field
<input_upload>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input type="file" name="%%name%%" id="%%name%%" class="input-file %%class%%">
            <p class="help-block">
                %%maxSize%%
            </p>
        </div>
    </div>
</input_upload>

Upload-Field for multiple files with progress bar
<input_upload_multiple>
    %%modalDialog%%
    <div id="showUploaderBtn" class="btn">[lang,upload_multiple_dialogHeader,mediamanager]</div>

    <div id="kajonaUploadDialog" style="display: none;">

        <div id="uploadContainerWrapper" class="well well-small">
            <div id="uploadContainer" >
                <noscript>%%fallbackContent%%</noscript>
            </div>
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                [lang,upload_dropArea,mediamanager]
            </div>
            <div id="startUploadBtn" class="btn">[lang,upload_multiple_uploadFiles,mediamanager]</div>
        </div>

        <script type="text/javascript">
            KAJONA.admin.loader.loadFile([
                "/core/module_mediamanager/admin/scripts/qqfileuploader/fileuploader.js",
                "/core/module_mediamanager/admin/scripts/qqfileuploader/fileuploader.css"
            ], function() {


                $('#showUploaderBtn').click(function() {
                    jsDialog_0.setContentRaw($('#kajonaUploadDialog').html());
                    jsDialog_0.init();
                });

                var uploader = new qq.FileUploader({
                    element: document.getElementById('uploadContainer'),
                    action: '_webpath_/xml.php?admin=1&module=mediamanager&action=fileUpload',
                    debug: false,
                    inputName : '%%name%%',
                    autoUpload: false,
                    allowedExtensions: [%%allowedExtensions%%],
                    params : {
                        systemid: document.getElementById("mutliuploadSystemid").value,
                        inputElement : '%%name%%',
                        jsonResponse : 'true'
                    },
                    messages : {
                        typeError: "[lang,upload_fehler_filter,mediamanager]",
                        sizeError: "%%upload_multiple_errorFilesize%%"
                    },
                    onComplete: function(id, fileName, responseJSON){
                        if(uploader.getInProgress() == 0)
                            document.location.reload();
                    },
                    uploadButtonText : '[lang,file_select,mediamanager]',
                    classes: {
                        // used to get elements from templates
                        button: 'qq-upload-button',
                        drop: 'qq-upload-drop-area ',
                        dropActive: 'qq-upload-drop-area-active ',
                        dropDisabled: 'qq-upload-drop-area-disabled',
                        list: 'qq-upload-list',
                        progressBar: 'qq-progress-bar',
                        file: 'qq-upload-file',
                        spinner: 'qq-upload-spinner',
                        size: 'qq-upload-size',
                        cancel: 'qq-upload-cancel',
                        success: 'active',
                        fail: 'error'
                    },
                    dragText : "[lang,upload_dropArea,mediamanager]"

                });

                $('table.admintable').bind("dragenter", function(e) {
                    jsDialog_0.setContentRaw($('#kajonaUploadDialog').html());
                    jsDialog_0.init();
                });

                $('div.grid').bind("dragenter", function(e) {
                    jsDialog_0.setContentRaw($('#kajonaUploadDialog').html());
                    jsDialog_0.init();
                });

                $('#startUploadBtn').click(function() {
                    uploader.uploadStoredFiles();
                });

                jsDialog_0.setTitle('[lang,upload_multiple_dialogHeader,mediamanager]');
            });
        </script>
    </div>

</input_upload_multiple>

Regular Submit-Button
<input_submit>
    <div class="control-group">
        <button type="submit" class="btn savechanges %%class%%" %%disabled%% %%eventhandler%%>
            <span class="btn-text">%%value%%</span>
            <span class="statusicon"></span>
        </button>
    </div>
</input_submit>

An easy date-selector
If you want to use the js-date-picker, leave %%calendarCommands%% at the end of the section
in addition, a container for the calendar is needed. use %%calendarContainerId%% as an identifier
If the calendar is used, you HAVE TO create a js-function named "calClose_%%calendarContainerId%%". This
function is called after selecting a date, e.g. to hide the calendar
<input_date_simple>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input id="%%calendarId%%" name="%%calendarId%%" class="input-xlarge" size="16" type="text" value="%%valuePlain%%">
            <script>
                KAJONA.admin.loader.loadFile(["_skinwebpath_/js/bootstrap-datepicker.js"], function() {
                    KAJONA.admin.loader.loadFile(["_skinwebpath_/js/locales/bootstrap-datepicker.%%calendarLang%%.js"], function() {
                        var format = '%%dateFormat%%';
                        format = format.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yyyy');
                        $('#%%calendarId%%').datepicker({
                            format: format,
                            weekStart: 1,
                            autoclose: true,
                            language: '%%calendarLang%%'
                        });
                    }, true);
                }, true);
            </script>
        </div>
    </div>

</input_date_simple>

<input_datetime_simple>

    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input id="%%calendarId%%" name="%%calendarId%%" class="input-xlarge" size="16" type="text" value="%%valuePlain%%">
            <input name="%%titleHour%%" id="%%titleHour%%" type="text" class="input-mini %%class%%" size="2" maxlength="2" value="%%valueHour%%" />
            <input name="%%titleMin%%" id="%%titleMin%%" type="text" class="input-mini %%class%%" size="2" maxlength="2" value="%%valueMin%%" />
            <script>
                KAJONA.admin.loader.loadFile(["_skinwebpath_/js/bootstrap-datepicker.js"], function() {
                    KAJONA.admin.loader.loadFile(["_skinwebpath_/js/locales/bootstrap-datepicker.%%calendarLang%%.js"], function() {
                        var format = '%%dateFormat%%';
                        format = format.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yyyy');
                        $('#%%calendarId%%').datepicker({
                            format: format,
                            weekStart: 1,
                            autoclose: true,
                            language: '%%calendarLang%%'
                        });
                    }, true);
                }, true);
            </script>
        </div>
    </div>

	<!--<div><label for="%%titleDay%%">%%title%% </label>-->
		<!--<input name="%%titleDay%%" id="%%titleDay%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueDay%%" />-->
		<!--<input name="%%titleMonth%%" id="%%titleMonth%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMonth%%" />-->
		<!--<input name="%%titleYear%%" id="%%titleYear%%" type="text" class="%%class%%" size="4" maxlength="4" value="%%valueYear%%" />-->
        <!--<a href="#" onclick="KAJONA.admin.calendar.showCalendar('%%calendarId%%', '%%calendarContainerId%%', this); return false;"><img src="_skinwebpath_/pics/icon_calendar.png" alt="" /></a>-->

        <!--<input name="%%titleHour%%" id="%%titleHour%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueHour%%" />-->
		<!--<input name="%%titleMin%%" id="%%titleMin%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMin%%" />-->

		<!--<div id="%%calendarContainerId%%" style="display: none;" class="calendarOverlay"></div>-->
	<!--</div><br />-->
</input_datetime_simple>

A page-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_pageselector-tag and make sure, that you
have a surrounding div with class "ac_container" and a div with id "%%name%%_container" and class
"ac_results" inside the "ac_container", to generate a resultlist
<input_pageselector>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>

        <div class="controls">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%" %%readonly%%>
            %%opener%%
            %%ajaxScript%%
        </div>
    </div>
</input_pageselector>

<input_userselector>
<div class="control-group">
<label for="%%name%%" class="control-label">%%title%%</label>

<div class="controls">
    <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%" %%readonly%%>
    %%opener%%
    %%ajaxScript%%
</div>
</input_userselector>

---------------------------------------------------------------------------------------------------------
-- MISC ELEMENTS ----------------------------------------------------------------------------------------
Used to fold elements / hide/unhide elements
<layout_folder>
<div id="%%id%%" style="display: %%display%%;">%%content%%</div>
</layout_folder>

Same as above, but using an image to fold / unfold the content
<layout_folder_pic>
%%link%%<br /><br /><div id="%%id%%" style="display: %%display%%;">%%content%%</div>
</layout_folder_pic>

A precent-beam to illustrate proportions
<percent_beam>
    <div class="progress progress-striped active"  title="%%percent%%%">
        <div class="bar" style="width: %%percent%%%;"></div>
    </div>
</percent_beam>

A fieldset to structure logical sections
<misc_fieldset>
<fieldset class="%%class%%"><legend>%%title%%</legend><div>%%content%%</div></fieldset><br />
</misc_fieldset>

<graph_container>
<div class="graphBox">%%imgsrc%%</div>
</graph_container>


<tabbed_content_wrapper>
    <ul class="nav nav-tabs" id="myTab">
        %%tabheader%%
    </ul>

    <div class="tab-content">
        %%tabcontent%%
    </div>
</tabbed_content_wrapper>

<tabbed_content_tabheader>
    <li class="%%classaddon%%"><a href="" data-target="#%%tabid%%" data-toggle="tab">%%tabtitle%%</a></li>
</tabbed_content_tabheader>

<tabbed_content_tabcontent>
    <div class="tab-pane fade %%classaddon%%" id="%%tabid%%">
        %%tabcontent%%
    </div>
</tabbed_content_tabcontent>



---------------------------------------------------------------------------------------------------------
-- SPECIAL SECTIONS -------------------------------------------------------------------------------------

The login-Form is being displayed, when the user has to log in.
Needed Elements: %%error%%, %%form%%
<login_form>
<div class="alert alert-error" id="loginError">
    <button type="button" class="close" data-dismiss="alert">×</button>
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
<noscript><div class="alert alert-error">%%loginJsInfo%%</div></noscript>
</login_form>

Part to display the login status, user is logged in
<logout_form>
    <div class="dropdown userNotificationsDropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="icon-user icon-white" id="icon-user"><span class="badge badge-info" id="badge-info">-</span></i> %%name%%
        </a>
        <ul class="dropdown-menu" role="menu">
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#"><i class='icon-tag'></i> [lang,modul_titel,messaging]</a>
                <ul class="dropdown-menu sub-menu" id="messagingShortlist">
                </ul>
            </li>

            <!-- messages will be inserted here -->
            <li class="divider"></li>
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#"><i class='icon-tag'></i> [lang,modul_titel,tags]</a>
                <ul class="dropdown-menu sub-menu" id="tagsSubemenu">
                </ul>
            </li>
            <li class="divider"></li>
            <li><a href="%%dashboard%%"><i class='icon-home'></i> %%dashboardTitle%%</a></li>
            <!--<li><a href="%%sitemap%%">%%sitemapTitle%%</a></li>-->
            <li class="divider"></li>
            <li><a href="#" onclick="document.print();"><i class='icon-print'></i> %%printTitle%%</a></li>
            <li class="divider"></li>
            <li><a href="%%profile%%"><i class='icon-user'></i> %%profileTitle%%</a></li>
            <li class="divider"></li>
            <li><a href="%%logout%%"><i class="icon-off"></i> %%logoutTitle%%</a></li>



        </ul>
    </div>
<script type="text/javascript">
    KAJONA.admin.messaging.getUnreadCount(function(intCount) {
        $('#badge-info').text(intCount);
        KAJONA.admin.messaging.getRecentMessages(function(objResponse) {
            $.each(objResponse, function(index, item) {
                if(item.unread == 0)
                    $('#messagingShortlist').append("<li><a href='"+item.details+"'><i class='icon-envelope'></i> <b>"+item.title+"</b></a></li>");
                else
                    $('#messagingShortlist').append("<li><a href='"+item.details+"'><i class='icon-envelope'></i> "+item.title+"</a></li>");
            });
            $('#messagingShortlist').append("<li><a href='_indexpath_?admin=1&module=messaging'><i class='icon-envelope'></i> [lang,actionShowAll,messaging]</a></li>");
        });
    });

    KAJONA.admin.ajax.genericAjaxCall("tags", "getFavoriteTags", "", function(data, status, jqXHR) {
        if(status == 'success') {
            $.each($.parseJSON(data), function(index, item) {
                $('#tagsSubemenu').append("<li><a href='"+item.url+"'><i class='icon-tag'></i> "+item.name+"</a></li>");
            });
        }
    });
</script>
</logout_form>

Shown, wherever the attention of the user is needed
<warning_box>
    <div class="alert alert-block %%class%%">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        %%content%%
    </div>
</warning_box>

Used to print plain text
<text_row>
<p class="%%class%%">%%text%%</p>
</text_row>

Used to print plaintext in a form
<text_row_form>
<div class="controls">
    <p class="help-block %%class%%">%%text%%</p>
</div>
</text_row_form>

Used to print headline in a form
<headline_form>
<h2 class="%%class%%">%%text%%</h2>
</headline_form>

This Section is used to display a few special details about the current page being edited
<page_infobox>
 <table style="width: 100%;" class="statusPages">
  <tr>
    <td style="width: 18%;">%%pagetemplateTitle%%</td>
    <td style="width: 72%;">%%pagetemplate%%</td>
  </tr>
  <tr>
    <td>%%lasteditTitle%%</td>
    <td>%%lastedit%% %%lastuserTitle%% %%lastuser%%</td>
  </tr>
</table><br /><br />
</page_infobox>

Infobox used by the filemanager
<filemanager_infobox>
<table class="statusFilemanager">
  <tr>
    <td style="padding-bottom: 5px;"></td>
    <td style="text-align: right; white-space: nowrap;" rowspan="2">%%nrfilestitle%% %%files%%<br />%%nrfoldertitle%% %%folders%%</td>
  </tr>
  <tr>
    <td class="actions">%%actions%%</td>
  </tr>
  <tr>
    <td colspan="2" class="actions">%%extraactions%%</td>
  </tr>
</table>
</filemanager_infobox>

---------------------------------------------------------------------------------------------------------
-- RIGHTS MANAGEMENT ------------------------------------------------------------------------------------

The following sections specify the layout of the rights-mgmt

<rights_form_header>
	<div align="left">%%desc%% %%record%% <br /><br /></div>
</rights_form_header>

<rights_form_form>
<table style="width: 98%;">
	<tr class="adminListRow1">
		<td width=\"19%\">&nbsp;</td>
		<td width=\"9%\">%%title0%%</td>
		<td width=\"9%\">%%title1%%</td>
		<td width=\"9%\">%%title2%%</td>
		<td width=\"9%\">%%title3%%</td>
		<td width=\"9%\">%%title4%%</td>
		<td width=\"9%\">%%title5%%</td>
		<td width=\"9%\">%%title6%%</td>
		<td width=\"9%\">%%title7%%</td>
		<td width=\"9%\">%%title8%%</td>
	</tr>
	%%rows%%
</table>
%%inherit%%
</rights_form_form>

<rights_form_row_1>
	<tr class="adminListRow1">
		<td width=\"19%\">%%group%%</td>
		<td width=\"9%\">%%box0%%</td>
		<td width=\"9%\">%%box1%%</td>
		<td width=\"9%\">%%box2%%</td>
		<td width=\"9%\">%%box3%%</td>
		<td width=\"9%\">%%box4%%</td>
		<td width=\"9%\">%%box5%%</td>
		<td width=\"9%\">%%box6%%</td>
		<td width=\"9%\">%%box7%%</td>
		<td width=\"9%\">%%box8%%</td>
	</tr>
</rights_form_row_1>
<rights_form_row_2>
	<tr class="adminListRow2">
		<td width=\"19%\">%%group%%</td>
		<td width=\"9%\">%%box0%%</td>
		<td width=\"9%\">%%box1%%</td>
		<td width=\"9%\">%%box2%%</td>
		<td width=\"9%\">%%box3%%</td>
		<td width=\"9%\">%%box4%%</td>
		<td width=\"9%\">%%box5%%</td>
		<td width=\"9%\">%%box6%%</td>
		<td width=\"9%\">%%box7%%</td>
		<td width=\"9%\">%%box8%%</td>
	</tr>
</rights_form_row_2>

<rights_form_inherit>
<table style="width: 90%;">
	<tr>
      <td style="width: 10%;" class="listecontent">%%title%%</td>
      <td><div align="left"><input name="%%name%%" type="checkbox" id="%%name%%" value="1" onclick="this.blur();" onchange="KAJONA.admin.checkRightMatrix();" %%checked%% /></div></td>
    </tr>
</table>
</rights_form_inherit>

---------------------------------------------------------------------------------------------------------
-- FOLDERVIEW -------------------------------------------------------------------------------------------

UPDATE IN 3.2: The sections folderview_detail_frame and folderview_detail_frame are removed since no longer needed.
               Replaced by the section folderview_image_details

<mediamanager_image_details>
<div class="folderview_image_details">
    %%file_pathnavi%% %%file_name%%
    <div class="imageContainer">
        <div class="image">%%file_image%%</div>
    </div>
    <div class="imageActions">
        %%file_actions%%
    </div>
    <table>
        <tr>
            <td class="first">%%file_path_title%%</td>
            <td>%%file_path%%</td>
        </tr>
        <tr>
            <td class="first">%%file_size_title%%</td>
            <td id="fm_image_size">%%file_size%%</td>
        </tr>
        <tr>
            <td class="first">%%file_dimensions_title%%</td>
            <td id="fm_image_dimensions">%%file_dimensions%%</td>
        </tr>
        <tr>
            <td class="first">%%file_lastedit_title%%</td>
            <td>%%file_lastedit%%</td>
        </tr>
    </table>
</div>
%%filemanager_internal_code%%
%%filemanager_image_js%%
</mediamanager_image_details>

---------------------------------------------------------------------------------------------------------
-- WYSIWYG EDITOR ---------------------------------------------------------------------------------------

NOTE: This section not just defines the layout, it also inits the WYSIWYG editor. Change settings with care!

The textarea field to replace by the editor. If the editor can't be loaded, a plain textfield is shown instead
<wysiwyg_ckeditor>
<div><label for="%%name%%">%%title%%</label><br /><textarea name="%%name%%" id="%%name%%" class="inputWysiwyg">%%content%%</textarea></div><br />
</wysiwyg_ckeditor>

A few settings to customize the editor. They are added right into the CKEditor configuration.
Please refer to the CKEditor documentation to see what's possible here
<wysiwyg_ckeditor_inits>
    width : 640,
    height : 250,
    resize_minWidth : 640,
    skin : 'BootstrapCK-Skin,_skinwebpath_/plugins/BootstrapCK-Skin/',
    uiColor : '#F5F5F5',
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
-- CONTENT TOOLBAR --------------------------------------------------------------------------------------

<contentToolbar_wrapper>
    <table class="contentToolbar">
        <tr>%%entries%%</tr>
    </table>
</contentToolbar_wrapper>

<contentToolbar_entry>
    <td>%%entry%%</td>
</contentToolbar_entry>

<contentToolbar_entry_active>
    <td class="active">%%entry%%</td>
</contentToolbar_entry_active>

---------------------------------------------------------------------------------------------------------
-- ERROR HANDLING ---------------------------------------------------------------------------------------

<error_container>
    <div class="alert alert-block alert-error">
        <a class="close" data-dismiss="alert" href="#">×</a>
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
    <link href="_skinwebpath_/less/bootstrap_pe.less?_system_browser_cachebuster_" rel="stylesheet/less">
    <script> less = { env:'development' }; </script>
    <script src="_skinwebpath_/less/less.js"></script>
    <!-- KAJONA_BUILD_LESS_END -->

    <div class="modal hide fade fullsize" id="peDialog">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body" id="peDialog_content">
            <!-- filled by js -->
        </div>
    </div>

	<script type="text/javascript">
		var peDialog;
		KAJONA.admin.lang["pe_dialog_close_warning"] = "[lang,pe_dialog_close_warning,pages]";
        KAJONA.admin.loader.loadFile([
            "_skinwebpath_/js/kajona_dialog.js",
            "_skinwebpath_/js/bootstrap-modal.js",
            "_skinwebpath_/js/bootstrap-dropdown.js"
        ], function() {
		    peDialog = new KAJONA.admin.ModalDialog('peDialog', 0, true, true);
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
<div id="container_%%systemid%%" class="peContainerOut" onmouseover="KAJONA.admin.portaleditor.showActions('%%systemid%%')" onmouseout="KAJONA.admin.portaleditor.hideActions('%%systemid%%')">
    <div id="menu_%%systemid%%" class="menuOut" style="display: none;">
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
    <li ><a href="#" onclick="KAJONA.admin.portaleditor.openDialog('%%elementHref%%')">%%elementName%% (%%element%%)</a></li>
</pe_actionNew>

Displays the new element button
<pe_actionNewWrapper>
    <div id="menuContainer_%%placeholder%%" class="dropdown">
        <img src="_skinwebpath_/pics/icon_new.png" role="button" data-toggle="dropdown" title="%%label%% %%placeholderName%%" rel="tooltip" class="peNewButton" />
        <div class="dropdown-menu peContextMenu" role="menu">
            <ul >
                %%contentElements%%
            </ul>
        </div>
    </div>
</pe_actionNewWrapper>


<pe_inactiveElement>
    <div class="pe_inactiveElement">%%title%%</div>
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
    <select id="languageChooser" class="input-small" onchange="%%onchangehandler%%">%%languagebuttons%%</select>
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
                trigger: 'hover'
            }).css("cursor", "help");
        });
    </script>
</quickhelp>

<quickhelp_button>
</quickhelp_button>

---------------------------------------------------------------------------------------------------------
-- PAGEVIEW ---------------------------------------------------------------------------------------------

<pageview_body>
    <div class="pagination">
        <ul>
            %%linkBackward%%
            %%pageList%%
            %%linkForward%%
            <li><span>%%nrOfElementsText%% %%nrOfElements%%</span></li>
        </ul>
    </div>
</pageview_body>

<pageview_link_forward>
    <li>
        <a href="%%href%%">%%linkText%%&gt;&gt;</a>
    </li>
</pageview_link_forward>

<pageview_link_backward>
    <li>
        <a href="%%href%%">&lt;&lt;%%linkText%%</a>
    </li>
</pageview_link_backward>

<pageview_page_list>
%%pageListItems%%
</pageview_page_list>

<pageview_list_item>
    <li>
        <a href="%%href%%">%%pageNr%%</a>
    </li>
</pageview_list_item>

<pageview_list_item_active>
    <li>
        <a href="%%href%%" class="active">%%pageNr%%</a>
    </li>
</pageview_list_item_active>

---------------------------------------------------------------------------------------------------------
-- WIDGETS / DASHBOAORD  --------------------------------------------------------------------------------
//TODO %%widget_id%% is not needed anymore
<adminwidget_widget>
    <div class="well well-small">
    <h2 class="">%%widget_name%%</h2>
    <div class="adminwidgetactions pull-right">%%widget_edit%% %%widget_delete%%</div>
    <div class="content loadingContainer">
        %%widget_content%%
    </div>
    </div>
</adminwidget_widget>

<dashboard_column_header>
	<td><ul id="%%column_id%%" class="adminwidgetColumn" data-sortable-handle="h2">
</dashboard_column_header>

<dashboard_column_footer>
	</ul></td>
</dashboard_column_footer>

<dashboard_encloser>
	<li class="dbEntry" data-systemid="%%entryid%%">%%content%%</li>
</dashboard_encloser>

<adminwidget_text>
<div>%%text%%</div>
</adminwidget_text>

<adminwidget_separator>
&nbsp;<br />
</adminwidget_separator>

<dashboard_wrapper>
    <table class="dashBoard"><tr>%%entries%%</tr></table>

    <script type="text/javascript">

        $('.adminwidgetColumn > li').each(function () {
            var widget = $(this);
            var systemId = widget.data('systemid');
            var content = widget.find('.content').first();
            KAJONA.admin.ajax.genericAjaxCall('dashboard', 'getWidgetContent', systemId, function(data, status, jqXHR) {
                if (status == 'success') {
                    content.removeClass('loadingContainer');
                    content.html( $.parseJSON(data) );
                    //TODO use jquerys eval?
                    KAJONA.util.evalScript(data);
                } else {
                    KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);
                }
            });
        });

        $("ul.adminwidgetColumn").each(function(index) {

            $(this).sortable({
                items: 'li.dbEntry',
                handle: 'h2',
                forcePlaceholderSize: true,
                cursor: 'move',
                connectWith: '.adminwidgetColumn',
                stop: function(event, ui) {
                    //search list for new pos
                    var intPos = 0;
                    $(".dbEntry").each(function(index) {
                        intPos++;
                        if($(this).data("systemid") == ui.item.data("systemid")) {
                            KAJONA.admin.ajax.genericAjaxCall("dashboard", "setDashboardPosition", ui.item.data("systemid") + "&listPos=" + intPos+"&listId="+ui.item.closest('ul').attr('id'), KAJONA.admin.ajax.regularCallback)
                            return false;
                        }
                    });
                },
                delay: KAJONA.util.isTouchDevice() ? 2000 : 0
            }).find("h2").css("cursor", "move");
        });
    </script>

</dashboard_wrapper>

---------------------------------------------------------------------------------------------------------
-- DIALOG -----------------------------------------------------------------------------------------------
<dialogContainer>
    <div class="modal hide fade fullsize" id="%%dialog_id%%">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">×</button>
            <h3 id="%%dialog_id%%_title"><!-- filled by js --></h3>
        </div>
        <div class="modal-body" id="%%dialog_id%%_content">
            <!-- filled by js -->
        </div>
    </div>
</dialogContainer>

<dialogConfirmationContainer>
    <div class="modal hide fade" id="%%dialog_id%%">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">×</button>
            <h3 id="%%dialog_id%%_title"><!-- filled by js --></h3>
        </div>
        <div class="modal-body" id="%%dialog_id%%_content">
            <!-- filled by js -->
        </div>
        <div class="modal-footer">
            <a href="#" class="btn" data-dismiss="modal" id="%%dialog_id%%_cancelButton">%%dialog_cancelButton%%</a>
            <a href="#" class="btn btn-primary" id="%%dialog_id%%_confirmButton">confirm</a>
        </div>
    </div>
</dialogConfirmationContainer>

<dialogLoadingContainer>
    <div class="modal hide fade" id="%%dialog_id%%" style="width: 100px;">
        <div class="modal-header">
            <h3 id="%%dialog_id%%_title">%%dialog_title%%</h3>
        </div>
        <div class="modal-body">
            <div id="dialogLoadingDiv" class="loadingContainer"></div>
            <div id="%%dialog_id%%_content"><!-- filled by js --></div>
        </div>
    </div>
</dialogLoadingContainer>

<dialogRawContainer>
    <div class="modal hide" id="%%dialog_id%%">
        <div class="modal-body">
            <div id="%%dialog_id%%_content"><!-- filled by js --></div>
        </div>
    </div>
</dialogRawContainer>



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
                        alert("DROP");
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
                    "icons" : true
                },
                "core" : {
                    "initially_open" : [ %%treeviewExpanders%% ],
                    "html_titles" : true
                },
                "plugins" : [ "themes","json_data","ui","dnd","crrm","types" ]
            })
            //TODO: Hotkeys removed. currently theres no way of preventing a node-renaming, e.g. by pressing f2
            .bind("select_node.jstree", function (event, data) {
                document.location.href=data.rslt.obj.attr("link");
            })
            .bind("rename_node.jstree", function (NODE, REF_NODE) {
                // Do your operation
            }).bind("move_node.jstree", function (e, data) {
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

The tag-wrapper is the section used to surround the list of tag.
Please make sure that the containers' id is named tagsWrapper_%%targetSystemid%%,
otherwise the JavaScript will fail!
<tags_wrapper>
    <div id="tagsWrapper_%%targetSystemid%%" class="loadingContainer">
    </div>
    <script type="text/javascript">
        KAJONA.admin.loader.loadFile('/core/module_tags/admin/scripts/tags.js', function() {
            KAJONA.admin.tags.reloadTagList('%%targetSystemid%%', '%%attribute%%');
        });
    </script>
</tags_wrapper>

<tags_tag>
    <span class="label label-info">%%tagname%%</span>
    <script type="text/javascript">KAJONA.admin.tooltip.addTooltip('#icon_%%strTagId%%');</script>
</tags_tag>

<tags_tag_delete>
    <span class="label label-info">%%tagname%% <a href="javascript:KAJONA.admin.tags.removeTag('%%strTagId%%', '%%strTargetSystemid%%', '%%strAttribute%%');"> <i class="icon-trash icon-white" id="icon_%%strTagId%%" title="[lang,commons_delete,tag]" rel="tooltip" ></i></a></span>
    <script type="text/javascript">KAJONA.admin.tooltip.addTooltip('#icon_%%strTagId%%');</script>
</tags_tag_delete>


A tag-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_tagselector-tag.
<input_tagselector>

    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>
        <div class="controls">
            <input type="text" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%">
            %%opener%%
        </div>
    </div>

%%ajaxScript%%
</input_tagselector>

Part of the admin-skin, quick-access to the users favorite tags
<adminskin_tagselector>
%%favorites_menu%%
    <li><a href="#" onclick="KAJONA.admin.contextMenu.showElementMenu('%%favorites_menu_id%%', this); return false;"><img src="_skinwebpath_/pics/icon_tag.png" title="%%icon_tooltip%%" rel="tooltip"/></a></li>
</adminskin_tagselector>

The aspect chooser is shown in cases more than one aspect is defined in the system-module.
It containes a list of aspects and provides the possibility to switch the different aspects.
<aspect_chooser>
    <select id="aspectChooser" class="input-medium" onchange="window.location.replace(this.value);">
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
        <ul >
            %%entries%%
        </ul>
    </div>
</contextmenu_wrapper>

<contextmenu_entry>
    <li ><a href="#" onclick="%%elementAction%%">%%elementName%%</a></li>
</contextmenu_entry>

<contextmenu_divider_entry>
    <li class="divider"></li>
</contextmenu_divider_entry>

<contextmenu_submenucontainer_entry>
    <li class="dropdown-submenu" >
        <a href="#" onclick="%%elementAction%%">%%elementName%%</a>
        <ul class="dropdown-menu">
            %%entries%%
        </ul>
    </li>
</contextmenu_submenucontainer_entry>


---------------------------------------------------------------------------------------------------------
-- BACKEND NAVIGATION -----------------------------------------------------------------------------------

<sitemap_wrapper>
    <div class="nav-header">Kajona V4</div>
        %%level%%

</sitemap_wrapper>

<sitemap_module_wrapper>
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#moduleNavigation" href="#%%systemid%%">
                %%moduleName%%
            </a>
        </div>
        <div id="%%systemid%%" class="accordion-body collapse">
            <div class="accordion-inner">
                <ul>%%actions%%</ul>
            </div>
        </div>
    </div>
</sitemap_module_wrapper>*

<sitemap_module_wrapper_active>
    <div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle active" data-toggle="collapse" data-parent="#moduleNavigation" href="#%%systemid%%">
                %%moduleName%%
            </a>
        </div>
        <div id="%%systemid%%" class="accordion-body collapse in">
            <div class="accordion-inner">
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


