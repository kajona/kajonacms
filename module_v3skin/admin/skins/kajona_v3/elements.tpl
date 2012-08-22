/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$											*
********************************************************************************************************/

This skin-file is used for the Kajona v3 admin skin and can be used as a sample file to create
your own cool skin. Just modify the sections you'd like to. Don't forget the css file and the basic
templates!



---------------------------------------------------------------------------------------------------------
-- LIST ELEMENTS ----------------------------------------------------------------------------------------

Optional Element to start a list
<list_header>
<table cellpadding="0" cellspacing="0" class="adminList">
</list_header>

Header to use when creating drag n dropable lists. places an id an loads the needed js-scripts in the
background using the ajaxHelper.
Loads the yui-script-helper and adds the table to the drag-n-dropable tables getting parsed later
<dragable_list_header>
<script type="text/javascript">
    $(function() {
        var oldPos = null;
        $('#%%listid%%').sortable( {
            items: 'tbody:has(tr[id!=""])',
            cursor: 'move',
            forcePlaceholderSize: true,
            placeholder: 'group_move_placeholder',
            start: function(event, ui) {
                oldPos = ui.item.index()
            },
            stop: function(event, ui) {
                if(oldPos != ui.item.index()) {
                    var intOffset = 1;
                    //see, of there are nodes not being sortable - would lead to another offset
                    $('#%%listid%% > tbody').each(function(index) {
                        if($(this).find('tr').attr('id') == "")
                            intOffset--;
                        if($(this).find('tr').attr('id') == ui.item.find('tr').attr('id'))
                            return false;
                    });
                    //console.log('move from '+(oldPos+intOffset)+'to'+(ui.item.index()+intOffset ))
                    KAJONA.admin.ajax.setAbsolutePosition(ui.item.find('tr').attr('id'), ui.item.index()+intOffset, null, null, '%%targetModule%%');
                }
                oldPos = 0;
            }
        });
        $('#%%listid%% > tbody:has(tr[id!=""]) > tr').each(function(index) {
            $(this).css('cursor', 'move');
        });
    });
</script>
<style>.group_move_placeholder { display: table-row; } </style>
<table cellspacing="0" cellpadding="0" id="%%listid%%" class="adminList">
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
        <tr id="%%listitemid%%" class="generalListSet1">
            <td >%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
    </tbody>
</generallist_1>

<generallist_2>
    <tbody>
        <tr id="%%listitemid%%" class="generalListSet2">
            <td >%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
    </tbody>
</generallist_2>

<generallist_desc_1>
    <tbody class="generalListSet1">
        <tr id="%%listitemid%%">
            <td rowspan="2">%%checkbox%%</td>
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
        <tr id="%%listitemid%%">
            <td rowspan="2">%%checkbox%%</td>
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
<divider><br />
<table cellpadding="0" cellspacing="0" style="width: 100%;">
	<tr>
  		<td class="%%class%%">&nbsp;</td>
	</tr>
</table>
</divider>

data list header. Used to open a table to print data
<datalist_header>
<table cellpadding="0" cellspacing="0" class="adminList">
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
		<td><strong>%%value%%</strong></td>
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
<list_button>%%content%%</list_button>

---------------------------------------------------------------------------------------------------------
-- FORM ELEMENTS ----------------------------------------------------------------------------------------

<form_start>
<form name="%%name%%" id="%%name%%" method="post" action="%%action%%" enctype="%%enctype%%" accept-charset="UTF-8" onsubmit="%%onsubmit%%">
</form_start>

<form_close>
</form>
</form_close>

Dropdown
<input_dropdown>
	<div><label for="%%name%%">%%title%% </label><select name="%%name%%" id="%%name%%" class="%%class%%" %%disabled%% %%addons%%>%%options%%</select></div><br />
</input_dropdown>

<input_dropdown_row>
<option value="%%key%%">%%value%%</option>
</input_dropdown_row>

<input_dropdown_row_selected>
<option value="%%key%%" selected="selected">%%value%%</option>
</input_dropdown_row_selected>

Checkbox
<input_checkbox>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="checked" type="checkbox" id="%%name%%" %%checked%% /></div><br />
</input_checkbox>

Regular Hidden-Field
<input_hidden>
	<input name="%%name%%" value="%%value%%" type="hidden" id="%%name%%" />
</input_hidden>

Regular Text-Field
<input_text>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" %%readonly%% /> %%opener%%</div><br />
</input_text>

Textarea
<input_textarea>
	<div><label for="%%name%%">%%title%% </label><textarea name="%%name%%" id="%%name%%" class="%%class%%"  %%readonly%%>%%value%%</textarea></div><br />
</input_textarea>

Regular Password-Field
<input_password>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="password" id="%%name%%" class="%%class%%" %%readonly%% /></div><br />
</input_password>

Upload-Field
<input_upload>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" type="file" id="%%name%%" class="%%class%%" /> (%%maxSize%%)</div><br />
</input_upload>

Upload-Field for multiple files with progress bar
<input_upload_multiple>


    <div id="uploadContainer">
        <noscript>%%fallbackContent%%</noscript>
    </div>

    <script type="text/javascript">

        KAJONA.admin.loader.loadFile([
            "/core/module_mediamanager/admin/scripts/qqfileuploader/fileuploader.js",
            "/core/module_mediamanager/admin/scripts/qqfileuploader/fileuploader.css"
        ], function() {

            var uploader = new qq.FileUploader({
                element: document.getElementById('uploadContainer'),
                action: '_webpath_/xml.php?admin=1&module=mediamanager&action=fileUpload',
                debug: false,
                inputName : '%%name%%',
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
                    console.log(uploader.getInProgress());

                    if(uploader.getInProgress() == 0)
                        document.location.reload();
                },
                uploadButtonText : '[lang,upload_multiple_uploadFiles,mediamanager]',
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

                    // added to list item <li> when upload completes
                    // used in css to hide progress spinner
                    success: 'active',
                    fail: 'error'
                },
                dragText : "[lang,upload_dropArea,mediamanager]"

            });

        });

    </script>

</input_upload_multiple>


Regular Submit-Button
<input_submit>
	<div><label for="%%name%%">&nbsp;</label><input type="submit" name="%%name%%" value="%%value%%" class="%%class%%" %%disabled%% %%eventhandler%% /></div><br />
</input_submit>

An easy date-selector
If you want to use the js-date-picker, leave %%calendarCommands%% at the end of the section
in addition, a container for the calendar is needed. use %%calendarContainerId%% as an identifier
If the calendar is used, you HAVE TO create a js-function named "calClose_%%calendarContainerId%%". This
function is called after selecting a date, e.g. to hide the calendar
<input_date_simple>
	<div><label for="%%titleDay%%">%%title%% </label>
		<input name="%%titleDay%%" id="%%titleDay%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueDay%%" />
		<input name="%%titleMonth%%" id="%%titleMonth%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMonth%%" />
		<input name="%%titleYear%%" id="%%titleYear%%" type="text" class="%%class%%" size="4" maxlength="4" value="%%valueYear%%" />
		<a href="#" onclick="KAJONA.admin.calendar.showCalendar('%%calendarId%%', '%%calendarContainerId%%', this); return false;"><img src="_skinwebpath_/pics/icon_calendar.gif" alt="" /></a>
		<div id="%%calendarContainerId%%" style="display: none;" class="calendarOverlay"></div>
	</div><br />
	<script type="text/javascript">
        KAJONA.admin.lang.toolsetCalendarWeekday = %%calendarLang_weekday%%;
        KAJONA.admin.lang.toolsetCalendarMonth = %%calendarLang_month%%;
	</script>
</input_date_simple>

<input_datetime_simple>
	<div><label for="%%titleDay%%">%%title%% </label>
		<input name="%%titleDay%%" id="%%titleDay%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueDay%%" />
		<input name="%%titleMonth%%" id="%%titleMonth%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMonth%%" />
		<input name="%%titleYear%%" id="%%titleYear%%" type="text" class="%%class%%" size="4" maxlength="4" value="%%valueYear%%" />
        <a href="#" onclick="KAJONA.admin.calendar.showCalendar('%%calendarId%%', '%%calendarContainerId%%', this); return false;"><img src="_skinwebpath_/pics/icon_calendar.gif" alt="" /></a>

        <input name="%%titleHour%%" id="%%titleHour%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueHour%%" />
		<input name="%%titleMin%%" id="%%titleMin%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMin%%" />

		<div id="%%calendarContainerId%%" style="display: none;" class="calendarOverlay"></div>
	</div><br />
    <script type="text/javascript">
        KAJONA.admin.lang.toolsetCalendarWeekday = %%calendarLang_weekday%%;
        KAJONA.admin.lang.toolsetCalendarMonth = %%calendarLang_month%%;
    </script>
</input_datetime_simple>

A page-selector.

<input_pageselector>
  <div class="ac_container">
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" %%readonly%% /> %%opener%%</div>
  </div><br />
%%ajaxScript%%
</input_pageselector>

<input_userselector>
  <div class="ac_container ac_container_user">
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" %%readonly%% /> %%opener%%</div>
  </div><br />
%%ajaxScript%%
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
<div class="percentBeamText">%%percent%% %</div><div class="percentBeam" style="width: %%width%%px;" title="%%percent%% %"><img src="_skinwebpath_/pics/icon_progressbar.gif" width="%%beamwidth%%" height="10" /></div><div style="clear: both;"></div>
</percent_beam>

A fieldset to structure logical sections
<misc_fieldset>
<fieldset class="%%class%%"><legend>%%title%%</legend><div>%%content%%</div></fieldset><br />
</misc_fieldset>

<graph_container>
<div class="graphBox">%%imgsrc%%</div>
</graph_container>


---------------------------------------------------------------------------------------------------------
-- SPECIAL SECTIONS -------------------------------------------------------------------------------------

The login-Form is being displayed, when the user has to log in.
Needed Elements: %%error%%, %%form%%
<login_form>
%%form%%
<p class="error" id="loginError">%%error%%</p>
<script type="text/javascript">
	if (navigator.cookieEnabled == false) {
	  document.getElementById("loginError").innerHTML = "%%loginCookiesInfo%%";
	}
</script>
<noscript><p class="error">%%loginJsInfo%%</p></noscript>
</login_form>

Part to display the login status, user is logged in
<logout_form>
    <li><a href="%%dashboard%%"><img src="_skinwebpath_/icon_home.gif" title="%%dashboardTitle%%" rel="tooltip" /></a></li>
    <li><a href="%%sitemap%%"><img src="_skinwebpath_/pics/icon_sitemap.gif" title="%%sitemapTitle%%" rel="tooltip" /></a></li>
    <li><a href="#" onclick="KAJONA.admin.contextMenu.showElementMenu('KJ_messageList', this); return false;"><img src="_skinwebpath_/pics/icon_mail.gif" title="" rel="tooltip" /></a><div id="messageBadge"></div></li>
    <li><a href="%%profile%%"><img src="_skinwebpath_/pics/icon_user.gif" title="%%name%% - %%profileTitle%%" rel="tooltip" /></a></li>
    <li><a href="%%logout%%"><img src="_skinwebpath_/icon_logout.gif" title="%%logoutTitle%%" rel="tooltip" /></a></li>
<script type="text/javascript">
    KAJONA.admin.messaging.getUnreadCount(function(intCount) {
        $('#messageBadge').text(intCount);

        //load the list of messages
        KAJONA.admin.messaging.getRecentMessages(function(objResponse) {
            var arrElements = new Array();
            $.each(objResponse, function(index, item) {
                var objElement = {
                    elementName : item.title,
                    elementAction : 'location.href=\''+item.details+'\''
                };
                if(item.unread == 0)
                    objElement.elementName = "<span class='messagesListMenuUnread'>"+objElement.elementName+"</span>";
                else
                    objElement.elementName = "<span class='messagesListMenuRead'>"+objElement.elementName+"</span>";
                arrElements.push(objElement);
            });
            arrElements.push({
                elementName : '<span class="messagesListMenuUnread">[lang,actionShowAll,messaging]<span>',
                elementAction : 'location.href="_indexpath_?admin=1&module=messaging"'
            });
            KAJONA.admin.contextMenu.addElements('KJ_messageList', arrElements);
        });

    });
</script>
</logout_form>

Shown, wherever the attention of the user is needed
<warning_box>
<div class="%%class%%">
%%content%%
</div>
</warning_box>

Used to print plain text
<text_row>
<span class="%%class%%">%%text%%</span><br />
</text_row>

Used to print plaintext in a form
<text_row_form>
<div class="formText"><div class="spacer"></div><div class="%%class%%">%%text%%</div></div><br />
</text_row_form>

Used to print headline in a form
<headline_form>
<h2 class="%%class%%">%%text%%</h2>
</headline_form>

This Section is used to display a few special details about the current page being edited
<page_infobox>
 <table cellpadding="0" cellspacing="0" style="width: 100%;" class="statusPages">
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
<table cellpadding="0" cellspacing="0" class="statusFilemanager">
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
	<div align="left">%%backlink%% | %%desc%% %%record%% <br /><br /></div>
</rights_form_header>

<rights_form_form>
<table cellpadding="0" cellspacing="0" style="width: 98%;">
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
<table cellpadding="0" cellspacing="0" style="width: 90%;">
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
    resize_maxWidth : 640,
    skin : 'office2003,_skinwebpath_/ckeditor/',
    uiColor : '#9AB8F3',
    filebrowserWindowWidth : 400,
    filebrowserWindowHeight : 500,
    filebrowserImageWindowWidth : 400,
    filebrowserImageWindowWindowHeight : 500,
</wysiwyg_ckeditor_inits>


---------------------------------------------------------------------------------------------------------
-- PATH NAVIGATION --------------------------------------------------------------------------------------

The following sections are used to display the path-navigations, e.g. used by the navigation module

<path_container>
%%pathnavi%%
</path_container>

<path_entry>%%pathlink%%&nbsp;&gt;&nbsp;</path_entry>

---------------------------------------------------------------------------------------------------------
-- CONTENT TOOLBAR --------------------------------------------------------------------------------------

<contentToolbar_wrapper>
    <table cellpadding="0" cellspacing="0" class="contentToolbar">
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
<div class="warnbox">
	<h3>%%errorintro%%</h3>
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
    <div class="preText">
    	<pre>%%pretext%%</pre>
    </div>
</preformatted>

---------------------------------------------------------------------------------------------------------
-- PORTALEDITOR -----------------------------------------------------------------------------------------

The following section is the toolbar of the portaleditor, displayed at top of the page.
The following placeholders are provided by the system:
pe_status_page, pe_status_status, pe_status_autor, pe_status_time
pe_status_page_val, pe_status_status_val, pe_status_autor_val, pe_status_time_val
pe_iconbar, pe_disable
<pe_toolbar>
    <style type="text/css">
        .peButtonNew {
            display: none;
        }
    </style>

	<div class="peDialog" id="peDialog" title="PORTALEDITOR">
	    <div class="bd dialogContent" id="peDialog_content" >
	        <!-- filled by js -->
	    </div>
	</div>

	<script type="text/javascript">
		var peDialog;
		KAJONA.admin.lang["pe_dialog_close_warning"] = "%%pe_dialog_close_warning%%";
        KAJONA.admin.loader.loadFile("_skinwebpath_/js/kajona_dialog.js", function() {
		    peDialog = new KAJONA.admin.ModalDialog('peDialog', 0, true, true);

            $('[rel="tooltip"]').each(function(index) {
                KAJONA.admin.tooltip.add(this);
            });
            KAJONA.admin.tooltip.hide();
        }, true);

	</script>

    <div id="peToolbar" style="display: none;">
    	<div class="logo"></div>
		<div class="info">
			<table cellpadding="0" cellspacing="0">
				<tbody>
		            <tr>
			            <td rowspan="2" style="width: 100%; text-align: center; vertical-align: middle;">%%pe_iconbar%%</td>
		                <td class="key" style="vertical-align: bottom;">%%pe_status_page%%</td>
		                <td class="value" style="vertical-align: bottom;">%%pe_status_page_val%%</td>
		                <td class="key" style="vertical-align: bottom;">%%pe_status_time%%</td>
		                <td class="value" style="vertical-align: bottom;">%%pe_status_time_val%%</td>
		                <td rowspan="2" style="text-align: right; vertical-align: top;">%%pe_disable%%</td>
		            </tr>
		            <tr>
		                <td class="key" style="vertical-align: top;">%%pe_status_status%%</td>
		                <td class="value" style="vertical-align: top;">%%pe_status_status_val%%</td>
		                <td class="key" style="vertical-align: top;">%%pe_status_autor%%</td>
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
    {
        element: "%%element%%",
        elementName: "%%elementName%%",
        elementHref: "%%elementHref%%"
    },
</pe_actionNew>

Displays the new element button
<pe_actionNewWrapper>
    <a href="#" class="peButtonNew" onclick="KAJONA.admin.portaleditor.showNewElementMenu('%%placeholder%%', this); return false;" title="%%label%% %%placeholderName%%" rel="tooltip"><img src="_skinwebpath_/pics/icon_new.gif" alt="" /></a>
    <div id="menuContainer_%%placeholder%%" class="yui-skin-sam"></div>
	<script type="text/javascript">
		KAJONA.admin.portaleditor.addNewElements("%%placeholder%%", "%%placeholderName%%", [
			%%contentElements%%
		]);
	</script>
</pe_actionNewWrapper>


<pe_inactiveElement>
    <div class="pe_inactiveElement">%%title%%</div>
</pe_inactiveElement>

---------------------------------------------------------------------------------------------------------
-- LANGUAGES --------------------------------------------------------------------------------------------

A single button, represents one language. Put together in the language-switch
<language_switch_button>
    <option value="%%language%%">%%languageName%%</option>
</language_switch_button>

A button for the active language
<language_switch_button_active>
    <option value="%%language%%" selected="selected">%%languageName%%</option>
</language_switch_button_active>

The language switch surrounds the buttons
<language_switch>
<div class="languageSwitch"><select name="switchLanguage" class="inputDropdown" onchange="KAJONA.admin.switchLanguage(this.value);">%%languagebuttons%%</select></div>
</language_switch>

---------------------------------------------------------------------------------------------------------
-- QUICK HELP -------------------------------------------------------------------------------------------

<quickhelp>
    <script type="text/javascript">
        function showQuickHelp() {
        	if (document.getElementById('quickHelp').style.display == 'block') {
        		hideQuickHelp();
        	} else {
	            document.getElementById('quickHelp').style.display='block';
	            document.getElementById('quickHelp').style.position='absolute';
	            document.getElementById('quickHelp').style.left=(screen.availWidth/2 - 200)+'px';
	            document.getElementById('quickHelp').style.top='150px';
	            document.getElementById('quickHelp').style.zIndex='21';
			}
        }

        function hideQuickHelp() {
            document.getElementById('quickHelp').style.display='none';
        }

        //Register mover for the help-layer
		document.onmousemove = KAJONA.util.mover.checkMousePosition;
		document.onmouseup = KAJONA.util.mover.unsetMousePressed;
    </script>
    <div id="quickHelp" onmousedown="KAJONA.util.mover.setMousePressed(this)" onmouseup="KAJONA.util.mover.unsetMousePressed()" onselectstart="return false;">
		<div class="hd">
			<div class="title">%%title%%</div>
			<div class="c"><a href="javascript:hideQuickHelp();">[X]</a></div>
			<div class="clear"></div>
		</div>
		<div class="bd">
			<div class="c">
				<div class="spacer"></div>
				<p>
					%%text%%
				</p>
				<div class="spacer"></div>
			</div>
		</div>
		<div class="ft">
			<div class="c"></div>
		</div>
	</div>
</quickhelp>

<quickhelp_button>
    <div class="quickHelpButton"><a href="javascript:showQuickHelp();">%%text%%</a></div>
</quickhelp_button>

---------------------------------------------------------------------------------------------------------
-- PAGEVIEW ---------------------------------------------------------------------------------------------

<pageview_body>
<div align="center" style="margin-top: 10px;">%%nrOfElementsText%% %%nrOfElements%% | %%linkBackward%% %%pageList%% %%linkForward%%</div>
</pageview_body>

<pageview_link_forward>
<a href="%%href%%">%%linkText%%&gt;&gt;</a>
</pageview_link_forward>

<pageview_link_backward>
<a href="%%href%%">&lt;&lt;%%linkText%%</a>
</pageview_link_backward>

<pageview_page_list>
%%pageListItems%%
</pageview_page_list>

<pageview_list_item>
<a href="%%href%%" >[ %%pageNr%% ]</a>&nbsp;
</pageview_list_item>

<pageview_list_item_active>
<b><a href="%%href%%" >[ %%pageNr%% ]</a></b>&nbsp;
</pageview_list_item_active>

---------------------------------------------------------------------------------------------------------
-- WIDGETS / DASHBOAORD  --------------------------------------------------------------------------------

<adminwidget_widget>
    <div class="adminwidget">
        <div class="hd ddHandle">
            <div class="title">%%widget_name%%</div>
            <div class="c">%%widget_edit%% %%widget_delete%%</div>
            <div class="clear"></div>
        </div>
        <div class="bd">
            <div class="c">
                <div id="p_widget_%%widget_id%%" >
                    <div class="loadingContainer content">%%widget_content%%</div>
                </div>
            </div>
        </div>
        <div class="ft">
            <div class="c"></div>
        </div>
    </div>
</adminwidget_widget>

<dashboard_column_header>
	<br />
	<td><ul id="%%column_id%%" class="adminwidgetColumn">
</dashboard_column_header>

<dashboard_column_footer>
	</ul></td>
</dashboard_column_footer>

<dashboard_encloser>
	<li id="%%entryid%%" class="dbEntry" data-systemid="%%entryid%%" style="padding: 0; margin: 0;">%%content%%</li>
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
        $("ul.adminwidgetColumn").each(function(index) {

            $(this).sortable({
                items: 'li.dbEntry',
                handle: '.ddHandle',
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
                }
            });
        });
    </script>

</dashboard_wrapper>

---------------------------------------------------------------------------------------------------------
-- DIALOG -----------------------------------------------------------------------------------------------
<dialogContainer><div class="dialog" id="%%dialog_id%%" title="" width="465">

		<div class="c" id="%%dialog_id%%_content">
			<!-- filled by js -->
		</div>

</div></dialogContainer>

<dialogConfirmationContainer><div class="dialog" id="%%dialog_id%%" title="" width="465">

			<span id="%%dialog_id%%_content" class="dialogContent"><!-- filled by js --></span><br /><br />
			<input type="submit" name="%%dialog_id%%_confirmButton" id="%%dialog_id%%_confirmButton" value="confirm" class="inputSubmit" /> <input type="submit" name="%%dialog_id%%_cancelButton" id="%%dialog_id%%_cancelButton" value="%%dialog_cancelButton%%" class="inputSubmitShort" onclick="jsDialog_1.hide(); return false;" />
			<br />

</div></dialogConfirmationContainer>

<dialogLoadingContainer><div class="dialog" id="%%dialog_id%%" style="width: 100px;" title="">
        <div class="c dialogContent" style="margin-left: 45px;">
            <div id="dialogLoadingDiv" class="loadingContainer"></div>
            <div id="%%dialog_id%%_content"><!-- filled by js --></div>
        </div>
    <div class="ft"><div class="c"></div></div>
</div></dialogLoadingContainer>

<dialogRawContainer><div class="dialog" id="%%dialog_id%%" title="" width="465"><span id="%%dialog_id%%_content" class="dialogContent"><!-- filled by js --></span></div></dialogRawContainer>



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
                    "drag_check" : function (data) { return false; },
                    "drop_target" : false,
                    "drag_target" : false
                },
                "themes" : {
                    "url" : "_webpath_/core/module_system/admin/scripts/jstree/themes/default/style.css",
                    "icons" : true
                },
                "core" : {
                    "initially_open" : [ %%treeviewExpanders%% ]
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

                    console.log("id: "+$(this).attr("id"));
                    console.log("position: "+(data.rslt.cp + i +1));
                    console.log("title: "+(data.rslt.name));
                    console.log("ref: "+ (data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id")));

                    var prevId = (data.rslt.cr === -1 ? '%%rootNodeSystemid%%' : data.rslt.np.attr("id"));
                    var systemid = $(this).attr("id");
                    var pos = (data.rslt.cp + i +1)
                    KAJONA.admin.ajax.genericAjaxCall("system", "setPrevid", systemid+"&prevId="+prevId, function() {
                        KAJONA.admin.ajax.setAbsolutePosition(systemid, pos, null, function() {
                            //location.reload();
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
    <div class="tag">%%tagname%%<a href="javascript:KAJONA.admin.tags.removeTag('%%strTagId%%', '%%strTargetSystemid%%', '%%strAttribute%%');">%%deleteIcon%%</a></div>
</tags_tag>

A tag-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_tagselector-tag and make sure, that you
have a surrounding div with class "ac_container" and a div with id "%%name%%_container" and class
"ac_results" inside the "ac_container", to generate a resultlist
<input_tagselector>
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" /> %%opener%%</div>
  <br />
%%ajaxScript%%
</input_tagselector>

Part of the admin-skin, quick-access to the users favorite tags
<adminskin_tagselector>
%%favorites_menu%%
    <li><a href="#" onclick="KAJONA.admin.contextMenu.showElementMenu('%%favorites_menu_id%%', this); return false;"><img src="_skinwebpath_/pics/icon_tag.gif" title="%%icon_tooltip%%" rel="tooltip" /></a></li>
</adminskin_tagselector>

The aspect chooser is shown in cases more than one aspect is defined in the system-module.
It containes a list of aspects and provides the possibility to switch the different aspects.
<aspect_chooser>
    <div id="aspectChooser"><select onchange="window.location.replace(this.value);" >%%options%%</select></div>
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
    <table class="calendarPager" cellpadding="0" cellspacing="0" >
        <tr>
            <td width="20%" style="text-align: left;">%%backwards%%</td>
            <td width="60%" style="text-align: center; font-weight: bold;">%%center%%</td>
            <td width="20%" style="text-align: right;">%%forwards%%</td>
        </tr>
    </table>
</calendar_pager>

<calendar_wrapper>
    <table class="calendar" cellpadding="0" cellspacing="0" >%%content%%</table>
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
    <script type="text/javascript"> (function() {
          KAJONA.admin.contextMenu.addElements('%%id%%', [%%entries%%]);
         })();
    </script>
</contextmenu_wrapper>

<contextmenu_entry>
    {elementName:'%%elementName%%', elementAction:'%%elementActionEscaped%%'},
</contextmenu_entry>



<sitemap_wrapper>
    %%level%%
</sitemap_wrapper>

<sitemap_module_wrapper>
    <li><span>%%module%%</span></li>
</sitemap_module_wrapper>

<sitemap_module_wrapper_active>
<li id="selected"><span>%%module%%</span><ul>%%actions%%</ul></li>

</sitemap_module_wrapper_active>

<sitemap_action_entry>
    <li>%%action%%</li>
</sitemap_action_entry>

<sitemap_divider_entry>
<li class="spacer"></li>
</sitemap_divider_entry>