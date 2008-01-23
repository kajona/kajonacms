<list>
  %%link_newentry%%<br />
  <p>%%link_back%% - %%link_pages%% - %%link_forward%%</p>
<p>%%liste_posts%%</p>
</list>

<post>
<table width="606" border="0">
  <tr>
    <td width="442">von: %%post_name%%</td>
    <td width="154">%%post_date%%</td>
  </tr>
  <tr>
    <td colspan="2">www: %%post_page%% </td>
  </tr>
  <tr>
    <td>Nachricht:</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">%%post_text%%</td>
  </tr>
</table>
<br /><br />
</post>


<entry_form>
%%eintragen_fehler%%
<form name="form1" method="post" action="%%action%%" accept-charset="UTF-8">
    <div><label for="gb_post_name">Name*:</label><input type="text" name="gb_post_name" id="gb_post_name" value="%%gb_post_name%%" class="inputText" /></div><br />
    <div><label for="gb_post_email">E-Mail*:</label><input type="text" name="gb_post_email" id="gb_post_email" value="%%gb_post_email%%" class="inputText" /></div><br />
    <div><label for="gb_post_page">Webseite:</label><input type="text" name="gb_post_page" id="gb_post_page" value="%%gb_post_page%%" class="inputText" /></div><br />
    <div><label for="gb_post_text">Nachricht*:</label><textarea name="gb_post_text" id="gb_post_text" class="inputTextarea">%%gb_post_text%%</textarea></div><br /><br />
    <div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&maxWidth=180" /></div><br />
	<div><label for="gb_post_captcha">Code*:</label><input type="text" name="gb_post_captcha" id="gb_post_captcha" class="inputText" /></div><br />
	<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="Neuer Code" class="button" /></div><br /><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="Eintragen" class="button" /></div><br />

</form>
</entry_form>

