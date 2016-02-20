<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                      *
********************************************************************************************************/
namespace Kajona\System;

//Determine the area to load
use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\RequestDispatcher;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemEventidentifier;

if (issetGet("admin") && getGet("admin") == 1) {
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
class Xml
{

    private static $bitRenderXmlHeader = true;

    /**
     * @var ResponseObject
     */
    public $objResponse;

    /**
     * @var \Kajona\System\System\ObjectBuilder
     */
    public $objBuilder;

    /**
     * Starts the processing of the requests, fetches params and passes control to the request dispatcher
     *
     * @return void
     */
    public function processRequest()
    {

        $strModule = Carrier::getInstance()->getParam("module");
        $strAction = Carrier::getInstance()->getParam("action");
        $strLanguageParam = Carrier::getInstance()->getParam("language");

        $this->objResponse = ResponseObject::getInstance();
        $this->objResponse->setStrResponseType(HttpResponsetypes::STR_TYPE_XML);
        $this->objResponse->setStrStatusCode(HttpStatuscodes::SC_OK);

        $this->objBuilder = Carrier::getInstance()->getContainer()->offsetGet('object_builder');

        $objDispatcher = new RequestDispatcher($this->objResponse, $this->objBuilder);
        $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);

        if ($this->objResponse->getStrContent() == "") {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);
            $this->objResponse->setStrContent("<error>An error occurred, malformed request</error>");
        }

        if ($this->objResponse->getStrResponseType() == HttpResponsetypes::STR_TYPE_XML && self::$bitRenderXmlHeader) {
            $this->objResponse->setStrContent("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n".$this->objResponse->getStrContent());
        }
    }

    /**
     * If set to true, the output will be sent without the mandatory xml-head-element
     *
     * @param bool $bitSuppressXmlHeader
     *
     * @return void
     */
    public static function setBitSuppressXmlHeader($bitSuppressXmlHeader)
    {
        self::$bitRenderXmlHeader = !$bitSuppressXmlHeader;
    }


}

//pass control
$objXML = new Xml();
$objXML->processRequest();
$objXML->objResponse->sendHeaders();
$objXML->objResponse->sendContent();
CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(RequestEntrypointEnum::XML()));
