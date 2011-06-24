<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                      *
********************************************************************************************************/


//mark the request as a xml-based request
define("_xmlLoader_", true);	

if(!require_once("./system/includes.php"))
	die("Error including necessary files");


//Determin the area to load
if(issetGet("admin") && getGet("admin") == 1)
	define("_admin_", true);
else
	define("_admin_", false);
	
	

/**
 * Class handling all requests to be served with xml
 *
 * @package modul_system
 */
class class_xml {
    
    public function __construct() {
		class_carrier::getInstance();
    }
    
    public function processRequest() {

        $strModule = getGet("module");
        if($strModule == "")
            $strModule = getPost("module");

        $strAction = getGet("action");
        if($strAction == "")
            $strAction = getPost("action");
        
        $strLanguageParam = getGet("language");
        if($strLanguageParam == "")
            $strLanguageParam = getPost("language");
        

        $objDispatcher = new class_request_dispatcher();
        $strContent = $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);
        
        if($strContent == "")
            $strContent = "<error>An error occured, malformed request</error>";

        $strContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$strContent;
        return $strContent;
    }

}

//pass control
$objXML = new class_xml();
header("Content-Type: text/xml; charset=utf-8");
echo $objXML->processRequest();

?>