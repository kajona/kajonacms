/**
 * (c) 2013-2017 by Kajona, www.kajona.de
 * Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
 */

/**
 * Wrapper for desktop notifications.
 *
 * @see https://developer.mozilla.org/en-US/docs/WebAPI/Using_Web_Notifications
 * @module desktopNotification
 */
define('desktopNotification', ['jquery'], function ($) {

    return /** @alias module:desktopNotification */ {
        bitGranted : false,

        /**
         * Sends a message to the client. Asks for permissions if not yet given.
         *
         * @param strTitle
         * @param strBody
         * @param {callback} onClick
         */
        showMessage : function (strTitle, strBody, onClick) {

            this.grantPermissions();

            //for fucking IE
            if(typeof Notification == "undefined")
                return;

            var me = this;
            if (Notification && Notification.permission === "granted") {
                this.bitGranted = true;
            }
            else if (Notification && Notification.permission !== "denied") {
                Notification.requestPermission(function (status) {
                    if (Notification.permission !== status) {
                        Notification.permission = status;
                    }

                    // If the user said okay
                    if (status === "granted") {
                        me.bitGranted = true;
                    }
                });
            }


            if(this.bitGranted) {
                var n = new Notification(strTitle, {body: strBody});

                if(onClick)
                    n.onclick = onClick;
            }
        },

        grantPermissions: function() {

            //for fucking IE
            if (typeof Notification == "undefined")
                return;

            if (Notification && Notification.permission !== "granted") {
                Notification.requestPermission(function (status) {
                    if (Notification.permission !== status) {
                        Notification.permission = status;
                    }
                });
            }
        }
    };

});
