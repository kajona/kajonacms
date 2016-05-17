<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                   *
********************************************************************************************************/

namespace Kajona\System;

//Determing the area to load
use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\RequestDispatcher;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\ServiceProvider;
use Kajona\System\System\SystemEventidentifier;

if(Carrier::getInstance()->getParam("admin") == 1) {
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
class Index {

    /**
     * @var ResponseObject
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

        $strModule = Carrier::getInstance()->getParam("module");
        if($strModule == "") {
            $strModule = _admin_ ?  "dashboard" : "pages";
        }

        $strAction = Carrier::getInstance()->getParam("action");
        $strLanguageParam = Carrier::getInstance()->getParam("language");

        $this->objResponse = ResponseObject::getInstance();
        $this->objResponse->setStrResponseType(HttpResponsetypes::STR_TYPE_HTML);
        $this->objResponse->setStrStatusCode(HttpStatuscodes::SC_OK);
        $this->objResponse->setObjEntrypoint(RequestEntrypointEnum::INDEX());

        $this->objBuilder = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_OBJECT_BUILDER);

        $objDispatcher = new RequestDispatcher($this->objResponse, $this->objBuilder);
        $objDispatcher->processRequest(_admin_, $strModule, $strAction, $strLanguageParam);
    }

}


//creating the wrapper instance and passing control
$objIndex = new Index();
$objIndex->processRequest();
$objIndex->objResponse->sendHeaders();
$objIndex->objResponse->sendContent();
CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(RequestEntrypointEnum::INDEX()));

