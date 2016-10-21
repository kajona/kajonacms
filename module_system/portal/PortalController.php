<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Portal;

use Kajona\System\System\AbstractController;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\History;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemSetting;
use ReflectionClass;


/**
 * Base class for all portal-interface classes.
 * Extend this class (or one of its subclasses) to generate portal-views.
 *
 * The action-method() takes care of calling your action-handlers.
 * If the URL-param "action" is set to "list", the controller calls your
 * action method "actionList()". Return the rendered output, everything else is generated
 * automatically.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @see Admin::action()
 */
abstract class PortalController extends AbstractController
{
    /**
     * @var array
     */
    protected $arrElementData = array();

    /**
     * @inject system_portaltoolkit
     * @var ToolkitPortal
     */
    protected $objToolkit;

    /**
     * Constructor
     *
     * @param array $arrElementData
     * @param string $strSystemid
     */
    public function __construct($arrElementData = array(), $strSystemid = "")
    {
        parent::__construct($strSystemid);

        //set the pagename
        if ($this->getParam("page") == "") {
            $this->setParam("page", $this->getPagename());
        }

        //set the correct language
        $objLanguage = new LanguagesLanguage();
        //set current language to the texts-object
        $this->getObjLang()->setStrTextLanguage($objLanguage->getStrPortalLanguage());

        $this->arrElementData = $arrElementData;
    }

    /**
     * Gets the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @return int
     * @deprecated call getStatus on a model-object directly
     */
    public function getStatus($strSystemid = "")
    {
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new SystemCommon($strSystemid);
        return $objCommon->getIntRecordStatus();
    }

    /**
     * Returns the name of the user who last edited the record
     *
     * @param string $strSystemid
     *
     * @return string
     */
    public function getLastEditUser($strSystemid = "")
    {
        if ($strSystemid == 0) {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new SystemCommon($strSystemid);
        return $objCommon->getLastEditUser();
    }

    /**
     * Gets the Prev-ID of a record
     *
     * @param string $strSystemid
     *
     * @return string
     * @deprecated
     */
    public function getPrevId($strSystemid = "")
    {
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new SystemCommon($strSystemid);
        return $objCommon->getPrevId();
    }


    /**
     * Returns the URL at the given position (from HistoryArray)
     *
     * @param int $intPosition
     *
     * @deprecated use History::getPortalHistory() instead
     * @see History::getPortalHistory()
     * @return string
     */
    protected function getHistory($intPosition = 0)
    {
        $objHistory = new History();
        return $objHistory->getPortalHistory($intPosition);
    }


    /**
     * Wrapper to Template::fillTemplate().
     * Includes the passing of an LangWrapper by default.
     * NOTE: Removes placeholders. If unwanted, call directly.
     *
     * @param array $arrContent
     * @param string $strIdentifier
     *
     * @see Template::fill_template
     * @since 3.2.0
     *
     * @deprecated use Template::fill_template directly
     * @return string
     */
    public final function fillTemplate($arrContent, $strIdentifier)
    {
        return $this->objTemplate->fillTemplate($arrContent, $strIdentifier, true);
    }


    /**
     * Returns the name of the page to be loaded
     *
     * @return string
     */
    public function getPagename()
    {
        //check, if the portal is disabled
        if (SystemSetting::getConfigValue("_system_portal_disable_") == "true") {
            $strReturn = SystemSetting::getConfigValue("_system_portal_disablepage_");
        }
        else {
            //Standard
            if ($this->getParam("page") != "") {
                $strReturn = $this->getParam("page");
            }
            //Use the page set in the configs
            else {
                $strReturn = SystemSetting::getConfigValue("_pages_indexpage_") != "" ? SystemSetting::getConfigValue("_pages_indexpage_") : "index";
            }

            //disallow rendering of master-page
            if ($strReturn == "master") {
                $strReturn = SystemSetting::getConfigValue("_pages_errorpage_");
            }
        }
        $strReturn = htmlspecialchars($strReturn);
        return $strReturn;
    }

    /**
     * Returns the data created by the child-class
     *
     * @return string
     */
    public function getModuleOutput()
    {
        return $this->strOutput;
    }

    /**
     * Use this method to do a header-redirect to a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     *
     * @return void
     */
    public function portalReload($strUrlToLoad)
    {
        //replace constants in url
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        ResponseObject::getInstance()->setStrRedirectUrl($strUrlToLoad);
    }


    /**
     * @return string
     * @deprecated use LanguagesLanguage directly
     * @see LanguagesLanguage::getPortalLanguage()
     */
    protected function getStrPortalLanguage()
    {
        $objLanguage = new LanguagesLanguage();
        return $objLanguage->getPortalLanguage();
    }

}
