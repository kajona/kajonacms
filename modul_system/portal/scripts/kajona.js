//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
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

//--- TOOLTIPS --------------------------------------------------------------------------------------
// based on Bubble Tooltips by Alessandro Fulciniti (http://pro.html.it - http://web-graphics.com)
function enableTooltips(className){
var links,i,h;
if(!document.getElementById || !document.getElementsByTagName) return;
// Check if tooltips are already enabled
if(document.getElementById("btc")!=null) return;
h=document.createElement("span");
h.id="btc";
h.setAttribute("id","btc");
h.style.position="absolute";
h.style.zIndex=2000;
document.getElementsByTagName("body")[0].appendChild(h);
links=document.getElementsByTagName("a");
for(i=0;i<links.length;i++){
if(className==null || className.length==0) {
Prepare(links[i]);
}
else {
if(links[i].className == className) {Prepare(links[i])};
}
}
}

function Prepare(el){
var tooltip,t,s;
t=el.getAttribute("title");
if(t==null || t.length==0) return;
el.removeAttribute("title");
tooltip=CreateEl("span","tooltip");
s=CreateEl("span","top");
s.appendChild(document.createTextNode(t));
tooltip.appendChild(s);
setOpacity(tooltip);
el.tooltip=tooltip;
el.onmouseover=showTooltip;
el.onmouseout=hideTooltip;
el.onmousemove=Locate;
}

function htmlTooltip (el, t) {
var tooltip,t,s;
if(t==null || t.length==0) return;
if(el.getAttribute("title")) el.removeAttribute("title");
tooltip=CreateEl("span","tooltip");
s=CreateEl("span","top");
s.innerHTML = t;
tooltip.appendChild(s);
el.tooltip=tooltip;
el.onmouseover=showTooltip;
el.onmouseout=hideTooltip;
el.onmousemove=Locate;
el.onmouseover(el);
}

function showTooltip(e){
document.getElementById("btc").appendChild(this.tooltip);
Locate(e);
}

function hideTooltip(e){
var d=document.getElementById("btc");
if(d.childNodes.length>0) d.removeChild(d.firstChild);
}

function setOpacity(el){
el.style.filter="alpha(opacity:85)";
el.style.KHTMLOpacity="0.85";
el.style.MozOpacity="0.85";
el.style.opacity="0.85";
}

function CreateEl(t,c){
var x=document.createElement(t);
x.className=c;
x.style.display="block";
return(x);
}

function Locate(e){
var posx=0,posy=0, t;
if(e==null) e=window.event;
if(e.pageX || e.pageY){
posx=e.pageX; posy=e.pageY;
}
else if(e.clientX || e.clientY){
if(document.documentElement.scrollTop){
posx=e.clientX+document.documentElement.scrollLeft;
posy=e.clientY+document.documentElement.scrollTop;
}
else{
posx=e.clientX+document.body.scrollLeft;
posy=e.clientY+document.body.scrollTop;
}
}
t = document.getElementById("btc");
t.style.top=(posy+10)+"px";
t.style.left=(posx-t.offsetWidth)+"px";
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

function fold(id, callbackShow) {
	style = document.getElementById(id).style.display;
	if (style=='none') 	{
		document.getElementById(id).style.display='block';
		if (callbackShow != undefined) {
			callbackShow();
		}
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
            if(kajonaAjaxHelper.arrayFilesToLoad[i] != null) {
                kajonaAjaxHelper.addJavascriptFile(kajonaAjaxHelper.arrayFilesToLoad[i]);
                kajonaAjaxHelper.arrayFilesToLoad[i] = null;
            }
        }
        kajonaAjaxHelper.bitPastOnload = true;
    },

    addJavascriptFile : function (file) {
        if(inArray(file, kajonaAjaxHelper.arrayFilesLoaded)) {
           return;
        }
        var l=document.createElement("script");
        l.setAttribute("type", "text/javascript");
        l.setAttribute("language", "javascript");
        l.setAttribute("src", file);
        document.getElementsByTagName("head").item(0).appendChild(l);
        intCount = kajonaAjaxHelper.arrayFilesLoaded.length;
        kajonaAjaxHelper.arrayFilesLoaded[intCount] = file;
    },
	
	loadAjaxBase : function () {
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/yahoo/yahoo-min.js');
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/event/event-min.js');
		kajonaAjaxHelper.addFileToLoad('portal/scripts/yui/connection/connection-min.js');
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
function enableTooltipsWrapper() { enableTooltips("showTooltip"); } addLoadEvent(enableTooltipsWrapper);