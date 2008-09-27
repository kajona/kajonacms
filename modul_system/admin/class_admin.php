<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_admin.php																						*
* 	Base-class of all admin-classes																		*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

include_once(_systempath_."/class_modul_system_common.php");

/**
 * The Base-Class for all other admin-classes
 *
 * @package modul_system
 */
abstract class class_admin {


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
	 * Instance of class_toolkit_<area>
	 *
	 * @var class_toolkit_admin
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
	private   $objText = null;				//Object managing the textfiles

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
	private   $arrOutput;					      //Array containing the admin-output
	private   $arrValidationErrors = array();     //Array to keep found validation errors

	/**
	 * Constructor
	 *
	 * @param string $arrModul
	 * @param string $strSystemid
	 */
	public function __construct($arrModul = array(), $strSystemid = "") {

		$arrModul["p_name"] 			= "modul_admin";
		$arrModul["p_author"] 			= "sidler@mulchprod.de";
		$arrModul["p_nummer"] 			= _system_modul_id_;

		//default-template: main.tpl
		if(!key_exists("template", $arrModul))
		    $arrModul["template"] 		= "/main.tpl";

		//Registering Area
		$this->strArea = "admin";

		//Merging Module-Data
		$this->arrModule =  $arrModul;

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


		//Generating all the needed Objects. For this we use our cool cool carrier-object
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

		//Setting template area LATERON THE SKIN IS BEING SET HERE
		$this->setTemplateArea("");

		//Writing to the history
		$this->setHistory();

		//And keep the action
		$this->strAction = $this->getParam("action");
		//in most cases, the list is the default action if no other action was passed
		if($this->strAction == "")
		    $this->strAction = "list";

		//Unlock old records still being locked
		$this->objSystemCommon->unlockOldRecords();

		//set the correct language to the text-object
		$this->objText->setStrTextLanguage($this->objSession->getAdminLanguage(true));
	}

// --- Common Methods -----------------------------------------------------------------------------------


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
	 * @final
	 */
	public final function getAllParams() {
	    return $this->arrParams;
	}

	/**
	 * returns the action used for the current request
	 *
	 * @return string
	 * @final
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
	 * @see functions.php
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
	 * @see functions.php
	 * @final
	 */
	public final function generateSystemid() {
		return generateSystemid();
	}

	/**
	 * Returns the current instance of the class_rights
	 *
	 * @return object
	 * @final
	 */
	public final  function getObjRights() {
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
		if($strSystemid == "")
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
	 * Sets the Position of a SystemRecord in the currect level one position upwards or downwards
	 *
	 * @param string $strIdToShift
	 * @param string $strDirection upwards || downwards
	 * @return void
	 */
	public function setPosition($strIdToShift, $strDirection = "upwards") {
	    return $this->objSystemCommon->setPosition($strIdToShift, $strDirection);
	}

	/**
	 * Sets the position of systemid using a given value.
	 *
	 * @param string $strIdToSet
	 * @param int $intPosition
	 */
	public function setAbsolutePosition($strIdToSet, $intPosition) {
		return $this->objSystemCommon->setAbsolutePosition($strIdToSet, $intPosition);
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
		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
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
	 * Admin and Portal are seperated arrays, but don't worry anyway...
	 *
	 */
	protected function setHistory() {
	    //Loading the current history from session
		$this->arrHistory = $this->objSession->getSession($this->strArea."History");

		$strQueryString = getServer("QUERY_STRING");
		//Clean Querystring of emtpy actions
		if(uniSubstr($strQueryString, -8) == "&action=")
		   $strQueryString = substr_replace($strQueryString, "", -8);
		//Just do s.th., if not in the rights-mgmt
	    if(uniStrpos($strQueryString, "module=right") !== false)
	       return;
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

// --- TextMethods --------------------------------------------------------------------------------------

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
	 * Sets the current area in the template object to have it work as expected
	 *
	 * @param string $strArea
	 */
	protected  function setTemplateArea($strArea) {
	    if($this->objTemplate != null)
		    $this->objTemplate->setArea($this->strArea.$strArea);
	}


// --- PageCache Features -------------------------------------------------------------------------------

	/**
	 * Deletes the complete Pages-Cache
	 *
	 * @return bool
	 */
	public function flushCompletePagesCache() {
	    include_once(_systempath_."/class_pagecache.php");
	    $objPagecache = new class_pagecache();
        return $objPagecache->flushCompletePagesCache();
	}

	/**
	 * Removes one page from the cache
	 *
	 * @param string $strPagename
	 * @return bool
	 */
	public function flushPageFromPagesCache($strPagename) {
	    include_once(_systempath_."/class_pagecache.php");
	    $objPagecache = new class_pagecache();
	    return $objPagecache->flushPageFromPagesCache($strPagename);
	}




// --- OutputMethods ------------------------------------------------------------------------------------

	/**
	 * Returns the complete Template.
	 * Collects all outputs and invokes the navi generation, ...
	 *
	 * @return string
	 * @final
	 */
	public final function getModuleOutput() {
		//Calling the contentsetter
		$this->arrOutput["content"] = $this->getOutputContent();
		$this->arrOutput["mainnavi"] = $this->getOutputMainNavi();
		$this->arrOutput["modulenavi"] = $this->getOutputModuleActionsNavi();
		$this->arrOutput["moduletitle"] = $this->getOutputModuleTitle();
		$this->arrOutput["login"] = $this->getOutputLogin();
		$this->arrOutput["quickhelp"] = $this->getQuickHelp();
		$this->arrOutput["module_id"] = $this->arrModule["moduleId"];
		$this->arrOutput["webpathTitle"] = urldecode(str_replace(array("http://", "https://"), array("", ""), _webpath_));
		$this->arrOutput["head"] = "<script language=\"Javascript\" type=\"text/javascript\">if(typeof KAJONA_DEBUG == 'undefined' || KAJONA_DEBUG == null) KAJONA_DEBUG = ".$this->objConfig->getDebug("debuglevel").";</script>";
		//Loading the wanted Template
		//if requested the pe, load different template
		if($this->getParam("peClose") == 1 || $this->getParam("pe") == 1) {
		    //add suffix
		    try {
		        $strTemplate = str_replace(".tpl", "", $this->arrModule["template"])."_portaleditor.tpl";
		        $strTemplateID = $this->objTemplate->readTemplate($strTemplate, "", false, true);
		    } catch (class_exception $objException) {
		        //An error occured. In most cases, this is because the user ist not logged in, so the login-template was requested.
		        if($this->arrModule["template"] == "/login.tpl")
		            throw new class_exception("You have to be logged in to use the portal editor!!!", class_exception::$level_ERROR);
		    }
		}
		else
		    $strTemplateID = $this->objTemplate->readTemplate($this->arrModule["template"]);
		return $this->objTemplate->fillTemplate($this->arrOutput, $strTemplateID);
	}


	/**
	 * Loads the content the module itself created.
	 * You have to overwrite this method in order to display your own content in the admin-area!
	 *
	 * @return string
	 */
	protected function getOutputContent() {
	    return "Module should overwrite ".__METHOD__." method!";
	}

	/**
	 * Tries to generate a quick-help button.
	 * Tests for exisiting help texts
	 *
	 * @return string
	 */
	private function getQuickHelp() {
        $strReturn = "";

        //Text for the current action available?
        //different loading when editing page-elements
        if($this->getParam("module") == "pages_content" && ($this->getParam("action") == "editElement" || $this->getParam("action") == "newElement")) {
            
            if($this->getParam("action") == "editElement") {
                $objElement = new class_modul_pages_pageelement($this->getSystemid());
            }
            else if ($this->getParam("action") == "newElement") {
                $strPlaceholderElement = $this->getParam("element");
                $objElement = class_modul_pages_element::getElement($strPlaceholderElement);    
            }
            //Load the class to create an instance
            include_once(_adminpath_."/elemente/".$objElement->getStrClassAdmin());
            //Build the class-name
            $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
            //and finally create the object
            $objElement = new $strElementClass();
            $strTextname = "quickhelp_".$objElement->getArrModule("name");
            $strText = class_carrier::getInstance()->getObjText()->getText($strTextname, $objElement->getArrModule("modul"), "admin");
        }
        else {
            $strTextname = "quickhelp_".$this->strAction;
            $strText = $this->getText($strTextname);
        }
        
        
        if($strText != "!".$strTextname."!") {
            //Text found, embed the quickhelp into the current skin
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp");
            $arrTemplate = array();
            $arrTemplate["title"] = $this->getText("quickhelp_title", "system");
            $arrTemplate["text"] = $strText;
            $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

            //and the button
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp_button");
            $arrTemplate = array();
            $arrTemplate["text"] = $this->getText("quickhelp_title", "system");
            $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        }

        return $strReturn;
	}

	/**
	 * Writes the Main Navi, overwrite if needed ;)
	 * Creates a list of all installed modules
	 *
	 * @return string
	 */
	protected function getOutputMainNavi() {
		if($this->objSession->isLoggedin()) {
			$strNavigation = "";
			//Loading all Modules
			include_once(_systempath_."/class_modul_system_module.php");
			$arrModules = class_modul_system_module::getModulesInNaviAsArray();
			$intI = 0;
			$arrModuleRows = array();
			foreach ($arrModules as $arrModule) {
				if($this->objRights->rightView($arrModule["module_id"])) {
					//Generate a view infos
				    $arrModuleRows[$intI]["rawName"] = $arrModule["module_name"];
					$arrModuleRows[$intI]["name"] = $this->getText("modul_titel", $arrModule["module_name"]);
					$arrModuleRows[$intI]["link"] = getLinkAdmin($arrModule["module_name"], "", "", $arrModule["module_name"], $arrModule["module_name"], "", true, "adminModuleNavi");
					$arrModuleRows[$intI]["href"] = getLinkAdminHref($arrModule["module_name"], "");
					$intI++;
				}
			}
			//NOTE: Some special Modules need other highlights
			if($this->arrModule["name"] == "modul_pages_elemente")
			    $strCurrent = "modul_pages";
			else
			    $strCurrent = $this->arrModule["name"];

			return $this->objToolkit->getAdminModuleNavi($arrModuleRows, $strCurrent);
		}
	}

	/**
	 * Writes the navigaiton to represent the action-navigation.
	 * Each module can create its own actions
	 *
	 * @return string
	 */
	private function getOutputModuleActionsNavi() {
		if($this->objSession->isLoggedin()) {
		    $arrItems = $this->getOutputModuleNavi();
		    $arrFinalItems = array();
		    //build array of final items
		    foreach($arrItems as $arrOneItem) {
		        $bitAdd = false;
		        switch ($arrOneItem[0]) {
		        	case "view":
                        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "edit":
                        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "delete":
                        if($this->objRights->rightDelete($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right":
                        if($this->objRights->rightRight($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right1":
                        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right2":
                        if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right3":
                        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right4":
                        if($this->objRights->rightRight4($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "right5":
                        if($this->objRights->rightRight5($this->getModuleSystemid($this->arrModule["modul"])))
                            $bitAdd = true;
		        		break;
		        	case "":
		        	    $bitAdd = true;
		        	    break;
		        	default:
		        		break;
		        }

		        if($bitAdd || $arrOneItem[1] == "")
                    $arrFinalItems[] = $arrOneItem[1];
		    }

			//Pass to the skin-object
            return $this->objToolkit->getAdminModuleActionNavi($arrFinalItems);
		}
	}


	/**
	 * Writes the ModuleNavi, overwrite if needed
	 * Use two-dim arary:
	 * array[
	 *     array["right", "link"],
	 *     array["right", "link"]
	 * ]
	 *
	 * @return array array containing all links
	 */
	protected function getOutputModuleNavi() {
		return array();
	}

	/**
	 * Writes the ModuleTitle, overwrite if needed
	 *
	 */
	protected function getOutputModuleTitle() {
	    if($this->getText("modul_titel") != "!modul_titel")
	       return $this->getText("modul_titel");
	    else
	       return $this->arrModule["name"];
	}

	/**
	 * Writes the SessionInfo, overwrite if needed
	 *
	 */
	protected function getOutputLogin() {
		include_once(_adminpath_."/class_login_admin.php");
		$objLogin = new class_login_admin();
		return $objLogin->getLoginStatus();
	}

//--- FORM-Validation -----------------------------------------------------------------------------------

    /**
     * Method used to validate posted form-values.
     * NOTE: To work with this method, the derived class needs to implement
     * a method "getRequiredFields()", returning an array of field to validate.
     * The array returned by getRequiredFields() has to fit the format
     *  [fieldname] = type, whereas type can be one of
     * string, number, email, folder, systemid
     *
     * The array saved in $this->$arrValidationErrors return by this method is empty in case of no validation Errors,
     * otherwise an array with the structure
     * [nonvalidField] = text from objText
     * is being created.
     *
     * @return bool
     */
    protected final function validateForm() {
        $arrReturn = array();

        $arrFieldsToCheck = $this->getRequiredFields();

        foreach($arrFieldsToCheck as $strFieldname => $strType) {

            if($strType == "string") {
                if(!checkText($this->getParam($strFieldname), 2))
                    $arrReturn[$strFieldname] = $this->getText("required_".$strFieldname);
            }
            elseif($strType == "number") {
                if(!checkNumber($this->getParam($strFieldname)))
                    $arrReturn[$strFieldname] = $this->getText("required_".$strFieldname);
            }
            elseif($strType == "email") {
                if(!checkEmailaddress($this->getParam($strFieldname)))
                    $arrReturn[$strFieldname] = $this->getText("required_".$strFieldname);
            }
            elseif($strType == "folder") {
                if(!checkFolder($this->getParam($strFieldname)))
                    $arrReturn[$strFieldname] = $this->getText("required_".$strFieldname);
            }
            elseif($strType == "systemid") {
                if(!validateSystemid($this->getParam($strFieldname)))
                    $arrReturn[$strFieldname] = $this->getText("required_".$strFieldname);
            }
            else {
               $arrReturn[$strFieldname] = "No or unknown validation-type for ".$strFieldname." given";
            }

        }
        $this->arrValidationErrors = array_merge($this->arrValidationErrors, $arrReturn);
        return (count($arrReturn) == 0);
    }

    /**
     * Overwrite this function, if you want to validate passed form-input
     *
     * @return mixed
     */
    protected function getRequiredFields() {
        return array();
    }

    /**
     * Returns the array of validationErrors
     *
     * @return mixed
     */
    public function getValidationErrors() {
        return $this->arrValidationErrors;
    }

    /**
     * Adds a validation error to the array of errors
     *
     * @param string $strField
     * @param string $strErrormessage
     */
    public function addValidationError($strField, $strErrormessage) {
        $this->arrValidationErrors[$strField] = $strErrormessage;
    }

    /**
     * Use this method to reload a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     */
    public function adminReload($strUrlToLoad) {
        //No redirect, if close-Command for admin-area should be sent
        if($this->getParam("peClose") == "") {
            header("Location: ".$strUrlToLoad);
        }
    }

    /**
     * Loads the language to edit content
     *
     * @return string
     */
    public function getLanguageToWorkOn() {
        return $this->objSystemCommon->getStrAdminLanguageToWorkOn();
    }

}

?>