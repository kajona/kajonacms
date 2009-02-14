<list>
  %%link_newentry%%<br />
  <p>%%link_back%% - %%link_pages%% - %%link_forward%%</p>
<p>%%liste_posts%%</p>
</list>

<post>
<table width="606" border="0">
  <tr>
    <td width="442">%%post_name_from%%: %%post_name_plain%%</td>
    <td width="154">%%post_date%%</td>
  </tr>
  <tr>
    <td colspan="2">%%post_page_text%%: %%post_page%% </td>
  </tr>
  <tr>
    <td>%%post_message_text%%:</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">%%post_text%%</td>
  </tr>
</table>
<br /><br />
</post>


<entry_form>
<ul>%%eintragen_fehler%%</ul>
<form name="form1" method="post" action="%%action%%" accept-charset="UTF-8">
    <div><label for="gb_post_name">%%post_name_text%%*:</label><input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="inputText" /></div><br />
    <div><label for="gb_post_email">%%post_mail_text%%*:</label><input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="inputText" /></div><br />
    <div><label for="gb_post_page">%%post_page_text%%:</label><input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="inputText" /></div><br />
    <div><label for="gb_post_text">%%post_message_text%%*:</label><textarea name="gb_post_text" id="gb_post_text" class="inputTextarea">%%gb_post_text%%</textarea></div><br /><br />
    <div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180" /></div><br />
	<div><label for="gb_post_captcha">Code*:</label><input type="text" name="gb_post_captcha" id="gb_post_captcha" class="inputText" /></div><br />
	<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="%%post_code_text%%" class="button" /></div><br /><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%post_submit_text%%" class="button" /></div><br />

</form>
</entry_form>

<error_row>
    <li>%%error%%</li>
</error_row>