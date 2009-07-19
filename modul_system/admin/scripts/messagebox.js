//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$


/**
 * This script contains a way to display a message.
 * Therefore, the html-page should provide the following elements as noted as instance-vars:
 * - div,   id: jsStatusBox    				the box to be animated
 * 		 class: jsStatusBoxMessage			class in case of an informal message
 * 		 class: jsStatusBoxError		    class in case of an error message
 * - div,   id: jsStatusBoxContent			the box to place the message-content into
 * 
 * Pass a xml-response from a kajona-server to displayXMLMessage() to start the logic
 * or use messageOK() / messageError() passing a regular string
 */
var kajonaStatusDisplay = {
	
	idOfMessageBox : "jsStatusBox",
	idOfContentBox : "jsStatusBoxContent",
	classOfMessageBox : "jsStatusBoxMessage",
	classOfErrorBox : "jsStatusBoxError",
	timeToFadeOutMessage : 4000,
	timeToFadeOutError : 10000,
	timeToFadeOut : null,
	animObject : null,
	
	/**
	 * General entrance point. Use this method to pass an xml-response from the kajona server.
	 * Tries to find a message- or an error-tag an invokes the corresponding methods
	 * 
	 * @param {String} message
	 */
	displayXMLMessage : function(message) {
		//decide, whether to show an error or a message, message only in debug mode
		if(message.indexOf("<message>") != -1 && KAJONA_DEBUG > 0) {
			var intStart = message.indexOf("<message>")+9;
			var responseText = message.substr(intStart, message.indexOf("</message>")-intStart);
			kajonaStatusDisplay.messageOK(responseText);
		}
		
		if(message.indexOf("<error>") != -1) {
			var intStart = message.indexOf("<error>")+7;
			var responseText = message.substr(intStart, message.indexOf("</error>")-intStart);
			kajonaStatusDisplay.messageError(responseText);
		}
	},
	
	/**
	 * Creates a informal message box containg the passed content
	 * 
	 * @param {String} strMessage
	 */
    messageOK : function(strMessage) {
		YAHOO.util.Dom.removeClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfMessageBox)
		YAHOO.util.Dom.removeClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfErrorBox)
		YAHOO.util.Dom.addClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfMessageBox);
		kajonaStatusDisplay.timeToFadeOut = kajonaStatusDisplay.timeToFadeOutMessage;
		kajonaStatusDisplay.startFadeIn(strMessage);
    },

	/**
	 * Creates an error message box containg the passed content
	 * 
	 * @param {String} strMessage
	 */
    messageError : function(strMessage) {
		YAHOO.util.Dom.removeClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfMessageBox)
		YAHOO.util.Dom.removeClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfErrorBox)
		YAHOO.util.Dom.addClass(kajonaStatusDisplay.idOfMessageBox, kajonaStatusDisplay.classOfErrorBox);
		kajonaStatusDisplay.timeToFadeOut = kajonaStatusDisplay.timeToFadeOutError;
		kajonaStatusDisplay.startFadeIn(strMessage);
    },
	
	startFadeIn : function(strMessage) {
		kajonaAjaxHelper.loadAnimationBase();
		//currently animated?
		if(kajonaStatusDisplay.animObject != null && kajonaStatusDisplay.animObject.isAnimated()) {
			kajonaStatusDisplay.animObject.stop(true);
			kajonaStatusDisplay.animObject.onComplete.unsubscribeAll();
		}
		var statusBox = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfMessageBox);
		var contentBox = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfContentBox);
		contentBox.innerHTML = strMessage;
		YAHOO.util.Dom.setStyle(statusBox, "display", "");
		YAHOO.util.Dom.setStyle(statusBox, "opacity", 0.0);
		
		//place the element at the top of the page
		var screenWidth = YAHOO.util.Dom.getViewportWidth();
		var divWidth = statusBox.offsetWidth;
		var newX = screenWidth/2 - divWidth/2;
		var newY = YAHOO.util.Dom.getDocumentScrollTop() -2;
		YAHOO.util.Dom.setXY(statusBox, new Array(newX, newY));

		//start fade-in handler
		kajonaStatusDisplay.fadeIn();
	},
	
	fadeIn : function () {
		kajonaStatusDisplay.animObject = new YAHOO.util.Anim(kajonaStatusDisplay.idOfMessageBox, { opacity: { to: 0.8 } }, 1, YAHOO.util.Easing.easeOut);
		kajonaStatusDisplay.animObject.onComplete.subscribe(function() {window.setTimeout("kajonaStatusDisplay.startFadeOut()", timeToFadeOut);});
		kajonaStatusDisplay.animObject.animate();
	},
	
	startFadeOut : function() {
		var statusBox = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfMessageBox);
		
		//get the current pos
		var attributes = {
	        points: { by: [0, (YAHOO.util.Dom.getY(statusBox)+statusBox.offsetHeight)*-1-5] }
	    };
	    kajonaStatusDisplay.animObject = new YAHOO.util.Motion(statusBox, attributes, 0.5);
	    kajonaStatusDisplay.animObject.onComplete.subscribe(function() {YAHOO.util.Dom.setStyle(kajonaStatusDisplay.idOfMessageBox, "display", "none");});
		kajonaStatusDisplay.animObject.animate();
	}
};