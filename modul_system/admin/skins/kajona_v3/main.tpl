<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="_skinwebpath_/styles.css" type="text/css" />
	<script type="text/javascript" src="_webpath_/admin/scripts/yui/yuiloader-dom-event/yuiloader-dom-event.js"></script>
	<script type="text/javascript" src="_webpath_/admin/scripts/kajona.js"></script>
	<title>Kajona³ admin [%%webpathTitle%%]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="generator" content="Kajona³, www.kajona.de" />
	<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
	%%head%%
    <script type="text/javascript">
    	function enableTooltipsWrapper() { enableTooltips("showTooltip"); }
    	YAHOO.util.Event.onDOMReady(enableTooltipsWrapper);
	</script>
</head>
<body>

<table cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td colspan="2" rowspan="2" id="logo"></td>
			<td colspan="2" id="logoSpacer"></td>
		</tr>
		<tr>
			<td id="moduleNavi">
				<div>
					%%mainnavi%%
					<a href="javascript:showMenu();" onmouseover="javascript:showMenu();" id="showMenuLink"><img id="modulNaviMoreIcon" src="_skinwebpath_/modulenavi_more.png" /></a>
				</div> 
				<div id="moduleNaviHidden">
					<ul id="naviCollectorUl"></ul>
				</div>
			</td>
			<td id="contentTopRightTop"></td>
		</tr>
		<tr>
			<td id="moduleActionNaviThree"><div></div></td>
			<td id="naviContainer">
			    <div id="statusBoxHeader"></div>
			    <div id="statusBox">%%login%%</div>
			    <div id="moduleActionNavi">%%modulenavi%%</div>
			</td>
			<td id="contentMain" style="background-image: url('_skinwebpath_/header/%%module_id%%.png');">
				<h1>%%moduletitle%%</h1>
				%%quickhelp%%
				<div id="contentBox">
					%%content%%
				</div>
			</td>
			<td id="contentTopRight"><div></div></td>
		</tr>
		<tr>
			<td id="footerLeftCorner"></td>
			<td id="footerLeft">
				_gentime_
			</td>
			<td id="footerRight"><div></div></td>
			<td id="footerRightCorner"></td>
		</tr>
		<tr>
			<td colspan="4" id="copyright">&copy; 2009 <a href="http://www.kajona.de" target="_blank" title="Kajona³ CMS - empowering your content">Kajona³</a></td>
		</tr>
	</tbody>
</table>
<div id="jsStatusBox" style="display: none; position: fixed;"><div class="jsStatusBoxHeader">Status-Info</div><div id="jsStatusBoxContent" class="jsStatusBoxContent"></div></div>
<script type="text/javascript">
	kajonaAjaxHelper.loadAjaxBase();
	kajonaAjaxHelper.loadAnimationBase(function() {YAHOO.util.Event.onContentReady("adminModuleNaviUl", naviSetup);});
	
	var moduleNaviHiddenTimeout = undefined;
	function naviSetup() {
		var list = YAHOO.util.Dom.get('adminModuleNaviUl');
		var arrayChildren = YAHOO.util.Dom.getChildren(list);
		
		var intEntriesVisible = 0;
		for(intI = 0; intI < arrayChildren.length; intI++) {
			if(YAHOO.util.Dom.hasClass(arrayChildren[intI], 'adminModuleNaviHidden')) {
				nodeToMove = arrayChildren[intI];
			
				YAHOO.util.Dom.setStyle(nodeToMove, "display", "block");
				var tmpNode = nodeToMove.cloneNode(true);
				tmpNode.onmouseout = function() {moduleNaviHiddenTimeout = window.setTimeout('hideMenu()', 1000);};
				tmpNode.onmouseover = function() {window.clearTimeout(moduleNaviHiddenTimeout);};
				
				document.getElementById('naviCollectorUl').appendChild(tmpNode);
				document.getElementById('adminModuleNaviUl').removeChild(nodeToMove);
				intEntriesVisible++;
			}
		}
		
		if(intEntriesVisible == 0)
		   document.getElementById('modulNaviMoreIcon').style.display='none';
	}
	
	function showMenu() {
		YAHOO.util.Dom.setStyle('moduleNaviHidden', "opacity", 0);
		YAHOO.util.Dom.setStyle('moduleNaviHidden', "display", "block");
		//get xy coords
		arrCoords = YAHOO.util.Dom.getXY(YAHOO.util.Dom.get('showMenuLink'));
		YAHOO.util.Dom.setXY('moduleNaviHidden', [ arrCoords[0], arrCoords[1] ], false);
		
		animObject = new YAHOO.util.Anim('moduleNaviHidden', { opacity: { to: 1 } }, 0.5, YAHOO.util.Easing.easeOut);
		animObject.animate();
	}
	
	function hideMenu() {
		animObject = new YAHOO.util.Anim('moduleNaviHidden', { opacity: { to: 0 } }, 1, YAHOO.util.Easing.easeOut);
		animObject.animate();
		animObject.onComplete.subscribe(function() {YAHOO.util.Dom.setStyle('moduleNaviHidden', "display", "");});
	}
</script>
</body>
</html>