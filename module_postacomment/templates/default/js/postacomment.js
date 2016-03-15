//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

if (typeof KAJONA.portal.postacomment == "undefined") {
    KAJONA.portal.postacomment = {};
}

KAJONA.portal.postacomment = (function() {

    /**
     * Sends the comment to the server and refreshes the page
     *
     * @param {String} strSystemId
     * @public
     */
    function submit(strSystemId) {
        var post_target = KAJONA_WEBPATH+"/xml.php?module=postacomment&action=savepost";
        var post_data = {};
        //get the comment form and fetch all form elements
        var arrCommentFormElements = document.getElementById("formComment_"+strSystemId).elements;
        for (var i = 0; i < arrCommentFormElements.length; i++) {
            post_data[arrCommentFormElements[i].name] = arrCommentFormElements[i].value;
        }

        //show loading animation
        $("#postacommentFormWrapper_"+strSystemId).html('<div align="center"><i class="fa fa-spinner fa-spin"></i></div>');

        //hide button
        $('#postaCommentButton_'+strSystemId).css('display', 'none');

        $.post(post_target, post_data, function(data, textStatus) {
            setResponseText(data, strSystemId);
        }, "text");


    }

    /**
     * Internal function to display the response
     *
     * @param {String} strResponse
     * @param {String} strSystemId
     * @private
     */
    function setResponseText(strResponse, strSystemId) {
        //just the stuff between <postacomment>
        $("#postacommentFormWrapper_"+strSystemId).html(strResponse);

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