//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 1701 2007-08-24 21:22:05Z sidler $


/**
 * This script contains a way to display a message.
 * Therefore, the html-page should provide the following elements as noted as instance-vars:
 * - div,   id: jsStatusBox    				the box to be animated
 * 		 class: jsStatusBoxMessage			class in case of an informal message
 * 		 class: jsStatusBoxError		    class in case of an error message
 * - div, id: jsStatusBoxContent			the box to place the message-content into
 * 
 * Pass a xml-response from a kajona-server to displayXMLMessage() to star the logic
 * or use messageOK() / messageError() passing a regular string
 */
var kajonaStatusDisplay = {
	
	idOfMessageBox : "jsStatusBox",
	idOfContentBox : "jsStatusBoxContent",
	classOfMessageBox : "jsStatusBoxMessage",
	classOfErrorBox : "jsStatusBoxError",
	animObject : null,
	
	/**
	 * General entrance point. Use this method to pass an xml-response from the kajona server.
	 * Tries to find a message- or an error-tag an invokes the corresponding methods
	 * 
	 * @param {String} message
	 */
	displayXMLMessage : function(message) {
		//decide, whether to show an error or a message
		if(message.indexOf("<message>") != -1) {
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
		kajonaStatusDisplay.startFadeIn(strMessage);
    },
	
	startFadeIn : function(strMessage) {
		kajonaAjaxHelper.loadAnimationBase();
		//currently animated?
		if(kajonaStatusDisplay.animObject != null && kajonaStatusDisplay.animObject.isAnimated())
			kajonaStatusDisplay.animObject.stop(true);
		var statusBox = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfMessageBox);
		var contentBox = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfContentBox);
		contentBox.innerHTML = strMessage;
		YAHOO.util.Dom.setStyle(statusBox, "display", "");
		YAHOO.util.Dom.setStyle(statusBox, "opacity", 0.0);
		//place the element at the right bottom of the page
		var screenWidth = YAHOO.util.Dom.getViewportWidth();
		var screenHeight = YAHOO.util.Dom.getViewportHeight();
		var divWidth = statusBox.offsetWidth;
		var divHeight = statusBox.offsetHeight;
		var newX = screenWidth - divWidth - 10;
		var newY = screenHeight - divHeight - 10;
		YAHOO.util.Dom.setXY(statusBox, new Array(newX, newY));
		//start fade-in handler		
		kajonaStatusDisplay.fadeIn(statusBox);
	},
	
	fadeIn : function () {
		var objectToSet = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfMessageBox);
		//get current opacity
		var opacity = parseFloat(YAHOO.util.Dom.getStyle(objectToSet, "opacity"));
		opacity += 0.02;
		YAHOO.util.Dom.setStyle(objectToSet, "opacity", opacity);
		//and load us again, or call the startFfadeOut after 2 secs
		if(opacity < 1.0)
			window.setTimeout("kajonaStatusDisplay.fadeIn()", 30);
		else
			window.setTimeout("kajonaStatusDisplay.startFadeOut()", 1000);	
	},
	
	startFadeOut : function() {
		//get the current pos
		var attributes = { 
	        points: { by: [0, -400] } 
	    }; 
	    kajonaStatusDisplay.animObject = new YAHOO.util.Motion(kajonaStatusDisplay.idOfMessageBox, attributes, 2);
		kajonaStatusDisplay.fadeOut(); 
		kajonaStatusDisplay.animObject.animate();
	},
	
	fadeOut : function () {
		var objectToSet = YAHOO.util.Dom.get(kajonaStatusDisplay.idOfMessageBox);
		//get current opacity
		var opacity = parseFloat(YAHOO.util.Dom.getStyle(objectToSet, "opacity"));
		opacity -= 0.05;
		YAHOO.util.Dom.setStyle(objectToSet, "opacity", opacity);
		//and load us again, or end all ;)
		if(opacity > 0.0)
			window.setTimeout("kajonaStatusDisplay.fadeOut()", 30);
	}
};
 
