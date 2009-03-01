//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

function loadCaptcha() {
	if (document.getElementById("kajonaCaptcha") == undefined) {
		var i=document.createElement("img");
		i.setAttribute("id", "kajonaCaptcha");
		i.setAttribute("src", "image.php?image=kajonaCaptcha&amp;maxWidth=180");
		document.getElementById("kajonaCaptchaContainer").appendChild(i);
	}
}

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
        var comment_page = document.getElementById('comment_page').value;
        var comment_systemid = document.getElementById('comment_systemid').value;
        var post_target = 'xml.php?module=postacomment&action=savepost';
        //concat to send all values
        var post_body = 'comment_name='+comment_name+'&comment_subject='+comment_subject+'&comment_message='+comment_message
                        +'&comment_systemid='+comment_systemid+'&comment_page='+comment_page
                        +'&form_captcha='+form_captcha+'&comment_template='+comment_template;
						
		//show loading-message
		this.setLoadingIcon();			
		
		//hide button
		document.getElementById('postaCommentButton').style.display = "none";
		
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
		//check if form is available -> validation errors occured, so show form and reload captcha
		if (document.getElementById("kajonaCaptchaContainer") != undefined) {
			loadCaptcha();
			reloadCaptcha("kajonaCaptcha");
			document.getElementById('postaCommentForm').style.display = "block";
		}
	}
	
}

submitPostacommentForm = new submitPostacommentForm();

function postacommentSubmitWrapper() {
	if(typeof YAHOO == "undefined" || typeof YAHOO.util.Connect == "undefined") {
        window.setTimeout("postacommentSubmitWrapper()", 1000);
        return;
    }
    
    submitPostacommentForm.submit();
}