<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

/**
 * Base class for all module-classes in the portal
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
abstract class class_portal  {

     /**
     * Instance of class_config
     *
     * @var class_config
     */
	protected $objConfig = null;			//Object containing config-data
	/**
	 * Instance of class_db
	 *
	 * @var class_db
	 */
	protected $objDB = null;				//Object to the database
	/**
	 * Instance of class_toolkit_portal
	 *
	 * @var class_toolkit_portal
	 */
	protected $objToolkit = null;			//Toolkit-Object
	/**
	 * Instance of class_session
	 *
	 * @var class_session
	 */
	protected $objSession = null;			//Object containing the session-management
	/**
	 * Instance of class_template
	 *
	 * @var class_template
	 */
	protected $objTemplate = null;			//Object to handle templates

	/**
	 * Instance of class_lang
	 *
	 * @var class_lang
	 */
	private  $objText = null;				//Object managing the lang-files

	/**
	 * Instance of class_module_system_common
	 *
	 * @var class_module_system_common
	 */
	private $objSystemCommon = null;

    /**
     * Instance of the current modules' definition
     *
     * @var class_module_system_module
     */
    private $objModule = null;

	private   $strAction;			        //current action to perform (GET/POST)
	private   $strSystemid;			        //current systemid
	private   $arrParams;			        //array containing other GET / POST / FILE variables
	private   $strArea;				        //String containing the current Area - admin or portal or installer or download
	private   $arrHistory;			        //Stack containing the 5 urls last visited
	protected $arrModule = array();	        //Array containing info about the current module
	protected $strTemplateArea;		        //String containing the current Area for the templateobject
	protected $strOutput;
	protected $arrElementData;

    /**
     * Constructor
     *
     * @param array $arrElementData
     * @param string $strSystemid
     */
	public function __construct($arrElementData = array(), $strSystemid = "") {

        //saving area
		$this->strArea = "portal";

		//GET / POST / FILE Params
		$this->arrParams = getAllPassedParams();

		//Setting SystemID
		if($strSystemid == "") {
			if(isset($this->arrParams["systemid"]))
				$this->setSystemid($this->arrParams["systemid"]);
			else
				$this->strSystemid = "";
		}
		else
			$this->setSystemid($strSystemid);

		//Generating all the required objects. For this we use our cool cool carrier-object
		//take care of loading just the necessary objects
		$objCarrier = class_carrier::getInstance();
		$this->objConfig = $objCarrier->getObjConfig();
		$this->objDB = $objCarrier->getObjDB();
	    $this->objToolkit = $objCarrier->getObjToolkit($this->strArea);
		$this->objSession = $objCarrier->getObjSession();
	    $this->objText = $objCarrier->getObjText();
	    $this->objTemplate = $objCarrier->getObjTemplate();
		$this->objSystemCommon = new class_module_system_common($strSystemid);

		//Writing to the history
        if(!_xmlLoader_)
            $this->setHistory();

		//And keep the action
		$this->strAction = $this->getParam("action");
        //in most cases, the list is the default action if no other action was passed
		if($this->strAction == "")
		    $this->strAction = "list";

		//set the pagename
		if($this->getParam("page") == "")
		    $this->setParam("page", $this->getPagename());

		//set the correct language
        $objLanguage = new class_module_languages_language();
        //set current language to the texts-object
        $this->objText->setStrTextLanguage($objLanguage->getStrPortalLanguage());

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
     *
     *
     * @param string $strAction
     * @return string
     * @since 3.4
     */
    public function action($strAction = "") {

        if($strAction == "")
            $strAction = $this->strAction;
        else
            $this->strAction = $strAction;

        //search for the matching method - build method name
        $strMethodName = "action".uniStrtoupper($strAction[0]).uniSubstr($strAction, 1);

        if(method_exists($this, $strMethodName)) {

            if(_xmlLoader_ === true) {
                //check it the method is allowed for xml-requests
                $objAnnotations = new class_annotations(get_class($this));
                if(!$objAnnotations->hasMethodAnnotation($strMethodName, "@xml")  && substr(get_class($this), -3) != "xml")
                    throw new class_exception("called method ".$strMethodName." not allowed for xml-requests", class_exception::$level_FATALERROR);
            }

            $this->strOutput = $this->$strMethodName();
        }
        else {

            if(_xmlLoader_ === true) {
                $objReflection = new ReflectionClass($this);
                throw new class_exception("called method ".$strMethodName." not existing for class ".$objReflection->getName(), class_exception::$level_FATALERROR);
            }

            //try to load the list-method
            $strListMethodName = "actionList";
            if(method_exists($this, $strListMethodName)) {
                $this->strOutput = $this->$strListMethodName();
            }
            else {
                $objReflection = new ReflectionClass($this);
                throw new class_exception("called method ".$strMethodName." not existing for class ".$objReflection->getName(), class_exception::$level_ERROR);
            }
        }

        return $this->strOutput;
    }


    /**
     * Writes a value to the params-array
     *
     * @param string $strKey Key
     * @param mixed $mixedValue Value
     */
	public function setParam($strKey, $mixedValue) {
		$this->arrParams[$strKey] = $mixedValue;
	}

	/**
	 * Returns a value from the params-Array
	 *
	 * @param string $strKey
	 * @return string else ""
	 */
	public function getParam($strKey) {
		if(isset($this->arrParams[$strKey]))
			return $this->arrParams[$strKey];
		else
			return "";
	}

	/**
	 * Returns the complete Params-Array
	 *
	 * @return mixed
	 */
	public final function getAllParams() {
	    return $this->arrParams;
	}

	/**
	 * returns the action used for the current request
	 *
	 * @return string
	 */
	public final function getAction() {
	    return (string)$this->strAction;
	}

    /**
	 * sets the action used for the current request
	 *
     * @param string $strAction
	 * @return string
	 */
	public final function setAction($strAction) {
	    $this->strAction = $strAction;
	}

// --- SystemID & System-Table Methods ------------------------------------------------------------------


	/**
	 * Sets the current SystemID
	 *
	 * @param string $strID
	 * @return bool
	 * @final
	 */
	public final function setSystemid($strID) {
		if(validateSystemid($strID)) {
			$this->strSystemid = $strID;
			return true;
		}
		else
			return false;
	}

	/**
	 * Returns the current SystemID
	 *
	 * @return string
	 * @final
	 */
	public final function getSystemid() {
		return $this->strSystemid;
	}

	/**
	 * Negates the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function setStatus($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->setStatus();
	}

	/**
	 * Gets the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getStatus($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
        $objCommon = new class_module_system_common($strSystemid);
		return $objCommon->getStatus();
	}

	/**
	 * Returns the name of the user who last edited the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLastEditUser($strSystemid = "") {
		if($strSystemid == 0)
			$strSystemid = $this->getSystemid();
        $objCommon = new class_module_system_common($strSystemid);
		return $objCommon->getLastEditUser();
	}

	/**
	 * Gets the Prev-ID of a record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getPrevId($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getPrevId();
	}

	/**
	 * Returns the data for a registered module
	 *
	 * @param string $strName
	 * @param bool $bitCache
	 * @return mixed
	 */
	public function getModuleData($strName, $bitCache = true) {
	    return $this->objSystemCommon->getModuleData($strName, $bitCache);
	}

	/**
	 * Returns the SystemID of a installed module
	 *
	 * @param string $strModule
	 * @return string
	 */
	public function getModuleSystemid($strModule) {
	    return $this->objSystemCommon->getModuleSystemid($strModule);
	}

	/**
	 * Generates a sorted array of systemids, reaching from the passed systemid up
	 * until the assigned module-id
	 *
	 * @param string $strSystemid
	 * @return mixed
	 */
	public function getPathArray($strSystemid = "") {
	    return $this->objSystemCommon->getPathArray($strSystemid);
	}

	/**
	 * Returns a value from the $arrModule array.
	 * If the requested key not exists, returns ""
	 *
	 * @param string $strKey
	 * @return string
	 */
	public function getArrModule($strKey) {
	    if(isset($this->arrModule[$strKey]))
	        return $this->arrModule[$strKey];
	    else
	        return "";
	}

    /**
     * Writes a key-value-pair to the arrModule
     *
     * @param string $strKey
     * @param mixed $strValue
     */
    public function setArrModuleEntry($strKey, $strValue) {
        $this->arrModule[$strKey] = $strValue;
    }



	/**
	 * Holds the last 5 URLs the user called in the Session
	 * Admin and Portal are seperated arrays, but don't care about that...
	 *
	 */
	protected function setHistory() {
	    //Loading the current history from session
		$this->arrHistory = $this->objSession->getSession($this->strArea."History");

		$strQueryString = getServer("QUERY_STRING");
		//Clean Querystring of emtpy actions
		if(uniSubstr($strQueryString, -8) == "&action=")
		   $strQueryString = substr_replace($strQueryString, "", -8);
	    //And insert just, if different to last entry
	    if($strQueryString == $this->getHistory())
	       return;
        //If we reach up here, we can enter the current query
		if($this->arrHistory !== false) {
			array_unshift($this->arrHistory, $strQueryString);
			while(count($this->arrHistory) > 5) {
				array_pop($this->arrHistory);
			}
		}
		else {
			$this->arrHistory[] = $strQueryString;
		}
		//saving the new array to session
		$this->objSession->setSession($this->strArea."History", $this->arrHistory);

		return;
	}

	/**
	 * Returns the URL at the given position (from HistoryArray)
	 *
	 * @param int $intPosition
	 * @return string
	 */
	protected function getHistory($intPosition = 0) {
		if(isset($this->arrHistory[$intPosition]))
			return $this->arrHistory[$intPosition];
		else
			return "History error!"	;
	}

// --- TextMethods & Languages --------------------------------------------------------------------------

    /**
	 * Used to get Text out of Textfiles
	 *
	 * @param string $strName
	 * @param string $strModule
	 * @param string $strArea
	 * @return string
	 */
	public function getText($strName, $strModule = "", $strArea = "") {
		if($strModule == "")
			$strModule = $this->arrModule["modul"];

		if($strArea == "")
			$strArea = $this->strArea;

		//Now we have to ask the Text-Object to return the text
		return $this->objText->getText($strName, $strModule, $strArea);
	}

	/**
	 * Returns the current Text-Object Instance
	 *
	 * @return obj
	 */
	protected function getObjText() {
	    return $this->objText;
	}

    /**
     * Loads the language to load content
     *
     * @return string
     */
    public function getPortalLanguage() {
        return $this->objSystemCommon->getStrPortalLanguage();
    }

    /**
     * Returns an instance of class_lang_wrapper, to be used with
     * class_template::fill_array()
     *
     * @return class_lang_wrapper
     */
    public final function getLangWrapper() {
        return new class_lang_wrapper($this->objText, $this->strArea, $this->arrModule["modul"]);
    }

    /**
     * Wrapper to class_template::fillTemplate().
     * Includes the passing of an class_lang_wrapper by default.
     * NOTE: Removes placeholders. If unwanted, call directly.
     *
     * @see class_template::fill_template
     * @since 3.2.0
     * @param array $arrContent
     * @param string $strIdentifier
     * @return string
     */
    public final function fillTemplate($arrContent, $strIdentifier) {
        return $this->objTemplate->fillTemplate($arrContent, $strIdentifier, true, $this->getLangWrapper());
    }


// --- PageCache Features -------------------------------------------------------------------------------

	/**
	 * Deletes the complete Pages-Cache
	 *
	 * @return bool
	 */
	public function flushCompletePagesCache() {
        return class_cache::flushCache("class_element_portal");
	}

	/**
	 * Removes one page from the cache
	 *
	 * @param string $strPagename
	 * @return bool
	 */
	public function flushPageFromPagesCache($strPagename) {
	    return class_cache::flushCache("class_element_portal", $strPagename);
	}

	/**
	 * Returns the name of the page to be loaded
	 *
	 * @return string
	 */
	public function getPagename() {
		$strReturn = "";

		//check, if the portal is disabled
		if(_system_portal_disable_ == "true") {
		    $strReturn = _system_portal_disablepage_;
		}
		else {
    		//Standard
    		if($this->getParam("page") != "") {
    			$strReturn = $this->getParam("page");
    		}
    		//Use the page set in the configs
    		else {
    			$strReturn = _pages_indexpage_;
    		}

            //disallow rendering of master-page
            if($strReturn == "master" )
                $strReturn = _pages_errorpage_;
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
     */
    public function portalReload($strUrlToLoad) {
        //replace constants in url
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        header("Location: ".str_replace("&amp;", "&", $strUrlToLoad));
    }


    /**
     * Returns the current instance of class_module_system_module, based on the current subclass.
     * Lazy-loading, so loaded on first access.
     * @return class_module_system_module|null
     */
    protected function getObjModule() {

        if($this->objModule == null)
            $this->objModule = class_module_system_module::getModuleByName($this->arrModule["modul"]);

        return $this->objModule;
    }

}
