//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$


function portalEditorHover(elementSysId) {
    divElement = document.getElementById('container_'+elementSysId);
    divElement.className="peContainerHover";
    menuElement = document.getElementById('menu_'+elementSysId);
    menuElement.className="menuHover";
}

function portalEditorOut(elementSysId) {
    divElement = document.getElementById('container_'+elementSysId);
	divElement.className="peContainerOut";
    menuElement = document.getElementById('menu_'+elementSysId);
    menuElement.className="menuOut";
}

function portalEditorDisable() {
    url = window.location.href;
    url = url.replace('#', '');
    url = url.replace('&pe=false', '');
    url = url.replace('&pe=true', '');
    url = url.replace('?pe=false', '');
    url = url.replace('?pe=true', '');
    //check if mod_rewrite is enabled
    if(url.indexOf(".html") != -1) {
        url = getIndexAsNonRewrite(url);
    }
    if(url.indexOf('?') == -1)
        window.location.replace(url+'?pe=false');
    else
        window.location.replace(url+'&pe=false');
}

function portalEditorEnable() {
    url = window.location.href;
    url = url.replace('#', '');
    url = url.replace('&pe=false', '');
    url = url.replace('&pe=true', '');
    url = url.replace('?pe=false', '');
    url = url.replace('?pe=true', '');
    //check if mod_rewrite is enabled
    if(url.indexOf(".html") != -1) {
        url = getIndexAsNonRewrite(url);
    }
    if(url.indexOf('?') == -1)
        window.location.replace(url+'?pe=true');
    else
        window.location.replace(url+'&pe=true');
}

function getIndexAsNonRewrite(currentUrl) {
    tempUrl = currentUrl.substr(currentUrl.lastIndexOf('/')+1);

    //Match regular expressions
    if(tempUrl.search(/([0-9a-z-_]+)\.([0-9a-z-_]*)\.([a-zA-Z]*)\.([0-9a-z]*).([a-z]*)\.html/) != -1) {
        tempUrl = "index.php?page="+RegExp.$1+"&action="+RegExp.$3+"&systemid="+RegExp.$4+"&language="+RegExp.$5;
    }
    else if(tempUrl.search(/([0-9a-z-_]+)\.([0-9a-z-_]*)\.([a-zA-Z]*)\.([0-9a-z]*)\.html/) != -1) {
        tempUrl = "index.php?page="+RegExp.$1+"&action="+RegExp.$3+"&systemid="+RegExp.$4;
    }
    else if(tempUrl.search(/([0-9a-z-_]+)\.([0-9a-z-_]*)\.([a-zA-Z]*)\.html/) != -1) {
        tempUrl = "index.php?page="+RegExp.$1+"&action="+RegExp.$3;
    }
    else if(tempUrl.search(/([0-9a-z-_]+)\.([a-z]{2,2})\.html/) != -1) {
        tempUrl = "index.php?page="+ RegExp.$1+"&language="+RegExp.$2;
    }
    else if(tempUrl.search(/([0-9a-z-_]+)\.([0-9a-z-_]*)\.html/) != -1) {
        tempUrl = "index.php?page="+ RegExp.$1;
    }
    else if(tempUrl.search(/([0-9a-z-_]+)\.html/) != -1) {
        tempUrl = "index.php?page="+ RegExp.$1;
    }
    currentUrl = currentUrl.substr(0, currentUrl.lastIndexOf('/')+1)+tempUrl;
    return currentUrl;
}

function reloadCaptcha(imageID) {
	timeCode = new Date().getTime();
	codeImg = document.getElementById(imageID);
 	codeImg.src = codeImg.src+"&reload="+timeCode;
}

//--- LITTLE HELPERS ------------------------------------------------------------------------------------
function addCss(file) {
	var l=document.createElement("link");
	l.setAttribute("type", "text/css");
	l.setAttribute("rel", "stylesheet");
	l.setAttribute("href", file);
	document.getElementsByTagName("head")[0].appendChild(l);
}

function inArray(needle, haystack) {
    for (var i = 0; i < haystack.length; i++) {
        if (haystack[i] == needle) {
            return true;
        }
    }
    return false;
}

function addLoadEvent(func) {
	var oldonload = window.onload;
    if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if(oldonload) {
				oldonload();
			}
			func();
		};
	}
}

function toggle(id) {
	style = document.getElementById(id).style.display;
	if (style=='none') 	{
		document.getElementById(id).style.display='block';
    }
    else {
        document.getElementById(id).style.display='none';
    }
}


var kajonaAjaxHelper =  {
	
	arrayFilesToLoad : new Array(),
	arrayFilesLoaded : new Array(),
	bitPastOnload : false,
	
	onLoadHandlerFinal : function() {
		for(i=0;i<kajonaAjaxHelper.arrayFilesToLoad.length;i++) {
			if(kajonaAjaxHelper.arrayFilesToLoad[i] != null)
				kajonaAjaxHelper.addJavascriptFile(kajonaAjaxHelper.arrayFilesToLoad[i]);
		}
		kajonaAjaxHelper.bitPastOnload = true;
	},

	addJavascriptFile : function (file) {
		var l=document.createElement("script");
		l.setAttribute("type", "text/javascript");
		l.setAttribute("language", "javascript");
		l.setAttribute("src", file);
		document.getElementsByTagName("head").item(0).appendChild(l);	
		intCount = kajonaAjaxHelper.arrayFilesLoaded.length;
		kajonaAjaxHelper.arrayFilesLoaded[(intCount+1)] = file;
	},
	
	loadAjaxBase : function () {
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/yahoo/yahoo.js');
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/event/event.js');
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/connection/connection.js');
	},
	
	
	addFileToLoad : function(fileName) {
		if(kajonaAjaxHelper.bitPastOnload) {
			if(!inArray(fileName, kajonaAjaxHelper.arrayFilesLoaded)) {
				kajonaAjaxHelper.addJavascriptFile(fileName);
			}
		}
		else {
			intCount = kajonaAjaxHelper.arrayFilesToLoad.length;
			kajonaAjaxHelper.arrayFilesToLoad[(intCount+1)] = fileName;
		}
	}
};

addLoadEvent(kajonaAjaxHelper.onLoadHandlerFinal);
