/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: elements.tpl 4766 2012-07-11 15:48:40Z sidler $											*
********************************************************************************************************/

This skin-file is used for the Kajona v4 admin skin and can be used as a sample file to create
your own cool skin. Just modify the sections you'd like to. Don't forget the css file and the basic
templates!



---------------------------------------------------------------------------------------------------------
-- LIST ELEMENTS ----------------------------------------------------------------------------------------

Optional Element to start a list
<list_header>
<table class="table table-striped">
</list_header>

Header to use when creating drag n dropable lists. places an id an loads the needed js-scripts in the
background using the ajaxHelper.
Loads the yui-script-helper and adds the table to the drag-n-dropable tables getting parsed later
<dragable_list_header>
<script type="text/javascript">
	KAJONA.admin.loader.loadDragNDropBase(function () {
		KAJONA.admin.loader.loadDragNDropBase(function() {
            KAJONA.admin.dragndroplist.DDApp.init();
            KAJONA.admin.dragndroplist.DDApp.targetModule = '%%targetModule%%';
        }, "dragdrophelper_tr.js");
	});
	if(arrayTableIds == null) {
        var arrayTableIds = new Array("%%listid%%");
    } else {
        arrayTableIds[(arrayTableIds.length +0)] = "%%listid%%";
	}

    var bitOnlySameTable = %%sameTable%%;
</script>
<table id="%%listid%%" class="table table-striped">
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
        <tr id="%%listitemid%%" class="generalListSet1">
            <td >%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
</generallist_1>

<generallist_2>
        <tr id="%%listitemid%%" class="generalListSet2">
            <td >%%checkbox%%</td>
            <td class="image">%%image%%</td>
            <td class="title">%%title%%</td>
            <td class="center">%%center%%</td>
            <td class="actions">%%actions%%</td>
        </tr>
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
<table style="width: 100%;">
	<tr>
  		<td class="%%class%%">&nbsp;</td>
	</tr>
</table>
</divider>

data list header. Used to open a table to print data
<datalist_header>
<table class="table table-striped">
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
            <select name="%%name%%" id="%%name%%" class="%%class%%" %%disabled%% %%addons%%>%%options%%</select>
        </div>
    </div>
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

            <!--
            <p class="help-block">
                In addition to freeform text, any HTML5 text-based input appears like so.
            </p>
            -->
        </div>
    </div>
</input_text>

Textarea
<input_textarea>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>

        <div class="controls">
            <textarea name="%%name%%" id="%%name%%" class="input-xlarge %%class%%" %%readonly%%>%%value%%</textarea>

            <!--
            <p class="help-block">
                In addition to freeform text, any HTML5 text-based input appears like so.
            </p>
            -->
        </div>
    </div>
</input_textarea>

Regular Password-Field
<input_password>
    <div class="control-group">
        <label for="%%name%%" class="control-label">%%title%%</label>

        <div class="controls">
            <input type="password" id="%%name%%" name="%%name%%" value="%%value%%" class="input-xlarge %%class%%" %%readonly%%>

            <!--
            <p class="help-block">
                In addition to freeform text, any HTML5 text-based input appears like so.
            </p>
            -->
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
<input_uploadFlash>
	<div style="display:inline;">
			<div id="kajonaUploadButtonsContainer" rel="tooltip" title="%%upload_multiple_pleaseWait%%">
				<div id="kajonaUploadButtonsOverlay" style="position:absolute; z-index:2"></div>
				<div style="z-index:1"><a id="kajonaUploadSelectLink" href="#" class="inputSubmit">%%upload_multiple_uploadFiles%%</a></div>
			</div>
	</div>

    %%modalDialog%%
    %%javascript%%

	<div id="kajonaUploadDialog" style="display: none;">
		<div class="kajonaUploadFilesContainer">
			<ul id="kajonaUploadFiles" class="kajonaUploadFilesList">
				<li id="kajonaUploadFileSample">
					<div>
						<div class="filename"></div>
						<div class="progress">&nbsp;</div>
						<div class="clearer"></div>
					</div>
					<div class="progressBar"></div>
				</li>
			</ul>
		</div>
		<br />
		<span id="kajonaUploadFilesTotal"></span>&nbsp;%%upload_multiple_totalFilesAndSize%%&nbsp;<span id="kajonaUploadFilesTotalSize"></span>
		<br /><br />
		<div id="kajonaUploadError" class="kajonaUploadError" style="display: none;">%%upload_multiple_errorFilesize%%<br /><br /></div>
		<input type="submit" name="kajonaUploadUploadLink" id="kajonaUploadUploadLink" value="%%upload_multiple_uploadFiles%%" class="inputSubmit" /> <input type="submit" name="kajonaUploadCancelLink" id="kajonaUploadCancelLink" value="%%upload_multiple_cancel%%" class="inputSubmitShort" />
		<br />
	</div>

	<div id="kajonaUploadFallbackContainer" style="display: none;">
		%%fallbackContent%%
	</div>
</input_uploadFlash>

Regular Submit-Button
<input_submit>
    <div class="form-actions">
        <button id="savechanges" type="submit" class="btn %%class%%" %%disabled%% %%eventhandler%%>
            <span class="btn-text">%%value%%</span>
            <span class="statusicon"></span>
        </button>
        <button id="cancelglobal" class="btn" onclick="location.reload();">
            <span class="btn-text">Cancel</span>
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
            <!--
            <div class="input-append date" id="%%calendarId%%" data-date="12.02.2012" data-date-format="dd.mm.yyyy" data-date-weekStart="1">
                <input class="input-xlarge" size="16" type="text" value="12.02.2012">
                <span class="add-on"><i class="icon-th"></i></span>
            </div>
            -->
            <input id="%%calendarId%%" class="input-xlarge" size="16" type="text" value="">
            <script>
            $(function () {
                $('#%%calendarId%%').datepicker({
                    format: 'dd.mm.yyyy',
                    weekStart: 1,
                    autoclose: true,
                    language: 'de'
                });
            });
            </script>

            <!--
            <p class="help-block">
                In addition to freeform text, any HTML5 text-based input appears like so.
            </p>
            -->
        </div>
    </div>


<!--
	<div><label for="%%titleDay%%">%%title%% </label>
		<input name="%%titleDay%%" id="%%titleDay%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueDay%%" />
		<input name="%%titleMonth%%" id="%%titleMonth%%" type="text" class="%%class%%" size="2" maxlength="2" value="%%valueMonth%%" />
		<input name="%%titleYear%%" id="%%titleYear%%" type="text" class="%%class%%" size="4" maxlength="4" value="%%valueYear%%" />
		<a href="#" onclick="KAJONA.admin.calendar.showCalendar('%%calendarId%%', '%%calendarContainerId%%', this); return false;"><img src="_skinwebpath_/pics/icon_calendar.gif" alt="" /></a>
		<div id="%%calendarContainerId%%" style="display: none;" class="calendarOverlay"></div>
	</div><br />
-->
	%%calendarCommands%%
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
	%%calendarCommands%%
</input_datetime_simple>

A page-selector.
If you want to use ajax to load a list of proposals on entering a char,
place ajaxScript before the closing input_pageselector-tag and make sure, that you
have a surrounding div with class "ac_container" and a div with id "%%name%%_container" and class
"ac_results" inside the "ac_container", to generate a resultlist
<input_pageselector>
  <div class="ac_container">
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" %%readonly%% /> %%opener%%</div>
     <div id="%%name%%_container" class="ac_results"></div>
  </div><br />
%%ajaxScript%%
</input_pageselector>

<input_userselector>
  <div class="ac_container ac_container_user">
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" %%readonly%% /> %%opener%%</div>
     <div id="%%name%%_container" class="ac_results"></div>
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
    <div class="progress progress-striped active" style="width: 30%;" title="%%percent%%%">
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
    <div class="dropdown userNotificationsDropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="icon-user icon-white" id="icon-user"><span class="badge badge-info" id="badge-info">13</span></i> %%name%%
        </a>
        <ul class="dropdown-menu">

            <li>
                <ul>
                    <li><a href="#">5 new comments</a></li>
                    <li><a href="#">2 review tasks</a></li>
                    <li><a href="#">2 Kajona updates available</a></li>
                </ul>
            </li>
            <li class="divider"></li>
            <li><a href="%%dashboard%%">%%dashboardTitle%%</a></li>
            <li><a href="%%sitemap%%">%%sitemapTitle%%</a></li>
            <li class="divider"></li>
            <li><a href="%%profile%%">%%profileTitle%%</a></li>
            <li class="divider"></li>
            <li><a href="%%logout%%">%%logoutTitle%%</a></li>
        </ul>
    </div>
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
	<div align="left">%%backlink%% | %%desc%% %%record%% <br /><br /></div>
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
    resize_maxWidth : 640,
    skin : 'office2003,_skinwebpath_/ckeditor/',
    uiColor : '#9AB8F3',
    filebrowserWindowWidth : 400,
    filebrowserWindowHeight : 500,
    filebrowserImageWindowWidth : 400,
    filebrowserImageWindowWindowHeight : 500,
</wysiwyg_ckeditor_inits>

---------------------------------------------------------------------------------------------------------
-- MODULE NAVIGATION ------------------------------------------------------------------------------------
                    /* TODO: REMOVE SECTION */
                    The surrounding of the module-navigation (NOT THE MODULE-ACTIONS!)
                    <modulenavi_main>
                        <ul id="adminModuleNaviUl">
                            %%rows%%
                        </ul>
                    </modulenavi_main>

                    One row representing one module
                    Possible: %%name%%, %%link%%, %%href%%
                    <modulenavi_main_row>
                    <li><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row>

                    <modulenavi_main_row_hidden>
                    <li class="adminModuleNaviHidden"><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row_hidden>

                    <modulenavi_main_row_selected>
                    <li class="active"><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row_selected>

                    <modulenavi_main_row_first>
                    <li class="first"><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row_first>

                    <modulenavi_main_row_selected_first>
                    <li class="first"><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row_selected_first>

                    <modulenavi_main_row_last>
                    <li><span><a href="%%href%%">%%name%%</a></span></li>
                    </modulenavi_main_row_last>

                    <modulenavi_main_row_selected_last>
                    <li><span><a href="%%href%%" >%%name%%</a></span></li>
                    </modulenavi_main_row_selected_last>

                    /* TODO: REMOVE SECTION */

---------------------------------------------------------------------------------------------------------
-- INTERNAL MODULE-ACTION NAVIGATION --------------------------------------------------------------------
                    /* TODO: REMOVE SECTION */

                    The sourrounding of the moduleaction-navigation (NOT THE MODULE LIST!)
                    <moduleactionnavi_main>
                        <ul>
                            %%rows%%
                        </ul>
                    </moduleactionnavi_main>

                    One row representing one action
                    Possible: %%name%%, %%link%%, %%href%%
                    <moduleactionnavi_row>
                    <li><a href="%%href%%">%%name%%</a></li>
                    </moduleactionnavi_row>

                    Spacer, used to seperate logical groups
                    <moduleactionnavi_spacer>
                    <li class="spacer"></li>
                    </moduleactionnavi_spacer>

                    /* TODO: REMOVE SECTION */

---------------------------------------------------------------------------------------------------------
-- PATH NAVIGATION --------------------------------------------------------------------------------------

The following sections are used to display the path-navigations, e.g. used by the navigation module

<path_container>
    <ul class="breadcrumb">
        %%pathnavi%%
    </ul>
</path_container>

<path_entry>
    <li>
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
    <pre class="pre-scrollable">%%pretext%%</pre>
</preformatted>

---------------------------------------------------------------------------------------------------------
-- PORTALEDITOR -----------------------------------------------------------------------------------------

The following section is the toolbar of the portaleditor, displayed at top of the page.
The following placeholders are provided by the system:
pe_status_page, pe_status_status, pe_status_autor, pe_status_time
pe_status_page_val, pe_status_status_val, pe_status_autor_val, pe_status_time_val
pe_iconbar, pe_disable
<pe_toolbar>
    <style>
        .peButtonNew {
            display: none;
        }
    </style>

	<div class="peDialog" id="peDialog">
	    <div class="hd" id="peDialog_title">PORTALEDITOR<div class="close"><a href="#" onclick="KAJONA.admin.portaleditor.closeDialog(); return false;">X</a></div></div>
	    <div class="bd" id="peDialog_content">
	        <!-- filled by js -->
	    </div>
	</div>

	<script type="text/javascript">
		var peDialog;
		KAJONA.admin.lang["pe_dialog_close_warning"] = "%%pe_dialog_close_warning%%";
		YAHOO.util.Event.onDOMReady(function () {
		    peDialog = new KAJONA.admin.ModalDialog('peDialog', 0, true, true);
		});
	</script>

    <div id="peToolbar" style="display: none;">
    	<div class="logo"></div>
		<div class="info">
			<table>
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
    <script>
        $(function () {
            $('#quickHelpButton').popover({
                title: '%%title%%',
                content: '%%text%%',
                placement: 'bottom'
            });
        });
    </script>
</quickhelp>

<quickhelp_button>
    <button id="quickHelpButton">%%text%%</button>
</quickhelp_button>

---------------------------------------------------------------------------------------------------------
-- PAGEVIEW ---------------------------------------------------------------------------------------------

<pageview_body>
    <div class="pagination">
        <ul>
            %%linkBackward%%
            %%pageList%%
            %%linkForward%%
        </ul>
    </div>
    <br>
    %%nrOfElementsText%% %%nrOfElements%%
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
    <li class="active">
        <a href="%%href%%">%%pageNr%%</a>
    </li>
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
                        <div class="loadingContainer">%%widget_content%%</div>
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
	<script type="text/javascript">
	    KAJONA.admin.loader.loadDragNDropBase(function () {
	        KAJONA.admin.loader.loadDragNDropBase(function() {
                KAJONA.admin.dragndroplistDashboard.DDApp.init();
            }, "dragdrophelper_li.js");
	    });
    	if(arrayListIds == null) {
            var arrayListIds = new Array("%%column_id%%");
        } else {
            arrayListIds[(arrayListIds.length +0)] = "%%column_id%%";
		}
	</script>

	<ul id="%%column_id%%" class="adminwidgetColumn">
</dashboard_column_header>

<dashboard_column_footer>
	</ul>
</dashboard_column_footer>

<dashboard_encloser>
	<li id="%%entryid%%" style="padding: 0; margin: 0;">%%content%%</li>
</dashboard_encloser>

<adminwidget_text>
<div>%%text%%</div>
</adminwidget_text>

<adminwidget_separator>
&nbsp;<br />
</adminwidget_separator>

---------------------------------------------------------------------------------------------------------
-- DIALOG -----------------------------------------------------------------------------------------------
<dialogContainer>
    <div class="modal hide" id="%%dialog_id%%">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="%%dialog_id%%">×</button>
            <h3 id="%%dialog_id%%_title"><!-- filled by js --></h3>
        </div>
        <div class="modal-body" id="%%dialog_id%%_content">
            <!-- filled by js -->
        </div>
    </div>
</dialogContainer>

<dialogConfirmationContainer>
    <div class="modal hide" id="%%dialog_id%%">
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
    <div class="modal hide" id="%%dialog_id%%" style="width: 100px;">
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

<treeview>
    <table width="100%" cellpadding="3">
        <tr>
            <td valign="top" width="200" >
                <div class="treeViewWrapper">
                    <div id="treeDiv"></div>
                </div>
            </td>
            <td valign="top" style="border-left: 1px solid #cccccc;">
                %%sideContent%%
            </td>
        </tr>
    </table>
    <script type="text/javascript">
    var tree;
    var arrTreeViewExpanders = new Array(%%treeviewExpanders%%);
    //anonymous function wraps the remainder of the logic:
    (function() {
        //function to initialize the tree:
        function treeInit() {
            //instantiate the tree:
            tree = new YAHOO.widget.TreeView("treeDiv");
            tree.setDynamicLoad(%%loadNodeDataFunction%%);

            var root = tree.getRoot();
            var tempNode = new YAHOO.widget.TextNode({label:'%%rootNodeTitle%%', href:'%%rootNodeLink%%'}, root, false);
            tempNode.systemid = '%%rootNodeSystemid%%';
            //The tree is not created in the DOM until this method is called:
            tree.draw();
            KAJONA.admin.treeview.checkInitialTreeViewToggling();
        }
        //build the tree when files are loaded
        KAJONA.admin.loader.loadTreeviewBase(treeInit);

    })();
    </script>
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
  <div class="ac_container">
     <div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="text" id="%%name%%" class="%%class%%" /> %%opener%%</div>
     <div id="%%name%%_container" class="ac_results"></div>
  </div><br />
%%ajaxScript%%
</input_tagselector>

Part of the admin-skin, quick-access to the users favorite tags
<adminskin_tagselector>
%%favorites_menu%%
    <li><a href="#" onclick="KAJONA.admin.contextMenu.showElementMenu('%%favorites_menu_id%%', this); return false;"><img src="_skinwebpath_/pics/icon_tag.gif" title="%%icon_tooltip%%" rel="tooltip"/></a></li>
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
    <script type="text/javascript"> (function() {
          KAJONA.admin.contextMenu.addElements('%%id%%', [%%entries%%]);
          KAJONA.admin.loader.load([ 'menu', 'container' ]);
         })();
    </script>
    <div id="menuContainer_%%id%%" class="yui-skin-sam"></div>
</contextmenu_wrapper>


---------------------------------------------------------------------------------------------------------
-- BACKEND NAVIGATION -----------------------------------------------------------------------------------

<sitemap_wrapper>
    <ul class="nav nav-list">
        %%level%%
    </ul>
</sitemap_wrapper>

<sitemap_module_wrapper>
    <li class="dropdown">
        <!--%%module%%-->
        <a class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="%%moduleHref%%">%%moduleName%%</a>
        <ul class="dropdown-menu">
            %%actions%%
        </ul>
    </li>
</sitemap_module_wrapper>

<sitemap_module_wrapper_active>
<li class="dropdown active">
    <!--%%module%%-->
    <a  class="dropdown-toggle" data-toggle="dropdown" data-target="#" href="%%moduleHref%%">%%moduleName%%</a>
    <ul class="dropdown-menu">
        %%actions%%
    </ul>
</li>
</sitemap_module_wrapper_active>

<sitemap_action_entry>
    <li>%%action%%</li>
</sitemap_action_entry>

<sitemap_divider_entry>
<li class="divider"></li>
</sitemap_divider_entry>