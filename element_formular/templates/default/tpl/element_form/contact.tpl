<contactform>
<form name="formContact" method="post" action="%%formaction%%" accept-charset="UTF-8">
	%%formular_fehler%%
	<div><label for="absender_name">[lang,formContact_name,elements]</label><input type="text" name="absender_name" id="absender_name" value="%%absender_name%%" class="inputText" /></div>
	<div><label for="absender_email">[lang,formContact_email,elements]</label><input type="text" name="absender_email" id="absender_email" value="%%absender_email%%" class="inputText" /></div>
	<div><label for="absender_nachricht">[lang,formContact_message,elements]</label><textarea name="absender_nachricht" id="absender_nachricht" class="inputTextarea">%%absender_nachricht%%</textarea></div><br />
	<div><label for="kajonaCaptcha_contact"></label><span id="kajonaCaptcha_contact"><script type="text/javascript">KAJONA.portal.loadCaptcha('contact', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('contact', 180); return false;">[lang,commons_captcha_reload,elements]</a>)</div>
	<div><label for="form_captcha">[lang,commons_captcha,elements]</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" autocomplete="off" /></div><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="[lang,formContact_send,elements]" class="button" /></div><br />
</form>
</contactform>

<email>
Folgende Anfrage wurde ueber das Kontaktformular erstellt:

[lang,formContact_name,elements]
	%%absender_name%%
[lang,formContact_email,elements]
	%%absender_email%%
[lang,formContact_message,elements]
	%%absender_nachricht%%
</email>

<errors>
[lang,formContact_errors,elements]<br />
<ul>%%liste_fehler%%</ul>
</errors>

<errorrow>
<li>%%error%%</li>
</errorrow>


<message_success>
<div class="success">[lang,formContact_message_success,elements]</div>
</message_success>

<message_error>
<div class="error">[lang,formContact_message_error,elements]</div>
</message_error>
