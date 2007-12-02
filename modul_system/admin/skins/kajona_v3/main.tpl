<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Kajona³ admin [%%webpathTitle%%]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="generator" content="Kajona³, www.kajona.de" />
	<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="_skinwebpath_/css.php" type="text/css" />
	<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/kajona.js"></script>
	<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/tooltips.js"></script>
	<script language="Javascript" type="text/javascript">
    	function enableTooltipsWrapper() { enableTooltips("showTooltip"); }
    	addLoadEvent(enableTooltipsWrapper);
    </script>
	<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/jscalendar/calendar.js"></script>
	<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/jscalendar/lang/calendar-de.js"></script>
	<script language="Javascript" type="text/javascript" src="_webpath_/admin/scripts/jscalendar/calendar-setup.js"></script>
	%%head%%
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
					<a href="javascript:showMenu();" onmouseover="javascript:showMenu();" id="showMenuLink"><img src="_skinwebpath_/modulenavi_more.png" /></a>
				</div> 
				<div id="moduleNaviHidden" >
					<ul id="naviCollectorUl">
						<li style="text-align: right"><a href="javascript:hideMenu();">[X]</a></li>
					</ul>
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
			<td id="contentMain" style="background-image: url('_skinwebpath_/header/%%module_id%%.gif');">
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
			<td colspan="4" id="copyright">&copy; 2007 <a href="http://www.kajona.de" target="_blank" title="Kajona³ CMS - empowering your content">Kajona³</a></td>
		</tr>
	</tbody>
</table>
<div id="jsStatusBox" style="display: none; position: absolute;"><div class="jsHeader">Status-Info</div><div id="jsStatusBoxContent"></div></div>
<script type="text/javascript">

kajonaAjaxHelper.loadDragNDropBase();
kajonaAjaxHelper.loadAnimationBase();

function naviPreSetup() {

		if(typeof YAHOO == "undefined") {
             window.setTimeout('naviPreSetup()', 1000);
             return;
        }
        naviSetup();
}


function naviSetup() {
	
	var list = YAHOO.util.Dom.get('adminModuleNaviUl');
	//alert(list.id+" size "+YAHOO.util.Dom.getChildren(list).length);
		
	var arrayChildren = YAHOO.util.Dom.getChildren(list);
	
	for(intI = 0; intI < arrayChildren.length; intI++) {
	
		if(YAHOO.util.Dom.hasClass(arrayChildren[intI], 'adminModuleNaviHidden')) {
		
			nodeToMove = arrayChildren[intI];
		
			YAHOO.util.Dom.setStyle(nodeToMove, "display", "block");
			document.getElementById('naviCollectorUl').appendChild(nodeToMove.cloneNode(true));
			document.getElementById('adminModuleNaviUl').removeChild(nodeToMove);
		
		}
		
	}

}

function showMenu() {
	YAHOO.util.Dom.setStyle('moduleNaviHidden', "display", "block");
	//get xy coords
	arrCoords = YAHOO.util.Dom.getXY(YAHOO.util.Dom.get('showMenuLink'));
	YAHOO.util.Dom.setXY('moduleNaviHidden', [ arrCoords[0], -200 ], false);
	
	var attributes = { 
        points: { to: [arrCoords[0], arrCoords[1] ] } 
	};
	
	animObject = new YAHOO.util.Motion('moduleNaviHidden', attributes, 1);
	animObject.animate();
}

function hideMenu() {
	YAHOO.util.Dom.setStyle('moduleNaviHidden', "display", "");
}

addLoadEvent(naviPreSetup);

</script>
</body>
</html>