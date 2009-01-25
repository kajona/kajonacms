//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id: kajona.js 2353 2008-12-31 15:22:01Z sidler $

var kajonaPortalEditorHelper = {
	portalEditorHover: function (elementSysId) {
	    divElement = document.getElementById('container_'+elementSysId);
	    divElement.className="peContainerHover";
	    menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuHover";
	},
	
	portalEditorOut: function (elementSysId) {
	    divElement = document.getElementById('container_'+elementSysId);
		divElement.className="peContainerOut";
	    menuElement = document.getElementById('menu_'+elementSysId);
	    menuElement.className="menuOut";
	},
	
	portalEditorStatus: function (status) {
	    var status = status == true ? 'true' : 'false';
		var url = window.location.href;
		var anchorPos = url.indexOf('#');
		if (anchorPos != -1) {
	    	url = url.substring(0, anchorPos);
		}
	
	    url = url.replace('&pe=false', '');
	    url = url.replace('&pe=true', '');
	    url = url.replace('?pe=false', '');
	    url = url.replace('?pe=true', '');
	
	    if(url.indexOf('?') == -1)
	        window.location.replace(url+'?pe='+status);
	    else
	        window.location.replace(url+'&pe='+status);
	}
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