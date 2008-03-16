<tellafriend_form>
<form name="formTellAFriend" method="post" action="%%action%%" accept-charset="UTF-8">
	%%tellafriend_errors%%
	<input type="hidden" name="action" value="%%tellafriend_action%%">
	<div><label for="tellafriend_sender">%%sender%%</label><input type="text" name="tellafriend_sender" id="tellafriend_sender" value="%%tellafriend_sender%%" class="inputText" /></div><br />
	<div><label for="tellafriend_sender_name">%%sender_name%%</label><input type="text" name="tellafriend_sender_name" id="tellafriend_sender_name" value="%%tellafriend_sender_name%%" class="inputText" /></div><br />
	<div><label for="tellafriend_receiver">%%receiver%%</label><input type="text" name="tellafriend_receiver" id="tellafriend_receiver" value="%%tellafriend_receiver%%" class="inputText" /></div><br />
	<div><label for="tellafriend_receiver_name">%%receiver_name%%</label><input type="text" name="tellafriend_receiver_name" id="tellafriend_receiver_name" value="%%tellafriend_receiver_name%%" class="inputText" /></div><br />
	<div><label for="tellafriend_message">%%message%%</label><textarea name="tellafriend_message" id="tellafriend_message" class="inputTextarea">%%tellafriend_message%%</textarea></div><br /><br />
	<div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180" /></div><br />
	<div><label for="form_captcha">%%captcha%%</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
	<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="%%reload_captcha%%" class="button" /></div><br /><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%submit%%" class="button" /></div><br />
</form>
</tellafriend_form>

<errors>
<table width="400" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><ul>%%liste_fehler%%<ul></td>
  </tr>
</table>
</errors>

<errorrow>
<li>%%error%%</li>
</errorrow>

<email_html>
Hallo %%tellafriend_receiver_name%%,<br /><br />
ich habe eine interessante Webseite gefunden:<br />
%%tellafriend_url%%<br /><br />
%%tellafriend_message%%<br/><br />
%%tellafriend_sender_name%%
</email_html>

<email_subject>Interessante Webseite www.kajona.de gefunden</email_subject>