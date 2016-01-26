<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                   *
********************************************************************************************************/


//Determing the area to load
if(class_carrier::getInstance()->getParam("admin") == 1) {
    define("_admin_", true);
}
else {
    define("_admin_", false);
}

define("_autotesting_", false);


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

    /**
     * @var \Kajona\System\System\ObjectBuilder
     */
    public $objBuilder;

    /**
     * Triggers the processing of the current request
     * @return void
     */
    public function processRequest() {

        $strModule = class_carrier::getInstance()->getParam("module");
        if($strModule == "") {
            $strModule = _admin_ ?  "dashboard" : "pages";
        }

        $strAction = class_carrier::getInstance()->getParam("action");
        $strLanguageParam = class_carrier::getInstance()->getParam("language");

        $this->objResponse = class_response_object::getInstance();
        $this->objResponse->setStrResponseType(class_http_responsetypes::STR_TYPE_HTML);
        $this->objResponse->setStrStatusCode(class_http_statuscodes::SC_OK);

        $this->objBuilder = class_carrier::getInstance()->getContainer()->offsetGet('object_builder');

        $objDispatcher = new class_request_dispatcher($this->objResponse, $this->objBuilder);
        $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);
    }

}


//creating the wrapper instance and passing control
$objIndex = new class_index();
$objIndex->processRequest();
$objIndex->objResponse->sendHeaders();
$objIndex->objResponse->sendContent();
class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(class_request_entrypoint_enum::INDEX()));

