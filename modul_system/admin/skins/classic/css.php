<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	css.php																					            *
* 	CSS-Angaben fuer den Admin-Bereich																	*
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

img {
	border: none;
}

.text {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: none;
}


.inputText {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: normal;
	color: #000000;
	border: 1px solid #000099;
	width: 180px;
}

.inputTextarea {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: normal;
	color: #000000;
	border: 1px solid #000099;
	width: 180px;
	height: 50px;
}

.inputTextShort {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	color: #000000;
	border: 1px solid #000099;
	width: 80px;
}

.inputDropdown {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	color: #000000;
	border: 1px solid #000099;
	width: 180px;
}

.dragList {
	border-spacing: 0px;

}

.adminListRow1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: 1px solid black;
	background-color: #CFD7FF;
	height: 15px;
}

.adminListRow1Over {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: 1px solid black;
	background-color: #cfcfcf;
	height: 15px;
}

.adminListRow2 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: none;
	background-color: #FFFFFF;
	height: 15px;
}

.adminListRow2Over {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: none;
	background-color: #cfcfcf;
	height: 15px;
}

.listActions {
    text-align: right;
}

.warnbox {
	width: 400px;
	align: center;
	cellpadding: 1px;
	cellspacing: 0px;
	border: 1px solid #FF0000;
}

.warnbox td {
	text-align: center;
	border: none;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: bold;
	font-variant: normal;
}

.divider {
	height: 1px;
	border-top: 1px solid #000099;
}

.statusPages {
	width: 100%;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	padding: 2px;
    background-color: #CFD7FF;
    border: 1px solid #5F7BFF;
}

.statusPages td tr {
	background-color: #dad7ff;
	width: 100%;
	border: none;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
}

.folderviewDetail {
	width: 80%;
	background-color: #eae8ff;
	border: none;
}

.wysiwyg {
	width: 600px;
	height: 400px;
	border: 1px solid #000099;
}

.inputDate {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	color: #000000;
	border: 1px solid #000099;

}

.fieldset {
    border: 1px solid #dad7ff;
}

/* Links */

a:link, a:visited, a:active {
    color: #340C5F;
    text-decoration: underline;
}

a:hover {
    text-decoration: underline;
    color: #000000;
}

.adminnavi { }
a.adminnavi {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	border: 1px solid transparent ;
	border: none;
	color: #000000;
	background-color: none;
	padding-right: 5px;
	padding-left: 5px;
	text-decoration: none;
}
a.adminnavi:link,
a.adminnavi:active,
a.adminnavi:visited {
	text-decoration: none;
}

a.adminnavi:hover {
	padding-right: 4px;
	padding-left: 4px;
	border: 1px solid #000099;
	background-color: #CFD0FF;
}



.adminModuleNaviSelected {
	 background-color: #DAD7FF;
}

a.adminModuleNaviSelected {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
	background-color: #DAD7FF;
	text-decoration: none;

}
a.adminModuleNaviSelected:link,
a.adminModuleNaviSelected:active,
a.adminModuleNaviSelected:visited {
	text-decoration: none;
	color: #000000;
}
a.adminModuleNaviSelected:hover {
    background-color: #DAD7FF;
    text-decoration: none;
}
a.adminModuleNaviSelected:visited { }

.adminModuleNavi { }
a.adminModuleNavi {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
	background-color: #FFFFFF;
	text-decoration: none;

}
a.adminModuleNavi:link,
a.adminModuleNavi:active,
a.adminModuleNavi:visited {
	text-decoration: none;
	color: #000000;
}
a.adminModuleNavi:hover {
    background-color: #DAD7FF;
    text-decoration: none;
}

.moduleNavi {
    border: 1px solid #ffffff;
    padding: 1px;
    padding-left: 10px;
}

.moduleNaviSelected {
    padding: 1px;
    padding-left: 10px;
    background-color: #CFD7FF;
    border: 1px solid #5F7BFF;
}

/* -----------------------------------------------------------------------------------*/


/* Seitenglobales*/

body { margin: 0px; padding: 0px; background: #FFFFFF; }

.tabelle_zeile_hgr {
	background-color: #DAD7FF;
}


.modulhead {
	padding: 1px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	font-style: normal;
	line-height: normal;
	font-weight: normal;
	font-variant: normal;
	text-align: left;
	color: #FFFFFF;
	background-position: left center;
	background-image: url("._skinwebpath_."/tabellenkopf.gif);
	background-repeat: no-repeat;
}

.modulheadkurz {
	padding: 1px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	font-style: normal;
	line-height: normal;
	font-weight: normal;
	font-variant: normal;
	text-align: left;
	color: #FFFFFF;
	background-position: left center;
	background-image: url("._skinwebpath_."/tabellenkopfkurz.gif);
}

.modullinie {
	height: 1px;
	background-color:#000099;
}
.listenframe {
	padding: 1px;
	border-top: 1px none #000099;
	border-right: 1px dashed #000099;
	border-bottom: 1px dashed #000099;
	border-left: 1px dashed #000099;
	background-color:#FFFFFF;
}

.listenzeile {
	padding: 1px;
	border-top: 1px none #000099;
	border-right: 1px dashed #000099;
	border-bottom: 1px none #000099;
	border-left: 1px dashed #000099;
	background-color:#FFFFFF;
}

.listenframeseite {
	padding: 1px;
	border-top: 1px none #000099;
	border-right: 1px dashed #000099;
	border-bottom: 1px dashed #000099;
	border-left: 1px dashed #000099;
	background-color:#FFFFFF;
}
.modulaktionen {
	padding: 2px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border-right-width: 1px;
	border-left-width: 1px;
	border-top-style: none;
	border-right-style: dashed;
	border-bottom-style: none;
	border-left-style: dashed;
	border-right-color: #000099;
	border-left-color: #000099;
	text-align: left;
	height: 17px;
	background-color:#ffffff;
	background-repeat: repeat-x;
	background-image: url("._skinwebpath_."/navi_back.gif);
}
.listecontent {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: none;

}
.listenframe {
	padding: 1px;
	border-top: 1px none #000099;
	border-right: 1px dashed #000099;
	border-bottom: 1px dashed #000099;
	border-left: 1px dashed #000099;
	background-color:#FFFFFF;
}


.listenframeseite {
	padding: 1px;
	border-top: 1px none #000099;
	border-right: 1px dashed #000099;
	border-bottom: 1px dashed #000099;
	border-left: 1px dashed #000099;
	background-color:#FFFFFF;
}

.inputSubmit {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	margin: 1px;
	border: 1px solid #000099;
}

.status_filemanager {
	width: 100%;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	background-color: #CFD7FF;
    border: 1px solid #5F7BFF;
}
.text1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	color: #000000;
	border: none;
}

.preText {
    border: 1px solid #000099;
    margin: 10px;
    background-color: #E0E0EF;
    padding: 2px;
}

.languageswitch {
	margin-bottom: 5px;
	background-color: #CFD7FF;
    border: 1px solid #5F7BFF;
}

.languageButton, .languageButton:link, .languageButton:visited, .languageButton:link {
	text-decoration: none;
	color: #000000;
	border-left: 1px solid #dad7ff;
	border-right: 1px solid #dad7ff;
}

.languageButton:hover {
	color: #000000;
	border-left: 1px solid #5F7BFF;
	border-right: 1px solid #5F7BFF;
	background-color: #ffffff;
}

.languageButtonActive, .languageButtonActive:link, .languageButtonActive:visited, .languageButtonActive:link {
	color: #000000;
	border-left: 1px solid #5F7BFF;
	border-right: 1px solid #5F7BFF;
	background-color: #ffffff;
	text-decoration: none;
}

/* T O O L T I P */
.tooltip {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    color: #000000;
    font-size: 10px;
    text-align: left;
    background-color: #ffffff;
    border: 1px solid #000099;
    padding: 4px;
    max-width: 200px;
    z-index: 2000;
}



.jsStatusBoxMessage {
	background-color: #ffffff;
	margin: 2px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	text-align: left;
	color: #FFFFFF;
	border: 1px solid #000099;
}

.jsStatusBoxError {
	background-color: #FFFFFF;
	margin: 2px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	text-align: left;
	color: #FFFFFF;
	border: 1px solid #F80800;
}

.jsHeader {
	background-position: left top;
	background-image: url("._skinwebpath_."/tabellenkopfkurz.gif);
	font-family: Arial, Verdana, Helvetica, sans-serif;
    color: #FFFFFF;
    font-size: 11px;
	padding-left: 2px;
}

#jsStatusBoxContent {
	padding: 2px;
	font-family: Arial, Verdana, Helvetica, sans-serif;
    color: #000000;
    font-size: 11px;
}

/* A U T O C O M P L E T E */
.ac_container {
    position:relative;
}

/* styles for results container */
.ac_container .ac_results {
    position:absolute;
    top:1.6em;
    width:180px;
}

/* styles for header/body/footer wrapper within container */
.ac_container .yui-ac-content {
    position:absolute;
    width:100%;
    border:1px solid #000099;
    background:#ffffff;
    overflow:hidden;
    z-index:9050;
}

/* styles for results list */
.ac_container .yui-ac-content ul{
    margin:0;
    padding:0;
    width:100%;
}

/* styles for result item */
.ac_container .yui-ac-content li {
    margin:0;
    padding:2px 5px;
    cursor:default;
    white-space:nowrap;
}


/* styles for highlighted result item */
.ac_container .yui-ac-content li.yui-ac-highlight {
	background-color: #CFD0FF;
}

.dashBoard {
	border: none;
	width: 100%;
	font-family: Arial, Verdana, Helvetica, sans-serif;
    color: #000000;
    font-size: 11px;
}

.dashBoard td {
	vertical-align: top;
}

.adminwidget {
	border: 1px solid #000099;
}

.adminwidgetHeader {

}

.adminwidgetHeaderTitle {
	padding: 2px 0 2px 2px;
	background-position: left top;
	background-image: url("._skinwebpath_."/tabellenkopfkurz.gif);
	color: #FFFFFF;
    font-size: 11px;
    width: 175px;
    float: left;
}

.adminwidgetHeaderActions {
	text-align: right;
	float: right;
	margin: 1px 1px 0 0;
}

.adminwidgetContent {
	padding: 2px;
}

.dashBoard ul {
	padding: 0 0 10px 0; 
	margin: none; 
	list-style: none; 
	width: 230px;
}

.dashBoard ul li {
	padding: 0; 
	margin: 0 0 10px 0; 
}



";
?>