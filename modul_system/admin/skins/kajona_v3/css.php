<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
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


/* C A L E N D A R  O V E R L A Y */
#calendarOverlay {
	position: absolute;
	width: 200px;
	z-index: 1000;
}

body {
	margin: 0 5px 20px 0;
	padding: 0px;
	background: #FFFFFF;
	background-image: url("._skinwebpath_."/back.gif);
	background-repeat: repeat-x;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
	text-align: center;
}

body.folderView, body.portalEditor {
	margin: 15px;
	text-align: left;
}

a, a:link, a:visited {
     text-decoration: underline;
     color: #737373;
}

a:hover, a:active, a:focus {
     text-decoration: underline;
     color: #000000;
}

img {
	border: none;
}

table {
	border: none;
}

td {
	text-align: left;
	vertical-align: top;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
}


/* L O G I N P A G E */
#loginBox {
	margin: 0 auto 0 auto;
	padding-top: 50px;
	width: 373px;
	text-align: left;
}

#loginBox .logo {
	background: url('"._skinwebpath_."/login_logo.gif') top left no-repeat;
	width: 178px;
	height: 51px;
}

#loginBox .topRight {
	background: url('"._skinwebpath_."/login_box_topright.gif') top right no-repeat;
	margin: 0 0 0 48px;
	padding: 0;
	width: 325px;
}

#loginBox .topLeft {
	background: url('"._skinwebpath_."/login_box_topleft.gif') top left no-repeat;
	margin: 0;
	padding: 0;
}

#loginBox .bottomRight {
	background: url('"._skinwebpath_."/login_box_bottomright.gif') bottom right no-repeat;
	margin: 0;
	padding: 0;
}

#loginBox .bottomLeft {
	background: url('"._skinwebpath_."/login_box_bottomleft.gif') bottom left no-repeat;
	margin: 0;
	padding: 0;
}

#loginBox .content {
	margin: 0;
	padding: 55px 20px 20px 20px;
}

#loginBox .content label {
	display: block;
	float: left;
	width: 60px;
	height: 19px;
	padding: 3px 0 0 0;
}

* html #loginBox .content label {
	height: 22px;
}

#loginBox .content p {
	margin: 0;
	padding: 0;
}

#loginBox .content p.error {
	color: #ff0000;
	margin-top: 10px;
}

#loginBox .copyright {
	text-align: right;
	color: #c2c2c2;
	margin-right: 10px;
}

#loginBox .copyright a, #loginBox .copyright a:link, #loginBox .copyright a:visited {
	 text-align: right;
     text-decoration: none;
     color: #c2c2c2;
}

#loginBox .copyright a:hover, #loginBox .copyright a:active, #loginBox .copyright a:focus {
     text-decoration: none;
     color: #737373;
}


/* L O G O */

#logo {
	background-image: url('"._skinwebpath_."/logo.gif');
	background-position: bottom;
	background-repeat: no-repeat;
	width: 326px;
	height: 74px;
}

#logoSpacer {
	height: 50px;
}


/* S T A T U S B O X */

#statusBoxHeader {
	background-image: url('"._skinwebpath_."/statusbox_header.gif');
	width: 150px;
	height: 5px;
}

#statusBox {
	background-image: url('"._skinwebpath_."/statusbox_back.gif');
	width: 150px;
	height: 70px;
}

#statusBox .boxContent {
	margin-left: 10px;
}

#statusBox strong {
	margin-left: 10px;
}

#statusBox ul {
	margin: 5px 0 5px 0;
	padding: 0;
	list-style-type: none;
}

#statusBox ul li a {
	background-image: url('"._skinwebpath_."/link_arrow.gif');
	background-repeat: no-repeat;
	background-position: left;
	text-indent: 10px;
	display: block;
	text-decoration: none;
}

#naviContainer {
    background-color: #ffffff;
	background-image: url('"._skinwebpath_."/moduleactionnavi_back.gif');
	background-repeat: repeat-y;
	background-position: right;
    background-color: #ffffff;
    border: none;
}

/* M O D U L E N A V I */

#moduleNavi {
	background-image: url('"._skinwebpath_."/modulenavi_back.gif');
	background-repeat: repeat-x;
	background-position: bottom;
	height: 24px;
}

#moduleNavi ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

#moduleNavi ul li {
    float: left;
    display: block;
    height: 24px;
    padding-right: 15px;
    margin-left: -8px;
    position: relative;
    background: url('"._skinwebpath_."/modulenavi_tab_right.png') 100% 0 no-repeat;
    white-space: nowrap;
}

#moduleNavi ul li.first {
    margin-left: -2px;
}

#moduleNavi ul span {
    height: 24px;
    line-height: 23px;
    padding: 1px 0 0 7px;
    background: url('"._skinwebpath_."/modulenavi_tab_left.gif') no-repeat;
}

html>body #moduleNavi ul span {
    display: block;
}

#moduleNavi ul li a, #moduleNavi ul li a:link, #moduleNavi ul li a:visited {
     text-decoration: none;
}

#moduleNavi ul li a:hover, #moduleNavi ul li a:active, #moduleNavi ul li a:focus {
     text-decoration: none;
}

#moduleNavi ul li#selected {
    z-index: 1;
    background-image: url('"._skinwebpath_."/modulenavi_tab_right_selected.png');
}

#moduleNavi ul li#selected span {
    background-image: url('"._skinwebpath_."/modulenavi_tab_left_selected.gif');
}

#moduleNavi ul li#selected a {
    color: #000000;
}


/* M O D U L E A C T I O N N A V I */

#moduleActionNaviThree {
	background-image: url('"._skinwebpath_."/navi_three_back.gif');
	background-repeat: repeat-y;
	background-position: right;
	width: 176px;
}

#moduleActionNaviThree div {
	background-image: url('"._skinwebpath_."/navi_three.gif');
	background-repeat: no-repeat;
	width: 176px;
	height: 264px;
}

#moduleActionNavi {
	background-color: #ffffff;
	background-image: url('"._skinwebpath_."/moduleactionnavi_back.gif');
	background-repeat: repeat-y;
	background-position: right;
}

#moduleActionNavi ul {
	margin: 10px 0 10px 0;
	padding: 0px;
	list-style-type: none;
}

#moduleActionNavi ul li {
}

#moduleActionNavi ul li.spacer {
	height: 10px;
}

#moduleActionNavi ul li a {
	background-image: url('"._skinwebpath_."/moduleactionnavi_disabled_back.gif');
	display: block;
	width: 150px;
	height: 17px;
	text-indent: 10px;
}

#moduleActionNavi ul li a, #moduleActionNavi ul li a:link, #moduleActionNavi ul li a:visited {
     text-decoration: none;
}

#moduleActionNavi ul li a:hover, #moduleActionNavi ul li a:active, #moduleActionNavi ul li a:focus {
     text-decoration: none;
}


/* F O O T E R */
#footerLeftCorner {
	background-image: url('"._skinwebpath_."/footer_left.gif');
	background-repeat: no-repeat;
	background-position: right;
	height: 18px;
}

#footerLeft {
	background-image: url('"._skinwebpath_."/footer_left_back.gif');
	background-repeat: no-repeat;
	width: 150px;
	height: 18px;
	text-align: center;
}

#footerRight {
	background-color: #ffffff;
	background-image: url('"._skinwebpath_."/footer_middle_back.gif');
	background-repeat: repeat-x;
	background-position: bottom;
	height: 18px;
}

#footerRight div {
	width: 600px;
	height: 1px;
}

#footerRightCorner {
	background-image: url('"._skinwebpath_."/footer_right.gif');
	background-repeat: no-repeat;
	background-position: left;
	width: 8px;
	height: 18px;
}

/* C O P Y R I G H T */

#copyright {
	text-align: right;
	color: #c2c2c2;
	padding-right: 10px;
}

#copyright a, #copyright a:link, #copyright a:visited {
     text-align: right;
     text-decoration: none;
     color: #c2c2c2;
}

#copyright a:hover, #copyright a:active, #copyright a:focus {
     text-decoration: none;
     color: #737373;
}


/* C O N T E N T */
#contentTopRightTop {
	background-image: url('"._skinwebpath_."/content_top_right_top.gif');
	background-repeat: no-repeat;
	background-position: bottom;
	width: 8px;
	height: 24px;
}

#contentTopRight {
	background-color: #ffffff;
	background-image: url('"._skinwebpath_."/content_right.gif');
	background-repeat: repeat-y;
	background-position: right;
	width: 8px;
}

#contentTopRight div {
	background-image: url('"._skinwebpath_."/content_top_right.gif');
	background-repeat: no-repeat;
	width: 8px;
	height: 5px;
}

#content {
	background-color: #ffffff;
	background-repeat: no-repeat;
	background-position: top right;
	text-align: right;
}

#content #contentBox {
	margin-left: 30px;
	margin-right: 20px;
	text-align: left;
}


/* H E A D L I N E S */
h1 {
	text-align: right;
	color: #c2c2c2;
	font-size: 22px;
	font-weight: normal;
	margin-top: 13px;
	padding-right: 75px;
}

h2 {
	color: #737373;
	font-size: 11px;
	font-weight: bold;
	margin-top: 13px;
}

/* Q U I C K H E L P, D A S H B O A R D */
.quickHelpButton {
	text-align: right;
	margin: 50px 0 0 0;
}

.quickHelpButton a {
	background-color: #c4f3c4;
	background-image: url('"._skinwebpath_."/quickhelp_button.gif');
	background-repeat: no-repeat;
	background-position: left;
	text-align: left;
	text-indent: 20px;
	text-transform: uppercase;
	line-height: 19px;
	display: block;
	width: 120px;
	height: 19px;
	cursor: help;
	margin-left: auto;
}

.quickHelpButton a, .quickHelpButton a:link, .quickHelpButton a:visited {
     text-decoration: none;
}

.quickHelpButton a:hover, .quickHelpButton a:active, .quickHelpButton a:focus {
     text-decoration: none;
}

#quickHelp, .adminwidget {
	display: none;
	text-align: left;
	width: 400px;
	margin: 0px auto;
	-moz-user-select: none;
	-khtml-user-select: none;
	user-select: none;
}

.adminwidget {
    display: block;
    width: 250px;
}

#quickHelp .hd .title, .adminwidget .hd .title {
	float: left;
	text-indent: 25px;
	text-transform: uppercase;
	padding-top: 5px;
}

.adminwidget .hd .title {
    text-indent: 5px;
}

#quickHelp .hd .c, .adminwidget .hd .c {
	text-align: right;
	line-height: 22px;
	height: 22px;
}

.adminwidget .hd .c {
    text-align: right;
    line-height: 22px;
    height: 22px;
    padding-right: 5px;
}

#quickHelp .hd .c a, .adminwidget .hd .c a {
	margin-right: 7px;
}

.adminwidget .hd .c a {
    margin-right: 1px;
}

#quickHelp .hd .clear, .adminwidget .hd .clear {
	clear: both;
}

#quickHelp .ft .c, .adminwidget .ft .c {
	font-size: 1px; /* ensure minimum height */
	height: 22px;
}

#quickHelp .ft .c, .adminwidget .ft .c {
	height: 14px;
}

#quickHelp .hd, .adminwidget .hd {
	background: transparent url('"._skinwebpath_."/quickhelp_topleft.png') no-repeat 0px 0px;
	margin-right: 7px; /* space for right corner */
	cursor: move;
}

.adminwidget .hd {
    background: transparent url('"._skinwebpath_."/adminwidget_topleft.png') no-repeat 0px 0px;
}


#quickHelp .hd .c, .adminwidget .hd .c {
	background: transparent url('"._skinwebpath_."/quickhelp_topright.png') no-repeat right 0px;
	margin-right: -7px; /* pull right corner back over empty space (from above margin) */
}

#quickHelp .bd, .adminwidget .bd {
	background: transparent url('"._skinwebpath_."/quickhelp_middleleft.png') repeat-y 0px 0px;
	margin-right: 6px;
}

#quickHelp .bd .c, .adminwidget .bd .c {
	background: transparent url('"._skinwebpath_."/quickhelp_middleright.png') repeat-y right 0px;
	margin-right: -6px;
}

#quickHelp .bd .c .spacer, .adminwidget .bd .c .spacer {
	height: 5px;
}

#quickHelp .bd .c .s, .adminwidget .bd .c .s {
	margin: 0px 8px 0px 4px;
	background: #000 url(ms.jpg) repeat-x 0px 0px;
	padding: 1em;
}

#quickHelp .ft, .adminwidget .ft {
	background: transparent url('"._skinwebpath_."/quickhelp_bottomleft.png') no-repeat 0px 0px;
	margin-right: 7px;
}

#quickHelp .ft .c, .adminwidget .ft .c {
	background: transparent url('"._skinwebpath_."/quickhelp_bottomright.png') no-repeat right 0px;
	margin-right: -7px;
}

#quickHelp p, .adminwidget p, .adminwidget .bd .c div {
	margin: 0;
	padding: 0 10px 0px 10px;
}


/* L I S T S */
.adminList {
	width: 80%;
}

body.folderView .adminList, body.portalEditor .adminList {
	width: 100%;
}

.adminListRow1 {
	height: 22px;
	background-color: #f0f0f0;
}

.adminListRow1:hover, .adminListRow1:hover td {
	background-color: #c4f3c4;
	color: #000000;
}

.adminListRow2 {
	height: 22px;
	background-color: #e3e3e3;
}

.adminListRow2:hover, .adminListRow2:hover td {
	background-color: #c4f3c4;
	color: #000000;
}

.adminListRow1 td, .adminListRow2 td{
	vertical-align: middle;
}

.adminListRow1 .image, .adminListRow2 .image {
	padding-left: 2px;
	padding-right: 3px;
}

.adminListRow1 .title, .adminListRow2 .title {
	width: 100%;
    padding-left: 2px;
	padding-right: 5px;
    line-height: 22px;
    vertical-align: top;
}

.adminListRow1 .center, .adminListRow2 .center {
	white-space: nowrap;
	padding-right: 5px;
}

.adminListRow1 .centerWrap, .adminListRow2 .centerWrap {
    padding-right: 5px;
}

.adminListRow1 .actions, .adminListRow2 .actions {
	white-space: nowrap;
	vertical-align: middle;
    text-align: right;
}

.adminListRow1 .actions img, .adminListRow2 .actions img {
	margin-right: 2px;
}


.adminListRow1 .dataTitle, .adminListRow2 .dataTitle {
	padding-left: 2px;
    padding-right: 5px;
    line-height: 22px;
    vertical-align: top;
}

.adminListRow1 .dataValue, .adminListRow2 .dataValue {
    padding-left: 2px;
    padding-right: 5px;
    line-height: 22px;
    vertical-align: top;
}

.adminListRow1 div, .adminListRow2 div {
    line-height: 11px;
}



/* F O R M S */
#content form label, body.portalEditor form label {
	display: block;
	float: left;
	width: 30%;
	height: 19px;
	padding: 3px 0 0 0;
}

* html #content form label, * html body.portalEditor form label {
	height: 22px;
}

form br {
	clear: both;
}

.inputText, .inputTextShort, .inputDate {
	background-image: url('"._skinwebpath_."/forms_input_back.gif');
	background-repeat: repeat-x;
	padding: 2px 0 0 4px;
	margin: 0 3px 0 0;
	display: block;
	float: left;
	width: 180px;
	height: 16px;
	border: 1px solid #c2c2c2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
}

* html .inputText, * html .inputTextShort, * html .inputDate {
	height: 20px;
}

.inputTextShort {
	width: 120px;
}

.inputDate {
	width: 30px;
}

* html .inputDate {
	width: 35px;
}

.inputTextarea {
	background-image: url('"._skinwebpath_."/forms_input_back.gif');
	background-repeat: repeat-x;
	padding: 1px 0 0 4px;
	margin: 0 3px 4px 0;
	display: block;
	float: left;
	width: 180px;
	height: 50px;
	border: 1px solid #c2c2c2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
}

.inputWysiwyg {
	float: left;
}

.inputDropdown {
	background-image: url('"._skinwebpath_."/forms_input_back.gif');
	background-repeat: repeat-x;
	padding: 1px 0 0 0;
	margin: 0 3px 0 0;
	display: block;
	float: left;
	width: 186px;
	height: 18px;
	border: 1px solid #c2c2c2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
}

* html .inputDropdown {
	height: 20px;
	width: 180px;
}

.inputText:focus, .inputTextShort:focus, .inputTextarea:focus, .inputDropdown:focus, .inputDate:focus {
	color: #000000;
}

.inputSubmit, .inputSubmitShort {
	background-color: #ffffff;
	background-image: url('"._skinwebpath_."/forms_button_back.gif');
	background-repeat: repeat-x;
	background-position: left center;
	padding: 1px 0 3px 15px;
	margin: 8px 0 0 0;
	display: block;
	float: left;
	width: 186px;
	height: 20px;
	border: 1px solid #c2c2c2;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #737373;
	text-align: left;
	cursor: pointer;
}

* html .inputSubmit {
	width: 180px;
}

.inputSubmitShort {
	width: 126px;
}

* html .inputSubmitShort {
	width: 120px;
}

.inputSubmit:hover, .inputSubmitShort:hover, .inputSubmit:focus, .inputSubmitShort:focus {
	color: #000000;
}

form .formText {
    padding-top: 3px;
}

form .formText .spacer {
    float: left;
    width: 30%;
    height: 11px;
}

form .formText .text {
    float: left;
    width: 70%;
}


/* F I E L D S E T */
.fieldset {
	width: 80%;
    border: 1px solid #737373;
    padding: 0 5px 5px 5px;
}

.fieldset legend {
	font-weight: bold;
    color: #737373;
}

.fieldset .adminList {
    width: 100%;
}


/* W A R N B O X */

.warnbox {
	width: 400px;
	border: 1px solid #FF0000;
	margin: 5px auto 10px auto;
	padding: 8px;
}

.warnbox h3 {
	margin: 0;
	padding: 0;
	font-size: 11px;
	font-weight: bold;
}


/* P R E F O R M A T T E D */

.preText {
	border: 1px solid #737373;
	margin: 10px 0 10px 0;
	background-color: #EDEDED;
	padding: 8px;
	overflow: auto;
}

.preText pre {
	margin: 0;
}


/* D A T E   S E L E C T O R  C O N T A I N E R */

.dateSelector {
	margin: 0 0 15px 0;
}


/* G R A P H  C O N T A I N E R */

.graphBox {
	margin: 10px 0 10px 0;
	text-align: center;
}

.graphBox img {
	border: 1px solid #737373;
}

/* P E R C E N T  B E A M */
div.percentBeamText {
	width: 50px;
	height: 15px;
	float: left;
    white-space: nowrap;
}

div.percentBeam {
	height: 10px;
	padding-top: 5px;
	border-left: 1px solid #737373;
	border-right: 1px solid #737373;
	float: right;
}


/* F I L E M A N A G E R */
.statusFilemanager {
	width: 100%;
}

.statusFilemanager .actions {
	text-align: left;
}

.statusFilemanager .actions a {
	margin-right: 2px;
}


/* F O L D E R V I E W */
body.folderView .listActions {
	text-align: right;
	vertical-align: middle;
}

body.folderView .listActions a {
	margin-right: 2px;
}

/* L A N G U A G E S W I T C H */

.languageSwitch {
	margin: 0 0 10px 0;
	padding: 0 0 0 20px;
	height: 19px;
	background-image: url('"._skinwebpath_."/languageswitch_left.gif');
	background-repeat: no-repeat;
	background-position: left center;
}

.languageSwitch a, .languageSwitch a:link, .languageSwitch a:visited {
	background-color: #c4f3c4;
	padding: 0 4px 0 4px;
	width: 50px;
	height: 19px;
	line-height: 19px;
	display: block;
	float: left;
    text-decoration: none;
}

.languageSwitch a.languageButtonActive, .languageSwitch a.languageButtonActive:link, .languageSwitch a.languageButtonActive:visited, .languageSwitch a:hover {
	color: #ffffff;
	background-color: #4def4d;
    text-decoration: none;
}

/* J S   S T A T U S   B O X */

.jsStatusBoxMessage {
	background-color: #f0f0f0;
	margin: 2px;
	border: 1px solid #e3e3e3;
}

.jsStatusBoxError {
	background-color: #f0f0f0;
	margin: 2px;
	text-align: left;
	border: 1px solid #F80800;
}

.jsHeader {
	padding-left: 2px;
}

#jsStatusBoxContent {
	padding: 2px;
	font-family: Arial, Verdana, Helvetica, sans-serif;
    font-size: 11px;
}

/* A U T O C O M P L E T E */
.ac_container {
	position:relative;
}

/* styles for results container */
.ac_container .ac_results {
	position: absolute;
	top: 1.6em;
	width: 178px;
	left: 30%;
	margin-left: 2px;
}

/* styles for header/body/footer wrapper within container */
.ac_container .yui-ac-content {
	position: absolute;
	width: 100%;
	border: 1px solid #00e800;
	background: #ffffff;
	overflow: hidden;
	z-index: 9050;
}

/* styles for results list */
.ac_container .yui-ac-content ul {
	margin: 0;
	padding: 0;
	width: 100%;
}

/* styles for result item */
.ac_container .yui-ac-content li {
	margin: 0;
	padding: 2px 5px;
	cursor: default;
	white-space: nowrap;
}

.ac_container .yui-ac-content li:hover {
	background-color: #f0f0f0;

}


/* styles for highlighted result item */
.ac_container .yui-ac-content li.yui-ac-highlight {
	background-color: #f0f0f0;
	color: #000000;
	cursor: pointer;
}
";
?>