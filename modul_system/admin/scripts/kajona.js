//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007-2009 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

//--- GLOBAL ELEMENTS, MOVER ----------------------------------------------------------------------------
var currentMouseXPos;
var currentMouseYPos;
//used for "the mover" ;)
var objToMove=null;
var objDiffX=0;
var objDiffY=0;

checkMousePosition = function (e) {
	if(document.all){
		currentMouseXPos = event.clientX + document.body.scrollLeft;
		currentMouseYPos = event.clientY + document.body.scrollTop;
    }
	else{
       currentMouseXPos = e.pageX;
	   currentMouseYPos = e.pageY;
    }

    if(objToMove != null) {
	    objToMove.style.left=currentMouseXPos-objDiffX+"px";
  		objToMove.style.top=currentMouseYPos-objDiffY+"px";
	}
}

var objMover = {
	mousePressed : 0,
	objPosX:0,
	objPosY:0,
	diffX:0,
	diffY:0,

	setMousePressed : function (obj){
	    objToMove=obj;
	    objDiffX=currentMouseXPos - objToMove.offsetLeft;
		objDiffY=currentMouseYPos - objToMove.offsetTop;
	},

	unsetMousePressed : function (){
        objToMove=null;
	}
}

//--- MISC ----------------------------------------------------------------------------------------------
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

function foldImage(id, bildid, bild_da, bild_weg) {
	style = document.getElementById(id).style.display;
	if (style=='none') {
		document.getElementById(id).style.display='block';
        document.getElementById(bildid).src = bild_da;
    }
    else {
        document.getElementById(id).style.display='none';
        document.getElementById(bildid).src = bild_weg;
    }
}

function switchLanguage(strLanguageToLoad) {
    url = window.location.href;
    url = url.replace(/(\?|&)language=([a-z]+)/, "");
    if(url.indexOf('?') == -1)
        window.location.replace(url+'?language='+strLanguageToLoad);
    else
        window.location.replace(url+'&language='+strLanguageToLoad);
    
    //TODO: this works with mod-rewrite enabled? 
}

function addCss(file){
	var l=document.createElement("link");
	l.setAttribute("type", "text/css");
	l.setAttribute("rel", "stylesheet");
	l.setAttribute("href", file);
	document.getElementsByTagName("head").item(0).appendChild(l);
}

function addDownloadInput(idOfPrototype, nameOfCounterId) {
    //load inner html of prototype
    var uploadForm = document.getElementById(idOfPrototype).innerHTML;

    //calc new counter
    var counter = 0;
    while(document.getElementById(nameOfCounterId+'['+counter+']') != null)
        counter++;

    //set new id
    uploadForm = uploadForm.replace(new RegExp(nameOfCounterId+'\\[0\\]', "g"), nameOfCounterId+'['+counter+']');

    //and place in document
    var newNode = document.createElement("div");
    newNode.setAttribute("style", "display: inline;");
    newNode.innerHTML = uploadForm;

    var protoNode = document.getElementById(idOfPrototype)
    protoNode.parentNode.insertBefore(newNode, protoNode);

}

function inArray(needle, haystack) {
    for (var i = 0; i < haystack.length; i++) {
        if (haystack[i] == needle) {
            return true;
        }
    }
    return false;
}

//deprecated, use YAHOO.util.Event.onDOMReady instead, if YUI loaded
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

function ModalDialog(strDialogId, intDialogType) {
    this.dialog = null;
    this.containerId = strDialogId;
    
	this.setContent = function(strQuestion, strConfirmButton, strLinkHref) {
		if (intDialogType == 1) {
			document.getElementById(this.containerId+"_content").innerHTML = strQuestion;
			var confirmButton = document.getElementById(this.containerId+"_confirmButton");
			confirmButton.value = strConfirmButton;
			confirmButton.onclick = function() { window.location = strLinkHref; return false; };
		}
	}
	
    this.init = function() {	
        this.dialog = 
    		new YAHOO.widget.Panel(this.containerId,
    			{
    			  fixedcenter:true,
    			  close:false,
    			  draggable:false,
    			  zindex:4000,
    			  modal:true,
    			  visible:true
    			}
		);
 		
        this.dialog.render(document.body);
        this.dialog.show();
		this.dialog.focusLast();
    }
    
    this.hide = function() {
        this.dialog.hide();
    }
}

//--- RIGHTS-STUFF --------------------------------------------------------------------------------------
function checkRightMatrix() {
	//mode 1: inheritance
	if(document.getElementById('inherit').checked) {
		//loop over all checkboxes to disable them
		for(intI=0; intI<document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if(objCurElement.type == 'checkbox') {
				if(objCurElement.id != 'inherit') {
					objCurElement.disabled = true;
					objCurElement.checked = false;
					strCurId = "inherit,"+objCurElement.id;
					if(document.getElementById(strCurId) != null) {
						if(document.getElementById(strCurId).value == '1') {
							objCurElement.checked = true;
						}
					}
				}
			}
		}
	}
	else {
		//mode 2: no inheritance, make all checkboxes editable
		for(intI=0; intI<document.forms['rightsForm'].elements.length; intI++) {
			var objCurElement = document.forms['rightsForm'].elements[intI];
			if(objCurElement.type == 'checkbox') {
				if(objCurElement.id != 'inherit')
					objCurElement.disabled = false;
			}
		}
	}
}

//--- TOOLTIPS --------------------------------------------------------------------------------------
// based on Bubble Tooltips by Alessandro Fulciniti (http://pro.html.it - http://web-graphics.com)
function enableTooltips(className){
var links,i,h;
if(!document.getElementById || !document.getElementsByTagName) return;
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
    if(links[i].className == className) {
        Prepare(links[i])
    }
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
var tooltip, s;
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

//--- AJAX-STUFF --------------------------------------------------------------------------------------
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
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/connection/connection-min.js');
	},

	loadDragNDropBase : function () {
		kajonaAjaxHelper.loadAjaxBase();
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/animation/animation-min.js');
		//kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/dragdrop/dragdrop-min.js');
	},

	loadAnimationBase : function () {
		kajonaAjaxHelper.loadAjaxBase();
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/animation/animation-min.js');
	},

	loadAutocompleteBase : function () {
		kajonaAjaxHelper.loadAjaxBase();
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/datasource/datasource-min.js');
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/autocomplete/autocomplete-min.js');
	},
	
	loadCalendarBase : function() {
	    kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/calendar/calendar-min.js');
	},
	

	loadImagecropperBase : function() {
		//kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/yahoo/yahoo-min.js');
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/element/element-beta-min.js');
		kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/animation/animation-min.js');
	    kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/resize/resize-min.js');
	    kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/imagecropper/imagecropper-beta-min.js');
	},
	
	loadDialogBase : function() {
	    kajonaAjaxHelper.addFileToLoad('admin/scripts/yui/container/container-min.js');
	},

	addFileToLoad : function(fileName) {
		if(kajonaAjaxHelper.bitPastOnload) {
			if(!inArray(fileName, kajonaAjaxHelper.arrayFilesLoaded)) {
				kajonaAjaxHelper.addJavascriptFile(fileName);
			}
		}
		else {
			if(!inArray(fileName, kajonaAjaxHelper.arrayFilesToLoad)) {
				kajonaAjaxHelper.arrayFilesToLoad[kajonaAjaxHelper.arrayFilesToLoad.length] = fileName;
			}
		}
	}
};

YAHOO.util.Event.onDOMReady(kajonaAjaxHelper.onLoadHandlerFinal);

var regularCallback = {
	success: function(o) { kajonaStatusDisplay.displayXMLMessage(o.responseText) },
	failure: function(o) { kajonaStatusDisplay.messageError("<b>request failed!!!</b>") }
};

var systemStatusCallback = function (o, bitSuccess) {
	if (bitSuccess) {
		kajonaStatusDisplay.displayXMLMessage(o.responseText);
		
		var strSystemid = o.argument[0];

		if(o.responseText.indexOf('<error>') == -1 && o.responseText.indexOf('<html>') == -1) {
			var image = document.getElementById('statusImage_'+strSystemid);
			var link = document.getElementById('statusLink_'+strSystemid);
		
			if(image.src.indexOf('icon_enabled.gif') != -1) {
				image.src=strInActiveImageSrc;
				image.setAttribute('alt', strInActiveText);
				link.setAttribute('title', strInActiveText);
			}
			else {
				image.src=strActiveImageSrc;
				image.setAttribute('alt', strActiveText);
				link.setAttribute('title', strActiveText);
			}
			Prepare(link);
		}     
	} else {
		kajonaStatusDisplay.messageError(o.responseText);
	}
};

var kajonaAdminAjax = {
	posConn : null,
	pagesConn : null,
	dashboardConn : null,
	statusConn : null,
	cropConn : null,
	rotateConn : null,

	setAbsolutePosition : function (systemIdToMove, intNewPos, strIdOfList) {
		//load ajax libs
		kajonaAjaxHelper.loadAjaxBase();
		kajonaAjaxHelper.addFileToLoad('admin/scripts/messagebox.js');

		var postTarget = 'xml.php?admin=1&module=system&action=setAbsolutePosition';
		var postBody = 'systemid='+systemIdToMove+'&listPos='+intNewPos;

		if(kajonaAdminAjax.posConn == null || !YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.posConn)) {
			kajonaAdminAjax.posConn = YAHOO.util.Connect.asyncRequest('POST', postTarget, regularCallback, postBody);
		}
	},
	
	setDashboardPos : function (systemIdToMove, intNewPos, strIdOfList) {
		//load ajax libs
		kajonaAjaxHelper.loadAjaxBase();
		kajonaAjaxHelper.addFileToLoad('admin/scripts/messagebox.js');

		var postTarget = 'xml.php?admin=1&module=dashboard&action=setDashboardPosition';
		var postBody = 'systemid='+systemIdToMove+'&listPos='+intNewPos+'&listId='+strIdOfList;

		if(kajonaAdminAjax.dashboardConn == null || !YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.dashboardConn)) {
			kajonaAdminAjax.dashboardConn = YAHOO.util.Connect.asyncRequest('POST', postTarget, regularCallback, postBody);
		}
	},
	
	setSystemStatus : function (systemIdToSet, objCallback) {
	    //load ajax libs
        kajonaAjaxHelper.loadAjaxBase();
        kajonaAjaxHelper.addFileToLoad('admin/scripts/messagebox.js');

        var postTarget = 'xml.php?admin=1&module=system&action=setStatus';
        var postBody = 'systemid='+systemIdToSet;

        if(kajonaAdminAjax.statusConn == null || !YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.statusConn)) {
            kajonaAdminAjax.statusConn = YAHOO.util.Connect.asyncRequest('POST', postTarget, objCallback, postBody);
        }
	},
	
	saveImageCropping : function (intX, intY, intWidth, intHeight, strRepoId, strFolder, strFile, objCallback) {
		//load ajax libs
        kajonaAjaxHelper.loadAjaxBase();
        kajonaAjaxHelper.addFileToLoad('admin/scripts/messagebox.js');

        var postTarget = 'xml.php?admin=1&module=filemanager&action=saveCropping';
        var postBody = 'systemid='+strRepoId+'&folder='+strFolder+'&file='+strFile+'&intX='+intX+'&intY='+intY+'&intWidth='+intWidth+'&intHeight='+intHeight+'';

        if(kajonaAdminAjax.cropConn == null || !YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.cropConn)) {
            kajonaAdminAjax.cropConn = YAHOO.util.Connect.asyncRequest('POST', postTarget, objCallback, postBody);
        }
	},
	
	saveImageRotating : function (intAngle, strRepoId, strFolder, strFile, objCallback) {
		//load ajax libs
        kajonaAjaxHelper.loadAjaxBase();
        kajonaAjaxHelper.addFileToLoad('admin/scripts/messagebox.js');

        var postTarget = 'xml.php?admin=1&module=filemanager&action=rotate';
        var postBody = 'systemid='+strRepoId+'&folder='+strFolder+'&file='+strFile+'&angle='+intAngle+'';

        if(kajonaAdminAjax.rotateConn == null || !YAHOO.util.Connect.isCallInProgress(kajonaAdminAjax.rotateConn)) {
            kajonaAdminAjax.rotateConn = YAHOO.util.Connect.asyncRequest('POST', postTarget, objCallback, postBody);
        }
	}

};

//--- FILEMANAGER ---------------------------------------------------------------------------------------
function filemanagerShowRealsize() {
	document.getElementById('fm_filemanagerPic').src = fm_image_rawurl+"?x="+(new Date()).getMilliseconds();
	fm_image_isScaled = false;
	
}

function filemanagerShowPreview() {
	document.getElementById('fm_filemanagerPic').src = fm_image_scaledurl+"&x="+(new Date()).getMilliseconds();
	fm_image_isScaled = true;
	if(fm_cropObj != null)
		fm_cropObj.destroy();
	
	document.getElementById("accept_icon").src = document.getElementById("accept_icon").src.replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
	fm_cropObj = null;
}

var fm_cropObj = null;
function filemanagerShowCropping() {
	/*if(fm_image_isScaled) {
		fm_preview_warning.init();
		return;
	}*/
	//init the cropping
	if(fm_cropObj == null) {
		fm_cropObj =  new YAHOO.widget.ImageCropper('fm_filemanagerPic', {status: true});
		document.getElementById("accept_icon").src = document.getElementById("accept_icon").src.replace("icon_crop_acceptDisabled.gif", "icon_crop_accept.gif");
	}
	else {
		fm_cropObj.destroy();
		fm_cropObj = null;
		document.getElementById("accept_icon").src = document.getElementById("accept_icon").src.replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
	}
	
}

function filemanagerSaveCropping() {
	if(fm_cropObj != null)
		fm_crop_save_warning.init();
}

var cropArea = null;
function filemanagerSaveCroppingToBackend() {
	fm_crop_save_warning.hide();
	fm_crop_screenlock.init();
	cropArea = fm_cropObj.getCropCoords();
    if(fm_image_isScaled) {
        //recalculate the "real" crop-coordinates
        var intScaledWidth = document.getElementById('fm_filemanagerPic').width;
        var intScaledHeight = document.getElementById('fm_filemanagerPic').height;
        var intOriginalWidth = document.getElementById('fm_int_realwidth').value;
        var intOriginalHeigth = document.getElementById('fm_int_realheight').value;

        cropArea.left = cropArea.left * (intOriginalWidth/intScaledWidth);
        cropArea.top = cropArea.top * (intOriginalHeigth/intScaledHeight);
        cropArea.width = cropArea.width * (intOriginalWidth/intScaledWidth);
        cropArea.height = cropArea.height * (intOriginalHeigth/intScaledHeight);

    }
	kajonaAdminAjax.saveImageCropping(cropArea.left, cropArea.top, cropArea.width, cropArea.height, fm_repo_id, fm_folder, fm_file, fm_cropping_callback);
}

var fm_cropping_callback = {
	success: function(o) { 
		kajonaStatusDisplay.displayXMLMessage(o.responseText);
		fm_cropObj.destroy();
		fm_cropObj = null;
		document.getElementById("accept_icon").src = document.getElementById("accept_icon").src.replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
		document.getElementById('fm_image_dimensions').innerHTML = cropArea.width+' x '+cropArea.height;
		document.getElementById('fm_image_size').innerHTML = 'n.a.';
        document.getElementById('fm_int_realwidth').value = cropArea.width;
        document.getElementById('fm_int_realheight').value = cropArea.height;
		filemanagerShowRealsize();
		cropArea = null;
		
		fm_crop_screenlock.hide();
	},
	failure: function(o) { 
		kajonaStatusDisplay.messageError("<b>request failed!!!</b>"+o.responseText); 
		fm_crop_screenlock.hide(); 
	}
};

function filemanagerRotate(intAngle) {
	fm_crop_screenlock.init();
	kajonaAdminAjax.saveImageRotating(intAngle, fm_repo_id, fm_folder, fm_file, fm_rotate_callback);
}

var fm_rotate_callback = {
		success: function(o) { 
			kajonaStatusDisplay.displayXMLMessage(o.responseText);

            if(fm_cropObj != null) {
                fm_cropObj.destroy();
                fm_cropObj = null;
                document.getElementById("accept_icon").src = document.getElementById("accept_icon").src.replace("icon_crop_accept.gif", "icon_crop_acceptDisabled.gif");
            }

			//document.getElementById('fm_image_dimensions').innerHTML = 'n.a';
			filemanagerShowRealsize();
			//fm_crop_screenlock.hide();
            //update size-info & hidden elements
            var intWidthOld = document.getElementById('fm_int_realwidth').value;
            var intHeightOld = document.getElementById('fm_int_realheight').value;
            document.getElementById('fm_int_realwidth').value = intHeightOld;
            document.getElementById('fm_int_realheight').value = intWidthOld;
            document.getElementById('fm_image_dimensions').innerHTML = intHeightOld+' x '+intWidthOld;

            fm_crop_screenlock.hide();
		},
		failure: function(o) { 
			kajonaStatusDisplay.messageError("<b>request failed!!!</b>"+o.responseText); 
			fm_crop_screenlock.hide(); 
		}
	};
