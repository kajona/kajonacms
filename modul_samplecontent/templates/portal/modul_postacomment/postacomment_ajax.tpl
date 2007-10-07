<postacomment_list>

<div id="postacommentFormWrapper">
   	<div><a href="javascript:toggle('postaCommentForm');">Kommentar schreiben</a></div>
    <div id="postaCommentForm" style="display: none;">
		%%postacomment_form%%
	</div>
</div>
	
%%postacomment_list%%

<script type="text/javascript" language="javascript">
//ok, further scripts needed. load now, to be set up, if user posts
kajonaAjaxHelper.loadAjaxBase();	

var postacommentCallback =
{
  success: function(o) { submitPostacommentForm.setResponseText(o); },
  failure: function(o) { submitPostacommentForm.setResponseText(o); }
};

function submitPostacommentForm()  {
	var connectionObject;
	
	this.submit = function() {
	
		//create a new ajax request. collect data.
		var comment_name = document.getElementById('comment_name').value;
		var comment_subject = document.getElementById('comment_subject').value;
		var comment_message = document.getElementById('comment_message').value;
		var form_captcha = document.getElementById('form_captcha').value;
		var comment_template = document.getElementById('comment_template').value;
		var post_target = 'xml.php?module=postacomment&action=savepost';
		//concat to send all values
		var post_body = 'comment_name='+comment_name+'&comment_subject='+comment_subject+'&comment_message='+comment_message
						+'&form_captcha='+form_captcha+'&comment_template='+comment_template;
						
		//show loading-message
		this.setLoadingIcon();			
		
		if(this.connectionObject == null || !YAHOO.util.Connect.isCallInProgress(this.connectionObject)) {
			this.connectionObject = YAHOO.util.Connect.asyncRequest('POST', post_target, postacommentCallback, post_body);
		}
	}
	
	this.setLoadingIcon = function() {
		document.getElementById('postacommentFormWrapper').innerHTML = '<div align="center" style="padding: 20px;"><img src="portal/pics/kajona/loading.gif" /></div>';
	}
	
	this.setResponseText = function(o) {
		//just the stuff between <postacomment>, plz
		var intStart = o.responseText.indexOf("<postacomment>")+14;
		var responseText = o.responseText.substr(intStart, o.responseText.indexOf("</postacomment>")-intStart);
		document.getElementById('postacommentFormWrapper').innerHTML = responseText;
	}
	
}

submitPostacommentForm = new submitPostacommentForm();

function postacommentSubmitWrapper() {

	if(typeof YAHOO == "undefined" || typeof YAHOO.util.Connect == "undefined") {
        window.setTimeout(postacommentSubmitWrapper(), 1000);
        return;
    }
    
    submitPostacommentForm.submit();
}


</script>
</postacomment_list>



<postacomment_post>
	<div class="pacComment">
		<div class="pacHeader">
			<div class="pacName">%%postacomment_post_name%%</div>
			<div class="pacDate">%%postacomment_post_date%%</div>
			<div class="pacSubject">%%postacomment_post_subject%%</div>
		</div>	
		<div class="pacText">%%postacomment_post_message%%</div>
	</div>
</postacomment_post>



<postacomment_form>
    		<form name="formComment" accept-charset="UTF-8">
    			<ul>
    			%%validation_errors%%
    			</ul>
    			<div><label for="comment_name">Name*:</label><input type="text" name="comment_name" id="comment_name" value="%%comment_name%%" class="inputText" /></div><br />
    			<div><label for="comment_subject">Betreff:</label><input type="text" name="comment_subject" id="comment_subject" value="%%comment_subject%%" class="inputText" /></div><br />
    			<div><label for="comment_message">Nachricht*:</label><textarea name="comment_message" id="comment_message" class="inputTextareaLarge">%%comment_message%%</textarea></div><br /><br />
    			<div><label for="kajonaCaptcha"></label><img id="kajonaCaptcha" src="_webpath_/image.php?image=kajonaCaptcha&amp;maxWidth=180" /></div><br />
    			<div><label for="form_captcha">Code*:</label><input type="text" name="form_captcha" id="form_captcha" class="inputText" /></div><br />
    			<div><label for="Reload"></label><input type="button" name="Reload" onclick="reloadCaptcha('kajonaCaptcha')" value="Neuer Code" class="button" /></div><br /><br />
    			<div><label for="Submit"></label><input type="button" name="Submit" value="Senden" class="button" onclick="postacommentSubmitWrapper();" /></div><br />
    			<input type="hidden" id="comment_template" value="%%comment_template%%" />
    			<input type="hidden" id="comment_systemid" value="%%comment_systemid%%" />
    			<input type="hidden" id="comment_page" value="%%comment_page%%" />
    		</form>
</postacomment_form>

<validation_error_row>
	<li>%%error%%</li>
</validation_error_row>