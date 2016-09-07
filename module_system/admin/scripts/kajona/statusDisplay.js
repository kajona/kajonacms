
/**
 * General way to display a status message.
 * Therefore, the html-page should provide the following elements as noted as instance-vars:
 * - div,   id: jsStatusBox    				the box to be animated
 * 		 class: jsStatusBoxMessage			class in case of an informal message
 * 		 class: jsStatusBoxError		    class in case of an error message
 * - div,   id: jsStatusBoxContent			the box to place the message-content into
 *
 * Pass a xml-response from a Kajona server to displayXMLMessage() to start the logic
 * or use messageOK() / messageError() passing a regular string
 */
define(['jquery'], function ($) {

    return {
        idOfMessageBox : "jsStatusBox",
        idOfContentBox : "jsStatusBoxContent",
        classOfMessageBox : "jsStatusBoxMessage",
        classOfErrorBox : "jsStatusBoxError",
        timeToFadeOutMessage : 3000,
        timeToFadeOutError   : 5000,
        timeToFadeOut : null,

        /**
         * General entrance point. Use this method to pass an xml-response from the kajona server.
         * Tries to find a message- or an error-tag an invokes the corresponding methods
         *
         * @param {String} message
         */
        displayXMLMessage : function(message) {
            //decide, whether to show an error or a message, message only in debug mode
            if(message.indexOf("<message>") != -1 && message.indexOf("<error>") == -1) {
                var intStart = message.indexOf("<message>")+9;
                var responseText = message.substr(intStart, message.indexOf("</message>")-intStart);
                this.messageOK(responseText);
            }

            if(message.indexOf("<error>") != -1) {
                var intStart = message.indexOf("<error>")+7;
                var responseText = message.substr(intStart, message.indexOf("</error>")-intStart);
                this.messageError(responseText);
            }
        },

        /**
         * Creates a informal message box contaning the passed content
         *
         * @param {String} strMessage
         */
        messageOK : function(strMessage) {
            $("#"+this.idOfMessageBox).removeClass(this.classOfMessageBox).removeClass(this.classOfErrorBox).addClass(this.classOfMessageBox);
            this.timeToFadeOut = this.timeToFadeOutMessage;
            this.startFadeIn(strMessage);
        },

        /**
         * Creates an error message box containg the passed content
         *
         * @param {String} strMessage
         */
        messageError : function(strMessage) {
            $("#"+this.idOfMessageBox).removeClass(this.classOfMessageBox).removeClass(this.classOfErrorBox).addClass(this.classOfErrorBox);
            this.timeToFadeOut = this.timeToFadeOutError;
            this.startFadeIn(strMessage);
        },

        startFadeIn : function(strMessage) {
            var statusBox = $("#"+this.idOfMessageBox);
            var contentBox = $("#"+this.idOfContentBox);
            contentBox.html(strMessage);
            statusBox.css("display", "").css("opacity", 0.0);

            //place the element at the top of the page
            var screenWidth = $(window).width();
            var divWidth = statusBox.width();
            var newX = screenWidth/2 - divWidth/2;
            var newY = $(window).scrollTop() -2;
            statusBox.css('top', newY);
            statusBox.css('left', newX);

            //start fade-in handler

            this.fadeIn();

        },

        fadeIn : function () {
            var me = this;
            $("#"+this.idOfMessageBox).animate({opacity: 0.8}, 1000, function() {
                window.setTimeout(me.startFadeOut, me.timeToFadeOut);
            });
        },

        startFadeOut : function() {
            var me = this;
            $("#"+this.idOfMessageBox).animate(
                { top: -200 },
                1000,
                function() {
                    $("#"+me.idOfMessageBox).css("display", "none");
                }
            );

        }
    };

});
