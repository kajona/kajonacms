<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                                      *
********************************************************************************************************/

if(!require_once("./system/includes.php"))
	die("Error including necessary files");


//Determin the area to load
if(issetGet("admin") && getGet("admin") == 1)
	define("_admin_", true);
else
	define("_admin_", false);
	
	
define("_xmlLoader_", true);	

/**
 * Class handling all requests to be served with xml
 *
 * @package modul_system
 */
class class_xml {
    
    /**
	 * class db
	 *
	 * @var class_db
	 */
	private $objDB;

	/**
	 * class template
	 *
	 * @var class_template
	 */
	private $objTemplate;

	/**
	 * class session
	 *
	 * @var class_session
	 */
	private $objSession;

    public function __construct() {
        //init the system
		$objCarrier = class_carrier::getInstance();
		$this->objDB = $objCarrier->getObjDb();
		$this->objTemplate = $objCarrier->getObjTemplate();
		$this->objSession = $objCarrier->getObjSession();
    }

    /**
     * Tries to load the requested module and invokes the passed actions
     *
     */
    public function process_request() {
        $strContent = "";
        //Loading the details for the wanted module
		if(issetGet("module"))
			$strModule = getGet("module");
		else
			$strModule = "";

		if(issetGet("action"))
			$strAction = getGet("action");
		else
			$strAction = "";

		$strModule = htmlspecialchars($strModule);
		$strAction = htmlspecialchars($strAction);

		if($strModule == "" || $strAction == "") {
		    $strContent = "<error>An error occured, malformed request</error>";
		}
		else {
		    //any reaction on language-commmands?
    	    if(issetGet("language")) {
	            $objLanguage = new class_modul_languages_language();
	            $objLanguage->setStrPortalLanguage(getGet("language"));
    	    }

            //Requested module installed?

            $objModule = class_modul_system_module::getModuleByName($strModule);
            if($objModule != null) {
                if(_admin_) {
                    if($this->objSession->isLoggedin() && $this->objSession->isAdmin()) {
                        //Load the admin-part
                        if($objModule->getStrXmlNameAdmin() != "") {
                            $strClassname = str_replace(".php", "", $objModule->getStrXmlNameAdmin());
                            $objModuleRequested = new $strClassname();
                            $strContent = $objModuleRequested->action($strAction);
                        }
                    }
                    else {
					    throw new class_exception("Sorry, but you don't have the needed permissions to access the admin-area", class_exception::$level_FATALERROR);
					}
                }
                else {
                    //Load the portal parts
                    if($objModule->getStrXmlNamePortal() != "") {
                        $strClassname = str_replace(".php", "", $objModule->getStrXmlNamePortal());
                        $objModuleRequested = new $strClassname();
                        $strContent = $objModuleRequested->action($strAction);
                    }
                }
            }
		}

		$strCompleteXML = $this->createXmlOutput($strContent);

		//check for conditionalGet Headers
		if(checkConditionalGetHeaders(sha1($strCompleteXML)))
		    return;

		//send conditinalGetHeaders
		sendConditionalGetHeaders(sha1($strCompleteXML));

		//send global headers
		$this->sendHeader();

		//compress output
		$objGzip = new class_gzip();
		//return $strCompleteXML;
		echo $objGzip->compressOutput($strCompleteXML);
    }


    /**
     * Creates the xml-body.
     *
     * @param string $strContent
     * @return string
     */
    private function createXmlOutput($strContent) {
        if($strContent == "")
            $strContent = "<error>An error occured, malformed request</error>";

        $strReturn = "";
        $strReturn .=
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $strReturn .= $strContent;

        //fill kajona placeholder
        $objTemplate = class_carrier::getInstance()->getObjTemplate();
        $objTemplate->setTemplate($strReturn);
        $objTemplate->fillConstants();
        $objTemplate->deletePlaceholder();
        $strReturn = $objTemplate->getTemplate();

        return $strReturn;
    }


    /**
     * Sends header for the requested content
     *
     */
    private function sendHeader() {
        header("Content-Type: text/xml; charset=utf-8");
    }

}

$objXML = new class_xml();
$objXML->process_request();

?>