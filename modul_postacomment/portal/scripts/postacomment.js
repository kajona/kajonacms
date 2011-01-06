//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2011 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

if (typeof KAJONA.portal.postacomment == "undefined") {
	KAJONA.portal.postacomment = {};
}

KAJONA.portal.postacomment = (function() {
	var connectionObject;
	
	/*
	 * Sends the comment to the server and refreshes the page
	 * 
	 * @param {String} strSystemId
	 * @public
	 */
	function submit(strSystemId) {
		var post_target = KAJONA_WEBPATH+"/xml.php?module=postacomment&action=savepost";
		var post_body = "";
		//get the comment form and fetch all form elements
		var arrCommentFormElements = document.getElementById("formComment_"+strSystemId).elements;
		for (var i = 0; i < arrCommentFormElements.length; i++) {
			if (i > 0) {
				post_body += "&";
			}
			post_body += arrCommentFormElements[i].name+"="+arrCommentFormElements[i].value;
		}
        
		//show loading animation
        document.getElementById("postacommentFormWrapper_"+strSystemId).innerHTML = '<div align="center" class="loading"><img src="'+KAJONA_WEBPATH+'/portal/pics/kajona/loading.gif" /></div>';
		
		//hide button
		document.getElementById('postaCommentButton_'+strSystemId).style.display = "none";
		
		var callback = {
		  success: function(objResponse) {setResponseText(objResponse, strSystemId);},
		  failure: function(objResponse) {setResponseText(objResponse, strSystemId);}
		};
		
		if(connectionObject == null || !YAHOO.util.Connect.isCallInProgress(connectionObject)) {
			connectionObject = YAHOO.util.Connect.asyncRequest('POST', post_target, callback, post_body);
		}
	}

	/*
	 * Internal function to display the response
	 * 
	 * @param {Object} objResponse
	 * @param {String} strSystemId
	 * @private
	 */
	function setResponseText(objResponse, strSystemId) {
		var strResponse = objResponse.responseText;
		//just the stuff between <postacomment>
		var intStart = strResponse.indexOf("<postacomment>")+14;
		var responseText = strResponse.substr(intStart, strResponse.indexOf("</postacomment>")-intStart);
		document.getElementById('postacommentFormWrapper_'+strSystemId).innerHTML = responseText;
		
		//check if form is available -> validation errors occured, so show form and reload captcha
		if (document.getElementById("postaCommentForm_"+strSystemId) != undefined) {
			KAJONA.portal.loadCaptcha(strSystemId);
            document.getElementById('postaCommentForm_'+strSystemId).style.display = "block";
		}
        
	}
	
	//public variables and methods
	return {
		submit : submit
	}
}());