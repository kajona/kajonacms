/********************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	elements.tpl																						*
* 	Elements-File for the kajona-v3 skin																*
*																										*
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

<dragable_list_header>
<table cellpadding="0" cellspacing="0" class="adminList">
</dragable_list_header>

Optional Element to close a list
<list_footer>
</table>
</list_footer>

<dragable_list_footer>
</table>
</dragable_list_footer>


Row in a list containing 2 Elements, NO leading picture
Part 1 - every 2nd entry
<list_row_2_1>
<tr class="adminListRow1">
	<td class="title">%%title%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_2_1>
Part 2 - every 2nd entry. Usefull if different css-classes are used every single row
<list_row_2_2>
<tr class="adminListRow2">
	<td class="title">%%title%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_2_2>

Row in a list containing 2 Elements and a leading picture
Part 1 - every 2nd entry
<list_row_2image_1>
<tr class="adminListRow1">
	<td class="image">%%image%%</td>
	<td class="title">%%title%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_2image_1>
Part 2 - every 2nd entry. Usefull if different css-classes are used every single row
<list_row_2image_2>
<tr class="adminListRow2">
	<td class="image">%%image%%</td>
	<td class="title">%%title%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_2image_2>

Row in a list containing 2 Elements, NO leading picture, 2nd variation
Used rather for info-lists than for edit-lists, e.g. the systeminfos
Part 1 - every 2nd entry
<list_row_2_1_b>
<tr class="adminListRow1">
	<td class="title" style="width: 30%;">%%title%%</td>
    <td class="centerWrap">%%actions%%</td>
 </tr>
</list_row_2_1_b>
Part 2 - every 2nd entry. Usefull if different css-classes are used every single row
<list_row_2_2_b>
<tr class="adminListRow2">
	<td class="title" style="width: 30%;">%%title%%</td>
    <td class="centerWrap">%%actions%%</td>
 </tr>
</list_row_2_2_b>

Row in a list containing 3 Elements AND A LEADING IMAGE
Part 1 - every 2nd entry
<list_row_3_1>
<tr class="adminListRow1">
	<td class="image">%%image%%</td>
	<td class="title">%%title%%</td>
	<td class="center">%%center%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_3_1>
Part 2 - every 2nd entry. Usefull if different css-classes are used every single row
<list_row_3_2>
<tr class="adminListRow2">
	<td class="image">%%image%%</td>
	<td class="title">%%title%%</td>
	<td class="center">%%center%%</td>
    <td class="actions">%%actions%%</td>
 </tr>
</list_row_3_2>

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
<table cellpadding="0" cellspacing="0" style="width: 100%;">
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
<form name="%%name%%" method="post" action="%%action%%" enctype="%%enctype%%" accept-charset="UTF-8">
</form_start>

<form_close>
</form>
</form_close>

Dropdown
<input_dropdown>
	<div><label for="%%name%%">%%title%% </label><select name="%%name%%" id="%%name%%" class="%%class%%" %%disabled%%>%%options%%</select></div><br />
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
	<div><label for="%%name%%">%%title%% </label><textarea name="%%name%%" id="%%name%%" class="%%class%%">%%value%%</textarea></div><br />
</input_textarea>

Regular Password-Field
<input_password>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" value="%%value%%" type="password" id="%%name%%" class="%%class%%" %%readonly%% /></div><br />
</input_password>

Upload-Field
<input_upload>
	<div><label for="%%name%%">%%title%% </label><input name="%%name%%" type="file" id="%%name%%" class="%%class%%" /></div><br />
</input_upload>

Regular Submit-Button
<input_submit>
	<div><label for="%%name%%">&nbsp;</label><input type="submit" name="%%name%%" value="%%value%%" class="%%class%%" %%eventhandler%% /></div><br />
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
		<a href="javascript:fold('%%calendarContainerId%%');"><img src="_skinwebpath_/pics/icon_calendar.gif" alt="" /></a>
	</div><br />
	<div id="%%calendarContainerId%%" style="display: none;" class="calendarOverlay"></div><script type="text/javascript"> function calClose_%%calendarContainerId%%() { fold('%%calendarContainerId%%'); }; </script>
	%%calendarCommands%%
</input_date_simple>

---------------------------------------------------------------------------------------------------------
-- MISC ELEMENTS ----------------------------------------------------------------------------------------
Used to fold elements / hide/unhide elements
<layout_folder>
<div id="%%id%%" style="display: %%display%%;">%%content%%<br /><br /></div>
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
<fieldset class="%%class%%"><legend>%%title%%</legend>%%content%%</fieldset><br /><br />
</misc_fieldset>

<graph_container>
<div class="graphBox"><img src="%%imgsrc%%" /></div>
</graph_container>


---------------------------------------------------------------------------------------------------------
-- SPECIAL SECTIONS -------------------------------------------------------------------------------------

The login-Form is being displayed, when the user has to log in.
Needed Elements: %%error%%, %%form%%
<login_form>
%%form%%
<p class="error" id="loginError">%%error%%</p>
<script language="Javascript" type="text/javascript">
	if (navigator.cookieEnabled == false) {
	  document.getElementById("loginError").innerHTML = "%%loginCookiesInfo%%";
	}
</script>
<noscript><p class="error">%%loginJsInfo%%</p></noscript>
</login_form>

Part to display the login status, user is logged in
<logout_form>
<div>
	<div class="boxContent">
		<strong>%%name%%</strong>
		<ul><li><a href="%%profile%%">%%profileTitle%%</a></li><li><a href="%%logout%%">%%logoutTitle%%</a></li></ul>
	</div>
</div>
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

This Section is used to display a few special details about the current page being edited
<page_infobox>
 <table cellpadding="0" cellspacing="0" style="width: 100%;" class="statusPages">
  <tr>
    <td style="width: 18%;">%%pagenameTitle%%</td>
    <td style="width: 72%;">%%pagename%%</td>
    <td style="width: 10%;"></td>
  </tr>
  <tr>
    <td style="width: 18%;">%%pagetemplateTitle%%</td>
    <td style="width: 72%;">%%pagetemplate%%</td>
    <td style="width: 10%;"></td>
  </tr>
  <tr>
    <td>%%lastuserTitle%%</td>
    <td>%%lastuser%%</td>
    <td></td>
  </tr>
  <tr>
    <td>%%lasteditTitle%%</td>
    <td>%%lastedit%%</td>
    <td align="right">%%pagepreview%%</td>
  </tr>
</table><br /><br />
</page_infobox>

Infobox used by the filemanager
<filemanager_infobox>
<table cellpadding="0" cellspacing="0" class="statusFilemanager">
  <tr>
    <td>%%foldertitle%% %%folder%%</td>
    <td style="width: 20%; text-align: right; white-space: nowrap;">%%nrfilestitle%% %%files%%</td>
  </tr>
  <tr>
    <td style="width: 73%;" class="actions">%%actions%%</td>
    <td style="text-align: right; vertical-align: middle; white-space: nowrap;">%%nrfoldertitle%% %%folders%%</td>
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
      <td><div align="left"><input name="%%name%%" type="checkbox" id="%%name%%" value="1" onclick="this.blur();" onchange="checkRightMatrix();" %%checked%% /></div></td>
    </tr>
</table>
</rights_form_inherit>

---------------------------------------------------------------------------------------------------------
-- FOLDERVIEW -------------------------------------------------------------------------------------------

<folderview_detail_frame>

<table cellpadding="0" cellspacing="0" class="folderviewDetail" style="text-align: center;">
	%%rows%%
</table>

</folderview_detail_frame>
<folderview_detail_row>
	<tr class="text">
		<td align="left"></td>
		<td align="left">%%title%%</td>
		<td align="left">%%name%%</td>
		<td align="right"></td>
	</tr>
</folderview_detail_row>

---------------------------------------------------------------------------------------------------------
-- WYSIWYG EDITOR ---------------------------------------------------------------------------------------

NOTE: This section not just defines the layout, it also inits the wysiwyg editor. Change settings with care!

The textarea-field to replace by the editor. If the editor can't be loaded, a plain textfield is shown instead
<wysiwyg_fckedit>
<div><label for="%%name%%">%%title%%</label><textarea name="%%name%%" id="%%name%%" class="inputWysiwyg">%%content%%</textarea></div><br />
</wysiwyg_fckedit>

A few settings to customize the editor. Up to now, those are:
Width, Height
<wysiwyg_fckedit_inits>
    objFCKeditor.Width = 600;
    objFCKeditor.Height = 400;
</wysiwyg_fckedit_inits>

---------------------------------------------------------------------------------------------------------
-- MODULE NAVIGATION ------------------------------------------------------------------------------------

The sourrounding of the module-navigation (NOT THE MODULE-ACTIONS!)
<modulenavi_main>
    <ul>
    	%%rows%%
	</ul>
</modulenavi_main>

One row representing one module
Possible: %%name%%, %%link%%, %%href%%
<modulenavi_main_row>
<li><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row>

<modulenavi_main_row_selected>
<li id="selected"><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row_selected>

<modulenavi_main_row_first>
<li class="first"><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row_first>

<modulenavi_main_row_selected_first>
<li id="selected" class="first"><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row_selected_first>

<modulenavi_main_row_last>
<li><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row_last>

<modulenavi_main_row_selected_last>
<li id="selected"><span><a href="%%href%%">%%name%%</a></span></li>
</modulenavi_main_row_selected_last>

---------------------------------------------------------------------------------------------------------
-- INTERNAL MODULE-ACTION NAVIGATION --------------------------------------------------------------------

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
-- PATH NAVIGATION --------------------------------------------------------------------------------------

The following sections are used to display the path-navigations, e.g. used by the navigation module

<path_container>
<div style="padding-bottom: 5px;">%%pathnavi%%</div>
</path_container>

<path_entry>
%%pathlink%%&nbsp;&gt;&nbsp;
</path_entry>

---------------------------------------------------------------------------------------------------------
--- PREFORMATTED ----------------------------------------------------------------------------------------

Used to print pre-formatted text, e.g. log-file contents
<preformatted>
    <div class="preText">
    	<pre>%%pretext%%</pre>
    </div>
</preformatted>

---------------------------------------------------------------------------------------------------------
--- PORTALEDITOR ----------------------------------------------------------------------------------------

The following section is the toolbar of the portaleditor, displayed at top of the page.
The following placeholders are provided by the system:
pe_status_page, pe_status_status, pe_status_autor, pe_status_time
pe_status_page_val, pe_status_status_val, pe_status_autor_val, pe_status_time_val
pe_iconbar, pe_disable
<pe_toolbar>
    <div id="peToolbar">
    	<div class="logo"></div>
		<div class="info">
			<table cellpadding="0" cellspacing="0" style="height: 36px;">
				<tbody>
		            <tr>
			            <td rowspan="2" style="width: 100%; text-align: center; vertical-align: middle;">%%pe_iconbar%%</td>
		                <td style="padding-right: 5px; text-align: right; vertical-align: bottom;">%%pe_status_page%%</td>
		                <td style="padding-right: 20px; vertical-align: bottom;">%%pe_status_page_val%%</td>
		                <td style="padding-right: 5px; text-align: right; vertical-align: bottom;">%%pe_status_time%%</td>
		                <td style="padding-right: 20px; vertical-align: bottom;">%%pe_status_time_val%%</td>
		                <td rowspan="2" style="text-align: right; vertical-align: top;">%%pe_disable%%</td>
		            </tr>
		            <tr>
		                <td style="padding-right: 5px; text-align: right; vertical-align: top;">%%pe_status_status%%</td>
		                <td style="padding-right: 20px; vertical-align: top;">%%pe_status_status_val%%</td>
		                <td style="padding-right: 5px; text-align: right; vertical-align: top;">%%pe_status_autor%%</td>
		                <td style="padding-right: 20px; vertical-align: top;">%%pe_status_autor_val%%</td>
		            </tr>
	            </tbody>
	        </table>
		</div>
    </div>
    <div id="peToolbarSpacer"></div>
</pe_toolbar>

<pe_actionToolbar>
<div id="container_%%systemid%%" class="peContainerOut" onmouseover="portalEditorHover('%%systemid%%')" onmouseout="portalEditorOut('%%systemid%%')">
    <div id="menu_%%systemid%%" class="menuOut">
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

---------------------------------------------------------------------------------------------------------
--- LANGUAGES -------------------------------------------------------------------------------------------

A single button, represents one language. Put together in the language-switch
<language_switch_button>
    <a href="javascript:%%onclickHandler%%">%%languageName%%</a>
</language_switch_button>

A button for the active language
<language_switch_button_active>
    <a href="javascript:%%onclickHandler%%" class="languageButtonActive">%%languageName%%</a>
</language_switch_button_active>

The language switch sourrounds the buttons
<language_switch>
<div class="languageSwitch">%%languagebuttons%%</div>
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
        var objMoverI = new objMover();
		document.onmousemove = checkMousePosition;
		document.onmouseup=objMoverI.unsetMousePressed;
    </script>
    <div id="quickHelp" onmousedown="objMoverI.setMousePressed(this)" onmouseup="objMoverI.unsetMousePressed()" onselectstart="return false;">
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