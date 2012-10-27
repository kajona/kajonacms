<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: postacomment_list, postacomment_form, postacomment_new_button, postacomment_back, postacomment_pages, postacomment_forward -->
<postacomment_list>   
    %%postacomment_new_button%%
    %%postacomment_form%%
    %%postacomment_list%%
    <p>%%postacomment_back%% %%postacomment_pages%% %%postacomment_forward%%</p>
</postacomment_list>

<!-- available placeholders: postacomment_post_name, postacomment_post_subject, postacomment_post_message, postacomment_post_date, postacomment_post_systemid, postacomment_post_rating (if module rating installed) -->
<postacomment_post>
	<div class="pacComment">
		<div class="pacHeader">
			<div class="pacName">%%postacomment_post_name%%</div>
			<div class="pacDate">%%postacomment_post_date%%</div>
			<div style="clear: both;"></div>
			<div class="pacSubject">%%postacomment_post_subject%%</div>
			<div class="pacRating">%%postacomment_post_rating%%</div>
			<div style="clear: both;"></div>
		</div>	
		<div class="pacText">%%postacomment_post_message%%</div>
	</div>
</postacomment_post>

<!-- available placeholders: comment_systemid -->
<postacomment_new_button>
    <div><a href="#" onclick="KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); return false;">[lang,postacomment_write_new,postacomment]</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page -->
<postacomment_form>
	<div id="postaCommentForm_%%comment_systemid%%" style="display: none;">
		<form name="formComment" method="post" action="%%formaction%%" accept-charset="UTF-8">
            <ul id="formComment_%%comment_systemid%%_errors">
                %%validation_errors%%
            </ul>
			<div><label for="comment_name_%%comment_systemid%%">[lang,form_name_label,postacomment]*:</label><input type="text" name="comment_name" id="comment_name_%%comment_systemid%%" value="%%comment_name%%" class="inputText" /></div>
			<div><label for="comment_subject_%%comment_systemid%%">[lang,form_subject_label,postacomment]:</label><input type="text" name="comment_subject" id="comment_subject_%%comment_systemid%%" value="%%comment_subject%%" class="inputText" /></div>
			<div><label for="comment_message_%%comment_systemid%%">[lang,form_message_label,postacomment]*:</label><textarea name="comment_message" id="comment_message_%%comment_systemid%%" class="inputTextareaLarge">%%comment_message%%</textarea></div><br />
			<div><label for="kajonaCaptcha_%%comment_systemid%%"></label><span id="kajonaCaptcha_%%comment_systemid%%"></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('%%comment_systemid%%'); return false;">[lang,commons_captcha_reload,postacomment]</a>)</div>
			<div><label for="form_captcha_%%comment_systemid%%">[lang,commons_captcha,postacomment]*:</label><input type="text" name="form_captcha" id="form_captcha_%%comment_systemid%%" class="inputText" autocomplete="off" /></div><br />
			<div><label for="comment_submit_%%comment_systemid%%"></label><input type="submit" name="Submit" value="[lang,form_submit_label,postacomment]" id="comment_submit_%%comment_systemid%%" class="button" /></div>
		</form>
	</div>
    <script type="text/javascript">
        $(function() {
		    if($('#formComment_%%comment_systemid%%_errors li').length != 0) {
			    KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); 
		    }
        });
	</script>
</postacomment_form>

<!-- available placeholders: error -->
<validation_error_row>
    <li>%%error%%</li>
</validation_error_row>

