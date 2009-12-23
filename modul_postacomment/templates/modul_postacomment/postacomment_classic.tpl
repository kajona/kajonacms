<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: postacomment_list, postacomment_form, postacomment_new_button -->
<postacomment_list>
    <script type="text/javascript">
        bitKajonaRatingsAvailable = false;
        
        function enableRatingsWrapper() {
            if (bitKajonaRatingsAvailable) {
                kajonaAjaxHelper.loadAjaxBase(null, "rating.js");
            }
        }
        YAHOO.util.Event.onDOMReady(enableRatingsWrapper);
    </script>
    
    %%postacomment_new_button%%
    %%postacomment_form%%
    %%postacomment_list%%
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

<!-- available placeholders: -->
<postacomment_new_button>
    <div><a href="#" onclick="fold('postaCommentForm', loadCaptcha); return false;">%%lang_postacomment_write_new%%</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page -->
<postacomment_form>
    <script type="text/javascript">
        function loadCaptcha() {
            if (document.getElementById("kajonaCaptcha") == undefined) {
                var i=document.createElement("img");
                i.setAttribute("id", "kajonaCaptcha");
                i.setAttribute("src", "_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180");
                document.getElementById("kajonaCaptchaContainer").appendChild(i);
            }
        }
    </script>

	<div id="postaCommentForm" style="display: none;">
		<form name="formComment" method="post" action="%%formaction%%" accept-charset="UTF-8">
			%%validation_errors%%
			<div><label for="comment_name">%%lang_form_name_label%%*:</label><input type="text" name="comment_name" id="comment_name" value="%%comment_name%%" class="inputText" /></div><br />
			<div><label for="comment_subject">%%lang_form_subject_label%%:</label><input type="text" name="comment_subject" id="comment_subject" value="%%comment_subject%%" class="inputText" /></div><br />
			<div><label for="comment_message">%%lang_form_message_label%%*:</label><textarea name="comment_message" id="comment_message" class="inputTextareaLarge">%%comment_message%%</textarea></div><br /><br />
			<div id="kajonaCaptchaContainer"><label for="kajonaCaptcha"></label></div><br />
			<div><label for="form_captcha">%%lang_form_captcha_label%%*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
			<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="%%lang_form_captcha_reload_label%%" class="button" /></div><br /><br />
			<div><label for="Submit"></label><input type="submit" name="Submit" value="%%lang_form_submit_label%%" class="button" /></div><br />
		</form>
	</div>
</postacomment_form>

<!-- available placeholders: error -->
<validation_error_row>
	&middot; %%error%%<br />
    <script type="text/javascript">document.getElementById('postaCommentForm').style.display = "block"; YAHOO.util.Event.onDOMReady(loadCaptcha);</script>
</validation_error_row>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_hits, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        bitKajonaRatingsAvailable = true;
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="kajonaTooltip.add(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%% (%%rating_hits%%)</span>
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="kajonaTooltip.add(this, '%%lang_postacomment_rating_rate1%%%%rating_icon_number%%%%lang_postacomment_rating_rate2%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>