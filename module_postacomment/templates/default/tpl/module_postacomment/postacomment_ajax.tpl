<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: postacomment_list, postacomment_form, postacomment_new_button, postacomment_back, postacomment_pages, postacomment_forward -->
<postacomment_list>  
    %%postacomment_new_button%%
    <div id="postacommentFormWrapper_%%postacomment_systemid%%">
        %%postacomment_form%%
    </div>
    
    %%postacomment_list%%
    <p>%%postacomment_back%% %%postacomment_pages%% %%postacomment_forward%%</p>
</postacomment_list>

<!-- available placeholders: postacomment_post_name, postacomment_post_subject, postacomment_post_message, postacomment_post_date, postacomment_post_systemid, postacomment_post_rating (if module rating installed) -->
<postacomment_post>
	<div class="pacComment">
        <div class="pacHeader">
            <div class="pacName">%%postacomment_post_name%%</div>
            <div class="pacDate">%%postacomment_post_date%%</div>
            <div class="clearfix"></div>
        </div>
        <div class="pacHeader">
            <div class="pacSubject">%%postacomment_post_subject%%</div>
            <div class="pacRating">%%postacomment_post_rating%%</div>
            <div class="clearfix"></div>
        </div>
		<div class="pacText">%%postacomment_post_message%%</div>
	</div>
</postacomment_post>

<!-- available placeholders: comment_systemid -->
<postacomment_new_button>
    <div id="postaCommentButton_%%comment_systemid%%"><a href="#" onclick="KAJONA.portal.loader.loadFile('/templates/default/js/postacomment.js'); KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); return false;">[lang,postacomment_write_new,postacomment]</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page -->
<postacomment_form>
    <div id="postaCommentForm_%%comment_systemid%%" style="display: none;">
    	<form name="formComment" accept-charset="UTF-8" id="formComment_%%comment_systemid%%" onsubmit="KAJONA.portal.postacomment.submit('%%comment_systemid%%'); return false;">
    		<ul>
    		    %%validation_errors%%
    		</ul>
    		<div><label for="comment_name_%%comment_systemid%%">[lang,form_name_label,postacomment]*:</label><input type="text" name="comment_name" id="comment_name_%%comment_systemid%%" value="%%comment_name%%" class="inputText" /></div>
    		<div><label for="comment_subject_%%comment_systemid%%">[lang,form_subject_label,postacomment]:</label><input type="text" name="comment_subject" id="comment_subject_%%comment_systemid%%" value="%%comment_subject%%" class="inputText" /></div>
    		<div><label for="comment_message_%%comment_systemid%%">[lang,form_message_label,postacomment]*:</label><textarea name="comment_message" id="comment_message_%%comment_systemid%%" class="inputTextareaLarge">%%comment_message%%</textarea></div><br />
    		<div><label for="kajonaCaptcha_%%comment_systemid%%"></label><span id="kajonaCaptcha_%%comment_systemid%%"></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('%%comment_systemid%%'); return false;">[lang,commons_captcha_reload,postacomment]</a>)</div>
    		<div><label for="form_captcha_%%comment_systemid%%">[lang,commons_captcha,postacomment]*:</label><input type="text" name="form_captcha" id="form_captcha_%%comment_systemid%%" class="inputText" autocomplete="off" /></div><br />
    		<div><label for="comment_submit_%%comment_systemid%%"></label><input type="submit" name="submit" value="[lang,form_submit_label,postacomment]" id="comment_submit_%%comment_systemid%%" class="button" /></div>
    		<input type="hidden" name="comment_template" value="%%comment_template%%" />
            <input type="hidden" name="comment_systemid" value="%%comment_systemid%%" />
            <input type="hidden" name="comment_page" value="%%comment_page%%" />
    	</form>
    </div>
</postacomment_form>

<!-- available placeholders: error -->
<validation_error_row>
	<li>%%error%%</li>
</validation_error_row>

