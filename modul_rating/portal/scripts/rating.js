//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2008 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$ 




function kajonaRatingMOver(strImageOverId, intNrOfIcons) {
    //schema of icon-ids: kajona_downloads_rating_icon_SYSID_NR
    var arrId = strImageOverId.split("_");
    var intMaxFilled = arrId[4];
    
    for(var intI = 1; intI < intNrOfIcons; intI++) {
        var strIconId = 'kajona_rating_icon_'+arrId[3]+'_'+intI;
        //alert(strIconId);
        var currentIcon = document.getElementById(strIconId).src;
        if(intI <= intMaxFilled) {
            if(currentIcon.indexOf('filled') == -1)
                currentIcon = currentIcon.replace('empty', 'filled');
        } 
        else {
            if(currentIcon.indexOf('empty') == -1)
                currentIcon = currentIcon.replace('filled', 'empty');
        }
        document.getElementById(strIconId).src = currentIcon;    
    }
}

function kajonaRatingMOut(strImageOverId, intNrOfIcons, intRatingToSet) {
    //schema of icon-ids: kajona_rating_icon_SYSID_NR
    var arrId = strImageOverId.split("_");
    var intMaxFilled = arrId[4];
    
    for(var intI = 1; intI < intNrOfIcons; intI++) {
        var strIconId = 'kajona_rating_icon_'+arrId[3]+'_'+intI;
        //alert(strIconId);
        var currentIcon = document.getElementById(strIconId).src;
        if(intI < intRatingToSet) {
            if(currentIcon.indexOf('filled') == -1)
                currentIcon = currentIcon.replace('empty', 'filled');
        } 
        else {
            if(currentIcon.indexOf('empty') == -1)
                currentIcon = currentIcon.replace('filled', 'empty');
        }
        document.getElementById(strIconId).src = currentIcon;    
    }
}


function kajonaRating(strSystemid, floatRating, intNrOfIcons) {
        kajonaAjaxHelper.loadAjaxBase();
        //create a new ajax request. collect data.
        var post_target = 'xml.php?module=rating&action=saveRating';
        //concat to send all values
        var post_body = 'systemid='+strSystemid+'&rating='+floatRating;
        
        //disable new ratings :)
        for(var intI = 1; intI < intNrOfIcons; intI++) {
            var strIconId = 'kajona_rating_icon_'+strSystemid+'_'+intI;
            document.getElementById(strIconId).onmouseout = function(){};
            document.getElementById(strIconId).onmouseover = function(){};
        }
        
                        
        YAHOO.util.Connect.asyncRequest('POST', post_target, {
            success: function(o) {
            },
            failure: function(o) {
            }
        }, post_body);
}