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
    <div><a href="#" onclick="KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); return false;">%%lang_postacomment_write_new%%</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page -->
<postacomment_form>
	<div id="postaCommentForm_%%comment_systemid%%" style="display: none;">
		<form name="formComment" method="post" action="%%formaction%%" accept-charset="UTF-8">
            <ul id="formComment_%%comment_systemid%%_errors">
                %%validation_errors%%
            </ul>
			<div><label for="comment_name_%%comment_systemid%%">%%lang_form_name_label%%*:</label><input type="text" name="comment_name" id="comment_name_%%comment_systemid%%" value="%%comment_name%%" class="inputText" /></div><br />
			<div><label for="comment_subject_%%comment_systemid%%">%%lang_form_subject_label%%:</label><input type="text" name="comment_subject" id="comment_subject_%%comment_systemid%%" value="%%comment_subject%%" class="inputText" /></div><br />
			<div><label for="comment_message_%%comment_systemid%%">%%lang_form_message_label%%*:</label><textarea name="comment_message" id="comment_message_%%comment_systemid%%" class="inputTextareaLarge">%%comment_message%%</textarea></div><br /><br />
			<div><label for="kajonaCaptcha_%%comment_systemid%%"></label><span id="kajonaCaptcha_%%comment_systemid%%"></span> (<a href="#" onclick="KAJONA.portal.loadCaptcha('%%comment_systemid%%'); return false;">%%lang_commons_captcha_reload%%</a>)</div><br />
			<div><label for="form_captcha_%%comment_systemid%%">%%lang_commons_captcha%%*:</label><input type="text" name="form_captcha" id="form_captcha_%%comment_systemid%%" class="inputText" autocomplete="off" /></div><br /><br />
			<div><label for="comment_submit_%%comment_systemid%%"></label><input type="submit" name="Submit" value="%%lang_form_submit_label%%" id="comment_submit_%%comment_systemid%%" class="button" /></div><br />
		</form>
	</div>
    <script type="text/javascript">
        YAHOO.util.Event.onDOMReady(function() {
		    if (document.getElementById('formComment_%%comment_systemid%%_errors').getElementsByTagName('li').length != 0) {
			    KAJONA.util.fold('postaCommentForm_%%comment_systemid%%', function() {KAJONA.portal.loadCaptcha('%%comment_systemid%%', 180);}); 
		    }
        });
	</script>
</postacomment_form>

<!-- available placeholders: error -->
<validation_error_row>
    <li>%%error%%</li>
</validation_error_row>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
	    if (typeof bitKajonaRatingsLoaded == "undefined") {
	    	KAJONA.portal.loader.loadAjaxBase(null, "rating.js");
	        var bitKajonaRatingsLoaded = true;
	    }
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span> (<span id="kajona_rating_hits_%%system_id%%">%%rating_hits%%</span>)
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="KAJONA.portal.tooltip.add(this, '%%lang_postacomment_rating_rate1%%%%rating_icon_number%%%%lang_postacomment_rating_rate2%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>