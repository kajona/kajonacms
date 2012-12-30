<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                   *
********************************************************************************************************/


//Determing the area to load
if(issetGet("admin") && getGet("admin") == 1) {
    define("_admin_", true);
}
else {
    define("_admin_", false);
}

define("_autotesting_", false);

// --- The Index Class ----------------------------------------------------------------------------------

/**
 * Wrapper class to centralize a method within its namespace
 *
 * @package module_system
 */
class class_index {

    /**
     * @var class_response_object
     */
    public $objResponse;

    public function __construct() {
        class_carrier::getInstance();
    }

    public function processRequest() {

        $strModule = getGet("module");
        if($strModule == "") {
            $strModule = getPost("module");
        }

        if($strModule == "" && _admin_) {
            $strModule = "dashboard";
        }

        if($strModule == "" && !_admin_) {
            $strModule = "pages";
        }

        $strAction = getGet("action");
        if($strAction == "") {
            $strAction = getPost("action");
        }

        $strLanguageParam = getGet("language");
        if($strLanguageParam == "") {
            $strLanguageParam = getPost("language");
        }


        $this->objResponse = class_response_object::getInstance();
        $this->objResponse->setStResponseType(class_http_responsetypes::STR_TYPE_HTML);
        $this->objResponse->setStrStatusCode(class_http_statuscodes::SC_OK);

        $objDispatcher = new class_request_dispatcher($this->objResponse);
        $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);
    }

}


//creating the wrapper instance and passing control
$objIndex = new class_index();
$objIndex->processRequest();
$objIndex->objResponse->sendHeaders();
echo $objIndex->objResponse->getStrContent();

