<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                   *
********************************************************************************************************/




//Determing the area to load
if(issetGet("admin") && getGet("admin") == 1)
	define("_admin_", true);
else
	define("_admin_", false);



// --- The Index Class ----------------------------------------------------------------------------------

/**
 * Wrapper class to centralize a method within its namespace
 *
 * @package module_system
 */
class class_index  {

	public function __construct() {
		class_carrier::getInstance();
	}

    public function processRequest() {

        $strModule = getGet("module");
        if($strModule == "")
            $strModule = getPost("module");

        if($strModule == "" && _admin_)
            $strModule = "dashboard";

        if($strModule == "" && !_admin_)
            $strModule = "pages";

        $strAction = getGet("action");
        if($strAction == "")
            $strAction = getPost("action");

        $strLanguageParam = getGet("language");
        if($strLanguageParam == "")
            $strLanguageParam = getPost("language");


        $objDispatcher = new class_request_dispatcher();
        return $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);
    }

}


//creating the wrapper instance and passing control
$objIndex = new class_index();
header('Content-Type: text/html; charset=utf-8');
echo $objIndex->processRequest();


?>