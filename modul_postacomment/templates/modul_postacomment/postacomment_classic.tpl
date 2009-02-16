<!-- see section "Template-API" of module manual for a list of available placeholders -->

<!-- available placeholders: postacomment_list, postacomment_form, postacomment_new_button -->
<postacomment_list>   
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

<!-- available placeholders: postacomment_write_new -->
<postacomment_new_button>
    <div><a href="#" onclick="fold('postaCommentForm', loadCaptcha); return false;">%%postacomment_write_new%%</a></div>
</postacomment_new_button>

<!-- available placeholders: formaction, comment_name, comment_subject, comment_message, comment_template, comment_systemid, comment_page, form_name_label, form_subject_label, form_message_label, form_captcha_label, form_captcha_reload_label, form_submit_label -->
<postacomment_form>
    <script type="text/javascript" language="javascript">
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
			<div><label for="comment_name">%%form_name_label%%*:</label><input type="text" name="comment_name" id="comment_name" value="%%comment_name%%" class="inputText" /></div><br />
			<div><label for="comment_subject">%%form_subject_label%%:</label><input type="text" name="comment_subject" id="comment_subject" value="%%comment_subject%%" class="inputText" /></div><br />
			<div><label for="comment_message">%%form_message_label%%*:</label><textarea name="comment_message" id="comment_message" class="inputTextareaLarge">%%comment_message%%</textarea></div><br /><br />
			<div id="kajonaCaptchaContainer"><label for="kajonaCaptcha"></label></div><br />
			<div><label for="form_captcha">%%form_captcha_label%%*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
			<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="%%form_captcha_reload_label%%" class="button" /></div><br /><br />
			<div><label for="Submit"></label><input type="submit" name="Submit" value="%%form_submit_label%%" class="button" /></div><br />
		</form>
	</div>
</postacomment_form>

<!-- available placeholders: error -->
<validation_error_row>
	&middot; %%error%%<br />
    <script type="text/javascript">document.getElementById('postaCommentForm').style.display = "block"; addLoadEvent(loadCaptcha);</script>
</validation_error_row>

<!-- available placeholders: rating_icons, rating_bar_title, rating_rating, rating_ratingPercent, system_id -->
<rating_bar>
    <script type="text/javascript">
        kajonaAjaxHelper.loadAjaxBase();
        kajonaAjaxHelper.addJavascriptFile("_webpath_/portal/scripts/rating.js");
    </script>
    <span class="inline-rating-bar">
    <ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="htmlTooltip(this, '%%rating_bar_title%%');">
        <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
        %%rating_icons%%
    </ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<!-- available placeholders: rating_icon_number, rating_icon_onclick, rating_icon_title -->
<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="htmlTooltip(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>