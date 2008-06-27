<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	css_portaleditor.php																	            *
* 	Portaleditor CSS-Styles																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

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
    border: 1px solid #000099;
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

#pe_classicskin {
    white-space: nowrap;
    position: fixed;
    top: 0px;
    left: 0px;
    border: none; 
    background-color: #EFEFEF; 
    z-index: 1000; 
    width: 100%;
}

/* P O R T A L E D I T O R   C O N T A I N E R */
.peContainerOut {
	display: inline !important;
	border: none !important;
}

.peContainerHover {
	display: block !important;
	border: 1px solid #cccccc !important;
	background-color: #EFEFEF !important;
}

.peContainerOut .menuOut {
	display: none !important;
}

.peContainerHover .menuHover {
	position: absolute !important;
	border: none !important;
	display: block !important;
}

.peContainerHover .menuHover .actions {
	display: inline !important;
	background-color: #FFFFFF !important;
	border: 1px solid black !important;
	font-family: Verdana, Arial, Helvetica, sans-serif !important;
	font-size: 11px !important;
}

a.pe_link {
    font-size: 11px !important;
    font-weight: normal !important;
    color: #000000 !important;
}

#pe_classicskin img {
	border: none !important;
}

";
?>