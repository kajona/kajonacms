//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2010 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 

function kajonaRating(strSystemid, floatRating, intNumberOfIcons) {
        //create a new ajax request. collect data.
        var post_target = 'xml.php?module=rating&action=saveRating';
        //concat to send all values
        var post_body = 'systemid='+strSystemid+'&rating='+floatRating;
        
        //disable rating buttons
		var ratingBar = document.getElementById("kajona_rating_"+strSystemid);
		var ratingIcons = ratingBar.getElementsByTagName("li");
        for(var intI = intNumberOfIcons; intI >= 1; intI--) {
			ratingBar.removeChild(ratingIcons[intI]);
        }
        
        YAHOO.util.Connect.asyncRequest('POST', post_target, {
            success: function(o) {
				//display new rating
        		var floatNewRating = o.responseXML.documentElement.firstChild.nodeValue;	
        		ratingIcons[0].style.width = floatNewRating/intNumberOfIcons*100+"%";
	        	try {
					document.getElementById("kajona_rating_rating_"+strSystemid).innerHTML = floatNewRating;
	        	} catch (e) {}
	        	try {
					var hits = document.getElementById("kajona_rating_hits_"+strSystemid);
					hits.innerHTML = parseInt(hits.innerHTML) + 1;
	        	} catch (e) {}
            },
            failure: function(o) {
            }
        }, post_body);
}