//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

function loadCaptcha(strCaptchaId) {
    var containerName = "kajonaCaptchaContainer";
    var imgID = "kajonaCaptcha";
    if(strCaptchaId != null) {
        containerName+=strCaptchaId;
        imgID += strCaptchaId;
    }
    
	if (document.getElementById("kajonaCaptcha") == undefined) {
		var i=document.createElement("img");
		i.setAttribute("id", imgID);
		i.setAttribute("src", "image.php?image=kajonaCaptcha&amp;maxWidth=180");
		document.getElementById(containerName).appendChild(i);
	}
}

var postacommentCallback =
{
  success: function(o) { submitPostacommentForm.setResponseText(o); },
  failure: function(o) { submitPostacommentForm.setResponseText(o); }
};

function submitPostacommentForm()  {
	var connectionObject;
	var strSystemId;
	this.submit = function(strSystemId) {
        this.strSystemId = strSystemId;
		//create a new ajax request. collect data.
        var comment_name = document.getElementById('comment_name'+strSystemId).value;
        var comment_subject = document.getElementById('comment_subject'+strSystemId).value;
        var comment_message = document.getElementById('comment_message'+strSystemId).value;
        var form_captcha = document.getElementById('form_captcha'+strSystemId).value;
        var comment_template = document.getElementById('comment_template'+strSystemId).value;
        var comment_page = document.getElementById('comment_page'+strSystemId).value;
        var comment_systemid = document.getElementById('comment_systemid'+strSystemId).value;
        var post_target = 'xml.php?module=postacomment&action=savepost';
        //concat to send all values
        var post_body = 'comment_name='+comment_name+'&comment_subject='+comment_subject+'&comment_message='+comment_message
                        +'&comment_systemid='+comment_systemid+'&comment_page='+comment_page
                        +'&form_captcha='+form_captcha+'&comment_template='+comment_template;
						
		//show loading-message
		this.setLoadingIcon(strSystemId);
		
		//hide button
		document.getElementById('postaCommentButton'+strSystemId).style.display = "none";
		
		if(this.connectionObject == null || !YAHOO.util.Connect.isCallInProgress(this.connectionObject)) {
			this.connectionObject = YAHOO.util.Connect.asyncRequest('POST', post_target, postacommentCallback, post_body);
		}
	}
	
	this.setLoadingIcon = function() {
		document.getElementById('postacommentFormWrapper'+this.strSystemId).innerHTML = '<div align="center" style="padding: 20px;"><img src="portal/pics/kajona/loading.gif" /></div>';
	}
	
	this.setResponseText = function(o) {
		//just the stuff between <postacomment>, plz
		var intStart = o.responseText.indexOf("<postacomment>")+14;
		var responseText = o.responseText.substr(intStart, o.responseText.indexOf("</postacomment>")-intStart);
		document.getElementById('postacommentFormWrapper'+this.strSystemId).innerHTML = responseText;
		//check if form is available -> validation errors occured, so show form and reload captcha
		if (document.getElementById("kajonaCaptchaContainer"+this.strSystemId) != undefined) {
			loadCaptcha(this.strSystemId);
			reloadCaptcha("kajonaCaptcha"+this.strSystemId);
			document.getElementById('postaCommentForm'+this.strSystemId).style.display = "block";
		}
	}
	
}

submitPostacommentForm = new submitPostacommentForm();

function postacommentSubmitWrapper(strSystemId) {
	if(typeof YAHOO == "undefined" || typeof YAHOO.util.Connect == "undefined") {
        window.setTimeout("postacommentSubmitWrapper('"+strSystemId+"')", 1000);
        return;
    }
    
    submitPostacommentForm.submit(strSystemId);
}