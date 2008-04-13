<contactform>
<form name="formContact" method="post" action="%%formaction%%" accept-charset="UTF-8">
	%%formular_fehler%%
	<div><label for="absender_name">Name*:</label><input type="text" name="absender_name" id="absender_name" value="%%absender_name%%" class="inputText" /></div><br />
	<div><label for="absender_email">E-Mail Adresse*:</label><input type="text" name="absender_email" id="absender_email" value="%%absender_email%%" class="inputText" /></div><br />
	<div><label for="absender_nachricht">Nachricht*:</label><textarea name="absender_nachricht" id="absender_nachricht" class="inputTextarea">%%absender_nachricht%%</textarea></div><br /><br />
	<div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180" /></div><br />
	<div><label for="form_captcha">Code*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
	<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="Neuer Code" class="button" /></div><br /><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="Senden" class="button" /></div><br />
</form>
</contactform>

<email>
Folgende Anfrage wurde ueber das Kontaktformular erstellt:

Name:
	%%absender_name%%
E-Mail Adresse:
	%%absender_email%%
Nachricht:
	%%absender_nachricht%%
</email>

<errors>
<table width="400" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Folgende Fehler sind aufgetreten:<br /><ul>%%liste_fehler%%</ul></td>
  </tr>
</table>
</errors>

<errorrow>
<li>%%error%%</li>
</errorrow>