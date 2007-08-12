<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	css_portaleditor.php																	            *
* 	Portaleditor CSS-Styles																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                                    *
********************************************************************************************************/

//Konstanten einlesen
require_once("../../../system/functions.php");
$strTemp = dirname((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
define("_skinwebpath_", saveUrlEncode(str_replace("/css", "", $strTemp)));

//Header senden
header("Content-type: text/css");

echo "

/* T O O L T I P */
.tooltip {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    color: #000000;
    font-size: 10px;
    text-align: left;
    background-color: #ffffff;
    border: 1px solid #00e800;
    padding: 4px;
    max-width: 200px;
    z-index: 2000;
}


/* P O R T A L E D I T O R  E N A B L E   B U T T O N */
#peEnableButton {
	position: fixed;
	top: 0px;
	right: 0px;
}


/* P O R T A L E D I T O R   T O O L B A R */
#peToolbar {
	z-index: 1000;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	color: #737373;
	font-size: 10px;
	white-space: nowrap;
	position: fixed;
    top: 0px;
    left: 0px;
}

#peToolbar td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	color: #737373;
	font-size: 10px;
	white-space: nowrap;
}

#peToolbar .logo {
	background-image: url("._skinwebpath_."/pe_logo.png);
	background-repeat: no-repeat;
	width: 200px;
	height: 45px;
	float: left;
}

#peToolbar .info {
	background-image: url("._skinwebpath_."/pe_back.png);
	background-repeat: repeat-x;
	height: 45px;
	margin-left: 200px;
}

#peToolbarSpacer {
	clear: both;
	height: 36px;
}


/* P O R T A L E D I T O R   C O N T A I N E R */
.peContainerOut {
	display: inline;
	border: none;
}

.peContainerHover {
	display: block;
	border: 1px solid #00e800;
}

.peContainerOut .menuOut {
	display: none;
}

.peContainerHover .menuHover {
	position: absolute;
	border: none;
	display: block;
}

.peContainerHover .menuHover .actions {
	display: inline;
	background-color: #00e800;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	padding: 0 2px 2px 2px;
}

.peContainerHover .menuHover .actions a, .peContainerHover .menuHover .actions a:link, .peContainerHover .menuHover .actions a:visited {
    font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #ffffff;
    text-decoration: none;
	font-weight: normal;
}

.peContainerHover .menuHover .actions a:hover, .peContainerHover .menuHover .actions a:active, .peContainerHover .menuHover .actions a:focus {
    font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #ffffff;
	text-decoration: underline;
	font-weight: normal;
}

";
?>