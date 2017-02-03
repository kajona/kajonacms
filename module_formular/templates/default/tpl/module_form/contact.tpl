<contactform>
<form name="formContact" method="post" action="%%formaction%%" accept-charset="UTF-8">
	%%error_list%%

	<fieldset class="form-group">
		<label for="absender_name">[lang,formContact_name,elements]</label>
		<input type="text" name="absender_name" id="absender_name" value="%%absender_name%%" class="form-control" />
	</fieldset>

	<fieldset class="form-group">
		<label for="absender_email">[lang,formContact_email,elements]</label>
		<input type="text" name="absender_email" id="absender_email" value="%%absender_email%%" class="form-control" />
	</fieldset>

	<fieldset class="form-group">
		<label for="absender_nachricht">[lang,formContact_message,elements]</label>
		<textarea name="absender_nachricht" id="absender_nachricht" class="form-control">%%absender_nachricht%%</textarea>
	</fieldset>



	<fieldset class="form-group">
		<label for="form_captcha">[lang,commons_captcha,elements]</label>

		<div class="row">
			<div class="col-3 col-xs-3">
				<input type="text" name="form_captcha" id="form_captcha" class="form-control" autocomplete="off" />
				<small class="text-muted"><a href="#" onclick="KAJONA.portal.loadCaptcha('contact', 180); return false;">[lang,commons_captcha_reload,elements]</a></small>
			</div>
			<div class="col-6 col-xs-6">
				<span id="kajonaCaptcha_contact"><script type="text/javascript">KAJONA.portal.loadCaptcha('contact', 180);</script></span>
			</div>
		</div>
	</fieldset>

	<fieldset class="form-group">
		<button type="submit" class="btn btn-primary">[lang,formContact_send,elements]</button>
	</fieldset>

	<!-- custom bootstrap error rendering, update if required -->
	<script type="text/javascript">
		$.each([%%error_fields%%], function(index, value) {
			$('#'+value).addClass('form-control-danger');
			$('#'+value).closest('.form-group').addClass('has-danger');
		});
	</script>

</form>
</contactform>

<email>
The following message was sent using the contactform:

[lang,formContact_name,elements]
	%%absender_name%%
[lang,formContact_email,elements]
	%%absender_email%%
[lang,formContact_message,elements]
	%%absender_nachricht%%
</email>

<errors>
	<div class="alert alert-danger" role="alert">
		[lang,formContact_errors,elements]
		<ul>%%error_list%%</ul>
	</div>
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
