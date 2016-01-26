<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                      *
********************************************************************************************************/


//Determine the area to load
if(issetGet("admin") && getGet("admin") == 1) {
    define("_admin_", true);
}
else {
    define("_admin_", false);
}

define("_autotesting_", false);


/**
 * Class handling all requests to be served with xml
 *
 * @package module_system
 */
class class_xml {

    private static $bitRenderXmlHeader = true;

    /**
     * @var class_response_object
     */
    public $objResponse;

    /**
     * @var \Kajona\System\System\ObjectBuilder
     */
    public $objBuilder;

    /**
     * Starts the processing of the requests, fetches params and passes control to the request dispatcher
     * @return void
     */
    public function processRequest() {

        $strModule = class_carrier::getInstance()->getParam("module");
        $strAction = class_carrier::getInstance()->getParam("action");
        $strLanguageParam = class_carrier::getInstance()->getParam("language");

        $this->objResponse = class_response_object::getInstance();
        $this->objResponse->setStrResponseType(class_http_responsetypes::STR_TYPE_XML);
        $this->objResponse->setStrStatusCode(class_http_statuscodes::SC_OK);

        $this->objBuilder = class_carrier::getInstance()->getContainer()->offsetGet('object_builder');

        $objDispatcher = new class_request_dispatcher($this->objResponse, $this->objBuilder);
        $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);

        if($this->objResponse->getStrContent() == "") {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);
            $this->objResponse->setStrContent("<error>An error occurred, malformed request</error>");
        }

        if($this->objResponse->getStrResponseType() == class_http_responsetypes::STR_TYPE_XML && self::$bitRenderXmlHeader) {
            $this->objResponse->setStrContent("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n" . $this->objResponse->getStrContent());
        }
    }

    /**
     * If set to true, the output will be sent without the mandatory xml-head-element
     *
     * @param bool $bitSuppressXmlHeader
     * @return void
     */
    public static function setBitSuppressXmlHeader($bitSuppressXmlHeader) {
        self::$bitRenderXmlHeader = !$bitSuppressXmlHeader;
    }



}

//pass control
$objXML = new class_xml();
$objXML->processRequest();
$objXML->objResponse->sendHeaders();
$objXML->objResponse->sendContent();
class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(class_request_entrypoint_enum::XML()));
