//   (c) 2007-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt

if (typeof KAJONA.portal.rating == "undefined") {
    KAJONA.portal.rating = {};
}

/*
 * Sends a user rating to the server and refreshes the rating bar
 * 
 * @param {String} strSystemid
 * @param {Number} floatRating
 * @param {Number} intNumberOfIcons
 */
KAJONA.portal.rating.rate = function (strSystemid, floatRating, intNumberOfIcons) {
    //hide tooltip
    KAJONA.portal.tooltip.hide();

    //disable rating buttons
    var objRatingBar = document.getElementById("kajona_rating_"+strSystemid);
    var arrRatingIcons = objRatingBar.getElementsByTagName("li");
    for (var intI = intNumberOfIcons; intI >= 1; intI--) {
        objRatingBar.removeChild(arrRatingIcons[intI]);
    }

    //create a new ajax request
    var post_target = KAJONA_WEBPATH+'/xml.php?module=rating&action=saveRating';

    var post_data = {
        systemid : strSystemid,
        rating: floatRating
    };

    $.post(post_target, post_data, function(data, textStatus) {

        var floatNewRating = data.documentElement.firstChild.nodeValue;
        arrRatingIcons[0].style.width = floatNewRating/intNumberOfIcons*100+"%";
        $("#kajona_rating_rating_"+strSystemid).html(floatNewRating);

        var hits = document.getElementById("kajona_rating_hits_"+strSystemid);
        hits.innerHTML = parseInt(hits.innerHTML) + 1;

    }, "xml");


};