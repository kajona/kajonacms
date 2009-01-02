<postacomment_list>
<script type="text/javascript">
kajonaAjaxHelper.loadAjaxBase();
</script>
%%postacomment_form%%
%%postacomment_list%%
</postacomment_list>



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



<postacomment_form>
	<div><a href="#" onclick="fold('postaCommentForm', loadCaptcha); return false;">Write a comment</a></div>
	<div id="postaCommentForm" style="display: none;">
		<form name="formComment" method="post" action="%%formaction%%" accept-charset="UTF-8">
			%%validation_errors%%
			<div><label for="comment_name">Name*:</label><input type="text" name="comment_name" id="comment_name" value="%%comment_name%%" class="inputText" /></div><br />
			<div><label for="comment_subject">Subject:</label><input type="text" name="comment_subject" id="comment_subject" value="%%comment_subject%%" class="inputText" /></div><br />
			<div><label for="comment_message">Message*:</label><textarea name="comment_message" id="comment_message" class="inputTextareaLarge">%%comment_message%%</textarea></div><br /><br />
			<div id="kajonaCaptchaContainer"><label for="kajonaCaptcha"></label></div><br />
			<div><label for="form_captcha">Code*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
			<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="New code" class="button" /></div><br /><br />
			<div><label for="Submit"></label><input type="submit" name="Submit" value="Submit" class="button" /></div><br />
		</form>
	</div>
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
</postacomment_form>

<validation_error_row>
	&middot; %%error%% <br />
</validation_error_row>

<rating_bar>
<script type="text/javascript">
<!--
kajonaAjaxHelper.addJavascriptFile("_webpath_/portal/scripts/rating.js");
//-->
</script>
<span class="inline-rating-bar">
<ul class="rating-icon" id="kajona_rating_%%system_id%%" onmouseover="htmlTooltip(this, '%%rating_bar_title%%');">
    <li class="current-rating" style="width:%%rating_ratingPercent%%%;"></li>
    %%rating_icons%%
</ul></span> <span id="kajona_rating_rating_%%system_id%%">%%rating_rating%%</span>
</rating_bar>

<rating_icon><li><a href="#" onclick="%%rating_icon_onclick%%" onmouseover="htmlTooltip(this, '%%rating_icon_title%%');" class="icon-%%rating_icon_number%%">%%rating_icon_number%%</a></li></rating_icon>
