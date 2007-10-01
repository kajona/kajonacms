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



/* P O R T A L E D I T O R   C O N T A I N E R */
.peContainerOut {
	display: inline;
	border: none;
}

.peContainerHover {
	display: block;
	border: 1px solid #cccccc;
	background-color: #EFEFEF;
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
	background-color: #FFFFFF;
	border: 1px solid black;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}

#pe_classicskin img {
	border: none;
}

";
?>