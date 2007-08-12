//   (c) 2004-2006 by MulchProductions, www.mulchprod.de
//   (c) 2007 by Kajona, www.kajona.de
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

function objMover() {
	this.mousePressed=0;
	this.objPosX=0;
	this.objPosY=0;
	this.diffX=0;
	this.diffY=0;

	this.setMousePressed = function (obj){
	    objToMove=obj;
	    objDiffX=currentMouseXPos - objToMove.offsetLeft;
		objDiffY=currentMouseYPos - objToMove.offsetTop;
	}

	this.unsetMousePressed = function (){
        objToMove=null;
	}
}

//--- MISC ----------------------------------------------------------------------------------------------
function fold(id) {
	style = document.getElementById(id).style.display;
	if (style=='none') 	{
		document.getElementById(id).style.display='block';
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
}

function addCss(file){
	var l=document.createElement("link");
	l.setAttribute("type", "text/css");
	l.setAttribute("rel", "stylesheet");
	l.setAttribute("href", file);
	document.getElementsByTagName("head")[0].appendChild(l);
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
