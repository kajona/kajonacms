<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

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
 * @see class_admin::action()
 */
abstract class class_portal_controller extends class_abstract_controller
{
    /**
     * @var array
     */
    protected $arrElementData = array();

    /**
     * @inject portaltoolkit
     * @var class_toolkit_portal
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
        if($this->getParam("page") == "") {
            $this->setParam("page", $this->getPagename());
        }

        //set the correct language
        $objLanguage = new class_module_languages_language();
        //set current language to the texts-object
        $this->getObjLang()->setStrTextLanguage($objLanguage->getStrPortalLanguage());

        $this->arrElementData = $arrElementData;
    }


    /**
     * This method triggers the internal processing.
     * It may be overridden if required, e.g. to implement your own action-handling.
     * By default, the method to be called is set up out of the action-param passed.
     * Example: The action requested is named "newPage". Therefore, the framework tries to
     * call actionNewPage(). If now method matching the schema is found, nothing is done.
     * <b> Please note that this is different from the admin-handling! </b> In the case of admin-classes,
     * an exception is thrown. But since there could be many modules on a single page, not each module
     * may be triggered.
     * Since Kajona 4.0, the check on declarative permissions via annotations is supported.
     * Therefore the list of permissions, named after the "permissions" annotation are validated against
     * the module currently loaded.
     *
     *
     * @param string $strAction
     *
     * @see class_rights::validatePermissionString
     * @throws class_exception
     * @return string
     * @since 3.4
     */
    public function action($strAction = "") {

        if($strAction != "") {
            $this->setAction($strAction);
        }

        $strAction = $this->getAction();

        //search for the matching method - build method name
        $strMethodName = "action" . uniStrtoupper($strAction[0]) . uniSubstr($strAction, 1);

        $objAnnotations = new class_reflection(get_class($this));
        if(method_exists($this, $strMethodName)) {

            //validate the permissions required to call this method, the xml-part is validated afterwards
            $strPermissions = $objAnnotations->getMethodAnnotationValue($strMethodName, "@permissions");
            if($strPermissions !== false) {

                if(validateSystemid($this->getSystemid()) && class_objectfactory::getInstance()->getObject($this->getSystemid()) != null) {
                    $objObjectToCheck = class_objectfactory::getInstance()->getObject($this->getSystemid());
                }
                else {
                    $objObjectToCheck = $this->getObjModule();
                }

                if(!class_carrier::getInstance()->getObjRights()->validatePermissionString($strPermissions, $objObjectToCheck)) {
                    $this->strOutput = $this->getLang("commons_error_permissions");

                    //redirect to the error page
                    if($this->getPagename() != class_module_system_setting::getConfigValue("_pages_errorpage_")) {
                        $this->portalReload(class_link::getLinkPortalHref(class_module_system_setting::getConfigValue("_pages_errorpage_"), ""));
                        return "";
                    }

                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_ERROR);
                }
            }

            if(_xmlLoader_ === true) {
                //check it the method is allowed for xml-requests
                $objAnnotations = new class_reflection(get_class($this));
                if(!$objAnnotations->hasMethodAnnotation($strMethodName, "@xml") && substr(get_class($this), -3) != "xml") {
                    throw new class_exception("called method " . $strMethodName . " not allowed for xml-requests", class_exception::$level_FATALERROR);
                }
            }

            $this->strOutput = $this->$strMethodName();
        }
        else {

            if(_xmlLoader_ === true) {
                $objReflection = new ReflectionClass($this);
                throw new class_exception("called method " . $strMethodName . " not existing for class " . $objReflection->getName(), class_exception::$level_FATALERROR);
            }

            //try to load the list-method
            $strListMethodName = "actionList";
            if(method_exists($this, $strListMethodName)) {

                $strPermissions = $objAnnotations->getMethodAnnotationValue($strListMethodName, "@permissions");
                if($strPermissions !== false) {

                    if(validateSystemid($this->getSystemid()) && class_objectfactory::getInstance()->getObject($this->getSystemid()) != null) {
                        $objObjectToCheck = class_objectfactory::getInstance()->getObject($this->getSystemid());
                    }
                    else {
                        $objObjectToCheck = $this->getObjModule();
                    }

                    if(!class_carrier::getInstance()->getObjRights()->validatePermissionString($strPermissions, $objObjectToCheck)) {
                        $this->strOutput = $this->getLang("commons_error_permissions");
                        throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_ERROR);
                    }
                }

                $this->strOutput = $this->$strListMethodName();
            }
            else {
                $objReflection = new ReflectionClass($this);
                throw new class_exception("called method " . $strMethodName . " not existing for class " . $objReflection->getName(), class_exception::$level_ERROR);
            }
        }

        return $this->strOutput;
    }



    /**
     * Gets the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @return int
     * @deprecated call getStatus on a model-object directly
     */
    public function getStatus($strSystemid = "") {
        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getIntRecordStatus();
    }

    /**
     * Returns the name of the user who last edited the record
     *
     * @param string $strSystemid
     *
     * @return string
     */
    public function getLastEditUser($strSystemid = "") {
        if($strSystemid == 0) {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
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
    public function getPrevId($strSystemid = "") {
        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getPrevId();
    }


    /**
     * Returns the URL at the given position (from HistoryArray)
     *
     * @param int $intPosition
     * @deprecated use class_history::getPortalHistory() instead
     * @see class_history::getPortalHistory()
     * @return string
     */
    protected function getHistory($intPosition = 0) {
        $objHistory = new class_history();
        return $objHistory->getPortalHistory($intPosition);
    }


    /**
     * Wrapper to class_template::fillTemplate().
     * Includes the passing of an class_lang_wrapper by default.
     * NOTE: Removes placeholders. If unwanted, call directly.
     *
     * @param array $arrContent
     * @param string $strIdentifier
     *
     * @see class_template::fill_template
     * @since 3.2.0
     *
     * @deprecated use class:template::fill_template directly
     * @return string
     */
    public final function fillTemplate($arrContent, $strIdentifier) {
        return $this->objTemplate->fillTemplate($arrContent, $strIdentifier, true);
    }


    /**
     * Returns the name of the page to be loaded
     *
     * @return string
     */
    public function getPagename() {
        //check, if the portal is disabled
        if(class_module_system_setting::getConfigValue("_system_portal_disable_") == "true") {
            $strReturn = class_module_system_setting::getConfigValue("_system_portal_disablepage_");
        }
        else {
            //Standard
            if($this->getParam("page") != "") {
                $strReturn = $this->getParam("page");
            }
            //Use the page set in the configs
            else {
                $strReturn = class_module_system_setting::getConfigValue("_pages_indexpage_") != "" ? class_module_system_setting::getConfigValue("_pages_indexpage_") : "index";
            }

            //disallow rendering of master-page
            if($strReturn == "master") {
                $strReturn = class_module_system_setting::getConfigValue("_pages_errorpage_");
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
    public function getModuleOutput() {
        return $this->strOutput;
    }

    /**
     * Use this method to do a header-redirect to a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     * @return void
     */
    public function portalReload($strUrlToLoad) {
        //replace constants in url
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        class_response_object::getInstance()->setStrRedirectUrl($strUrlToLoad);
    }


    /**
     * @return string
     * @deprecated use class_module_languages_language directly
     * @see class_module_languages_language::getPortalLanguage()
     */
    protected function getStrPortalLanguage() {
        $objLanguage = new class_module_languages_language();
        return $objLanguage->getPortalLanguage();
    }

}
