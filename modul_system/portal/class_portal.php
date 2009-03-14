<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_lang_wrapper.php");

/**
 * Base class for all module-classes in the portal
 *
 * @package modul_system
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
	protected $objSession = null;			//Object containting the session-management
	/**
	 * Instance of class_template
	 *
	 * @var class_template
	 */
	protected $objTemplate = null;			//Object to handle templates
	/**
	 * Instance of class_rights
	 *
	 * @var class_rights
	 */
	protected $objRights = null;			//Object handling the right-stuff

	/**
	 * Instance of class_texte
	 *
	 * @var class_texte
	 */
	private  $objText = null;				//Object managing the textfiles

	/**
	 * Instance of class_modul_system_common
	 *
	 * @var class_modul_system_common
	 */
	private $objSystemCommon = null;

	private   $strAction;			        //current action to perform (GET/POST)
	private   $strSystemid;			        //current systemid
	private   $arrParams;			        //array containing other GET / POST / FILE variables
	private   $strArea;				        //String containing the current Area - admin or portal or installer or download
	private   $arrHistory;			        //Stack cotaining the 5 urls last visited
	protected $arrModule;			        //Array containing Infos about the current modul
	protected $strTemplateArea;		        //String containing the current Area for the templateobject
	protected $strOutput;
	protected $arrElementData;

	/**
	 * Constructor
	 *
	 * @param mixed $arrModule
	 * @param string $strSystemid
	 */
	public function __construct($arrModule = array(), $arrElementData = array(), $strSystemid = "") {
		$arrModule["p_name"] 			= "modul_portal";
		$arrModule["p_author"] 			= "sidler@mulchprod.de";
		$arrModule["p_nummer"] 			= _system_modul_id_;
        $this->arrElementData           = $arrElementData;

        //saving area
		$this->strArea = "portal";

		//Merging Module-Data
		$this->arrModule = $arrModule;

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

		//Generating all the needes Objects. For this we use our cool cool carrier-object
		//take care of loading just the necessary objects
		include_once(_realpath_."/system/class_carrier.php");
		$objCarrier = class_carrier::getInstance();
		$this->objConfig = $objCarrier->getObjConfig();
		$this->objDB = $objCarrier->getObjDB();
	    $this->objToolkit = $objCarrier->getObjToolkit($this->strArea);
		$this->objSession = $objCarrier->getObjSession();
	    $this->objText = $objCarrier->getObjText();
	    $this->objTemplate = $objCarrier->getObjTemplate();
		$this->objRights = $objCarrier->getObjRights();
		$this->objSystemCommon = new class_modul_system_common();

		//Setting template area
		$this->objTemplate->setArea($this->strArea);

		//Writing to the history
	    $this->setHistory();

		//And keep the action
		$this->strAction = $this->getParam("action");

		//set the pagename
		if($this->getParam("page") == "")
		    $this->setParam("page", $this->getPagename());

		//set the correct language
        include_once(_systempath_."/class_modul_languages_language.php");
        $objLanguage = new class_modul_languages_language();
        //set current language to the texts-object
        class_texte::getInstance()->setStrTextLanguage($objLanguage->getStrPortalLanguage());

	}


    /**
	 * Writes a value to the params-array
	 *
	 * @param string $strName Key
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

// --- SystemID & System-Table Methods ------------------------------------------------------------------


	/**
	 * Sets the current SystemID
	 *
	 * @param string $strID
	 * @return bool
	 * @final
	 */
	public final function setSystemid($strID) {
		if($this->validateSystemid($strID)) {
			$this->strSystemid = $strID;
			return true;
		}
		else
			return false;
	}

	/**
	 * Checks a systemid for the correct syntax
	 *
	 * @param string $strtID
	 * @return bool
	 * @final
	 */
	public final function validateSystemid($strID) {
	    return validateSystemid($strID);
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
	 * Generates a new SystemID
	 *
	 * @return string The new SystemID
	 * @final
	 */
	public final function generateSystemid() {
		return generateSystemid();
	}

	/**
	 * Returns the current instance of the class_rights
	 *
	 * @return object
	 */
	public function getObjRights() {
	    return $this->objRights;
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
        return $this->objSystemCommon->setStatus($strSystemid);
	}

	/**
	 * Gets the status of a systemRecord
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getStatus($strSystemid = "") {
		if($strSystemid == "0")
			$strSystemid = $this->getSystemid();
		return $this->objSystemCommon->getStatus($strSystemid);
	}

	/**
	 * Returns the userid locking the record
	 *
	 * @param string $strSystemid
	 * @return string
	 */
	public function getLockId($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
		return $this->objSystemCommon->getLockId($strSystemid);
	}

	/**
	 * Locks a systemrecord for the current user
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function lockRecord($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
        return $this->objSystemCommon->lockRecord($strSystemid);
	}

	/**
	 * Unlocks a dataRecord
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function unlockRecord($strSystemid = "")	{
		if($strSystemid == 0)
			$strSystemid = $this->getSystemid();
        return $this->objSystemCommon->unlockRecord($strSystemid);
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
		return $this->objSystemCommon->getLastEditUser($strSystemid);
	}

	/**
	 * Returns the time the record was last edited
	 *
	 * @param string $strSystemid
	 * @return int
	 */
	public function getEditDate($strSystemid = "") 	{
		if($strSystemid == 0)
			$strSystemid = $this->getSystemid();
        return $this->objSystemCommon->getEditDate($strSystemid);
	}

	/**
	 * Sets the current date as the edit-date of a system record
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function setEditDate($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
        return $this->objSystemCommon->setEditDate($strSystemid);
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
        return $this->objSystemCommon->getPrevId($strSystemid);
	}

	/**
	 * Return a complete SystemRecord
	 *
	 * @param string $strSystemid
	 * @return mixed
	 */
	public function getSystemRecord($strSystemid = "") {
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
		return $this->objSystemCommon->getSystemRecord($strSystemid);
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


// --- HistoryMethods -----------------------------------------------------------------------------------

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
     * @param <type> $arrContent
     * @param <type> $strIdentifier
     * @return <type>
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
	    include_once(_systempath_."/class_modul_pages_pagecache.php");
	    $objPagecache = new class_modul_pages_pagecache();
        return $objPagecache->flushCompletePagesCache();
	}

	/**
	 * Removes one page from the cache
	 *
	 * @param string $strPagename
	 * @return bool
	 */
	public function flushPageFromPagesCache($strPagename) {
	    include_once(_systempath_."/class_modul_pages_pagecache.php");
	    $objPagecache = new class_modul_pages_pagecache();
	    return $objPagecache->flushPageFromPagesCache($strPagename);
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
    		//For backwards-compatibility
    		elseif ($this->getParam("seite") != "")	{
    			$strReturn = $this->getParam("seite");
    		}
    		//Use the page set in the configs
    		else {
    			$strReturn = _pages_indexpage_;
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
     */
    public function portalReload($strUrlToLoad) {
        header("Location: ".str_replace("&amp;", "&", $strUrlToLoad));
    }

}
?>