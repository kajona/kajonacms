//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

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
    var post_body = 'systemid='+strSystemid+'&rating='+floatRating;
    
    YAHOO.util.Connect.asyncRequest('POST', post_target, {
        success: function(o) {
			//display new rating
    		var floatNewRating = o.responseXML.documentElement.firstChild.nodeValue;	
    		arrRatingIcons[0].style.width = floatNewRating/intNumberOfIcons*100+"%";
        	try {
				document.getElementById("kajona_rating_rating_"+strSystemid).innerHTML = floatNewRating;
        	} catch (e) {}
        	try {
				var hits = document.getElementById("kajona_rating_hits_"+strSystemid);
				hits.innerHTML = parseInt(hits.innerHTML) + 1;
        	} catch (e) {}
        },
        failure: function(o) { }
    }, post_body);
}