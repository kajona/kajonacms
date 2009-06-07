<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: index.php 2655 2009-03-28 19:00:05Z sidler $                                                *
********************************************************************************************************/

// check if PHP version is less than the required version 5
	if((int)substr(phpversion(),0,1)<5)
		die("<b>Wrong PHP version</b><br/>Kajona requires at least PHP 5. You're running PHP ".phpversion().".<br/>Please enable PHP 5 or ask your webhoster for help in case you don't know what to do.");
	else
		header("Location: http://". $_SERVER["SERVER_NAME"] .str_replace("index.php", "installer.php", $_SERVER["PHP_SELF"]));
?>