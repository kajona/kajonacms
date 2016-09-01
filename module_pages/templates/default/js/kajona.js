//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2016 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (typeof KAJONA == "undefined") {
    var KAJONA = {
        util: {},
        portal: {
            lang: {}
        },
        admin: {
            lang: {}
        }
    };
}


/*
 * -------------------------------------------------------------------------
 * Global functions
 * -------------------------------------------------------------------------
 */


/**
 * Checks if the given array contains the given string
 *
 * @param {String} strNeedle
 * @param {String[]} arrHaystack
 */
KAJONA.util.inArray = function (strNeedle, arrHaystack) {
    for (var i = 0; i < arrHaystack.length; i++) {
        if (arrHaystack[i] == strNeedle) {
            return true;
        }
    }
    return false;
};

/**
 * Used to show/hide an html element
 *
 * @param {String} strElementId
 * @param {Function} objCallbackVisible
 * @param {Function} objCallbackInvisible
 */
KAJONA.util.fold = function (strElementId, objCallbackVisible, objCallbackInvisible) {
    var element = document.getElementById(strElementId);
    if (element.style.display == 'none') 	{
        element.style.display = 'block';
        if ($.isFunction(objCallbackVisible)) {
            objCallbackVisible();
        }
    }
    else {
        element.style.display = 'none';
        if ($.isFunction(objCallbackInvisible)) {
            objCallbackInvisible();
        }
    }
};

KAJONA.util.isTouchDevice = function() {
    return !!('ontouchstart' in window) ? 1 : 0;
};


/*
 * -------------------------------------------------------------------------
 * Portal-specific functions
 * -------------------------------------------------------------------------
 */


/**
 * Loads/Reloads the Kajona captcha image with the given element id
 *
 * @param {String} strCaptchaId
 * @param {Number} intWidth
 */
KAJONA.portal.loadCaptcha = function (strCaptchaId, intWidth) {
    var containerName = "kajonaCaptcha";
    var imgID = "kajonaCaptchaImg";

    if(strCaptchaId != null) {
        containerName += "_"+strCaptchaId;
        imgID += "_"+strCaptchaId;
    } else {
        //fallback for old templates (old function call)
        imgID = "kajonaCaptcha";
    }
    if (!intWidth) {
        var intWidth = 180;
    }

    var timeCode = new Date().getTime();
    if (document.getElementById(imgID) == undefined && document.getElementById(containerName) != null) {
        var objImg=document.createElement("img");
        objImg.setAttribute("id", imgID);
        objImg.setAttribute("src", KAJONA_WEBPATH+"/image.php?image=kajonaCaptcha&maxWidth="+intWidth+"&reload="+timeCode);
        document.getElementById(containerName).appendChild(objImg);
    } else if(document.getElementById(imgID) != undefined) {
        var objImg = document.getElementById(imgID);
        objImg.src = objImg.src + "&reload="+timeCode;
    }
};

/**
 * Tooltips
 *
 * originally based on Bubble Tooltips by Alessandro Fulciniti (http://pro.html.it - http://web-graphics.com)
 */
KAJONA.portal.tooltip = (function() {
    var container;
    var lastMouseX = 0;
    var lastMouseY = 0;

    function locate(e) {
        var posx = 0, posy = 0, c;
        if (e == null) {
            e = window.event;
        }
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        } else if (e.clientX || e.clientY) {
            if (document.documentElement.scrollTop) {
                posx = e.clientX + document.documentElement.scrollLeft;
                posy = e.clientY + document.documentElement.scrollTop;
            } else {
                posx = e.clientX + document.body.scrollLeft;
                posy = e.clientY + document.body.scrollTop;
            }
        }

        //save current x and y pos (needed to show tooltip at right position if it's added by onclick)
        if (posx == 0 && posy == 0) {
            posx = lastMouseX;
            posy = lastMouseY;
        } else {
            lastMouseX = posx;
            lastMouseY = posy;
        }

        c = container;
        var left = (posx - c.offsetWidth);
        if (left - c.offsetWidth < 0) {
            left += c.offsetWidth;
        }
        c.style.top = (posy + 10) + "px";
        c.style.left = left + "px";
    }

    function add(objElement, strHtmlContent, bitOpacity) {
        var tooltip;

        if (strHtmlContent == null || strHtmlContent.length == 0) {
            try {
                strHtmlContent = objElement.getAttribute("title");
            } catch (e) {}
        }
        if (strHtmlContent == null || strHtmlContent.length == 0) {
            return;
        }

        //try to remove title
        try {
            objElement.removeAttribute("title");
        } catch (e) {}

        tooltip = document.createElement("span");
        tooltip.className = "kajonaTooltip";
        tooltip.style.display = "block";
        tooltip.innerHTML = strHtmlContent;

        if (bitOpacity != false) {
            tooltip.style.filter = "alpha(opacity:85)";
            tooltip.style.KHTMLOpacity = "0.85";
            tooltip.style.MozOpacity = "0.85";
            tooltip.style.opacity = "0.85";
        }

        //create tooltip container and save reference
        if (container == null) {
            var h = document.createElement("span");
            h.id = "kajonaTooltipContainer";
            h.setAttribute("id", "kajonaTooltipContainer");
            h.style.position = "absolute";
            h.style.zIndex = "2000";
            document.getElementsByTagName("body")[0].appendChild(h);
            container = h;
        }

        objElement.tooltip = tooltip;
        objElement.onmouseover = show;
        objElement.onmouseout = hide;
        objElement.onmousemove = locate;
        objElement.onmouseover(objElement);
    }

    function show(objEvent) {
        hide();
        container.appendChild(this.tooltip);
        locate(objEvent);
    }

    function hide() {
        try {
            var c = container;
            if (c.childNodes.length > 0) {
                c.removeChild(c.firstChild);
            }
        } catch (e) {}
    }

    //public variables and methods
    return {
        add : add,
        show : show,
        hide : hide
    }
}());


KAJONA.portal.loader = new KAJONA.util.Loader();

