<!-- available placeholders: action, tellafriend_sender, tellafriend_sender_name, tellafriend_receiver, tellafriend_receiver_name, tellafriend_message -->
<tellafriend_form>
<form name="formTellAFriend" method="post" action="%%action%%" accept-charset="UTF-8">
	%%tellafriend_errors%%
	<input type="hidden" name="action" value="%%tellafriend_action%%">
	<div><label for="tellafriend_sender">%%lang_sender%%</label><input type="text" name="tellafriend_sender" id="tellafriend_sender" value="%%tellafriend_sender%%" class="inputText" /></div><br />
	<div><label for="tellafriend_sender_name">%%lang_sender_name%%</label><input type="text" name="tellafriend_sender_name" id="tellafriend_sender_name" value="%%tellafriend_sender_name%%" class="inputText" /></div><br />
	<div><label for="tellafriend_receiver">%%lang_receiver%%</label><input type="text" name="tellafriend_receiver" id="tellafriend_receiver" value="%%tellafriend_receiver%%" class="inputText" /></div><br />
	<div><label for="tellafriend_receiver_name">%%lang_receiver_name%%</label><input type="text" name="tellafriend_receiver_name" id="tellafriend_receiver_name" value="%%tellafriend_receiver_name%%" class="inputText" /></div><br />
	<div><label for="tellafriend_message">%%lang_message%%</label><textarea name="tellafriend_message" id="tellafriend_message" class="inputTextarea">%%tellafriend_message%%</textarea></div><br /><br />
	<div><label for="kajonaCaptcha_taf"></label><span id="kajonaCaptcha_taf"><script type="text/javascript">KAJONA.portal.loadCaptcha('taf', 180);</script></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('taf', 180); return false;">%%lang_commons_captcha_reload%%</a>)</div><br />
	<div><label for="form_captcha">%%commons_captcha%%</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" autocomplete="off" /></div><br /><br />
	<div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_submit%%" class="button" /></div><br />
</form>
</tellafriend_form>

<!-- available placeholders: liste_fehler -->
<errors>
<ul>%%liste_fehler%%</ul>
</errors>

<!-- available placeholders: error -->
<errorrow>
<li>%%error%%</li>
</errorrow>

<!-- available placeholders: tellafriend_sender_name, tellafriend_receiver_name, tellafriend_url, tellafriend_message -->
<email_html>
Hallo %%tellafriend_receiver_name%%,<br /><br />
ich habe eine interessante Webseite gefunden:<br />
%%tellafriend_url%%<br /><br />
%%tellafriend_message%%<br/><br />
%%tellafriend_sender_name%%
</email_html>

<!-- available placeholders: tellafriend_sender_name -->
<email_subject>Webseiten-Empfehlung von %%tellafriend_sender_name%%</email_subject>