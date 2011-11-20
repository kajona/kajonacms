<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Heart of the system - granting access to all needed objects e.g. the database or the session-object
 *
 * @package module_system
 */
class class_carrier {

    /**
     * Internal array of all params passed globally to the script
     * @var array
     */
    private static $arrParams = null;

    private $arrModule;
	private $objDB = null;
	private $objConfig = null;
	private $objSession = null;
	private $objTemplate = null;
	private $objRights = null;
	private $objText = null;
	private $objToolkitAdmin = null;
	private $objToolkitPortal = null;

	/**
	 * Current instance
	 *
	 * @var class_carrier
	 */
	private static $objCarrier = null;

	/**
	 * Constructor for class_carrier, doing nothing important,
	 * but being private ;), so use getInstance() instead
	 *
	 */
	private function __construct() {
        $this->arrModule["author"]     = "sidler@mulchprod.de";
	}

	/**
	 * Method to get an instance of class_carrier though the constructor is private
	 *
	 * @return class_carrier
	 */
	public static function getInstance() {

		if(self::$objCarrier == null) {
			self::$objCarrier = new class_carrier();
			$objConfig = self::$objCarrier->getObjConfig();
			$objDB = self::$objCarrier->getObjDB();
			//so, lets init the constants
            if(!defined("_block_config_db_loading_"))
                $objConfig->loadConfigsDatabase($objDB);
            //and init the internal session
            //SIR 2010/03: deactivated session startup right here.
            //The session-start is handled by class_session internally to avoid
            //senseless db-updates, e.g. when manipulating images
            //self::$objCarrier->getObjSession()->initInternalSession();
		}

		return self::$objCarrier;
	}

	/**
	 * Managing access to the database object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_db
	 */
	public function getObjDB() {
		//Do we have to generate the object?
		if($this->objDB == null) {
		    //require_once(_realpath_."/modul_system/system/class_db.php");
		    $this->objDB = class_db::getInstance();
			//Now we have to set up the database connection
            //SIR 2010/03: connection is established on request, lazy loading
			//$this->objDB->dbconnect();
		}
		return $this->objDB;
	}


	/**
	 * Managing access to the rights object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_rights
	 */
	public function getObjRights() {
		//Do we have to generate the object?
		if($this->objRights == null) {
			$this->objRights = class_rights::getInstance();
		}
		return $this->objRights;
	}

	/**
	 * Managing access to the config object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_config
	 */
	public function getObjConfig() {
		//Do we have to generate the object?
		if($this->objConfig == null) {
			$this->objConfig = class_config::getInstance();

			//Loading the config-Files
			//Invocation removed, all configs from database
			//$this->objConfig->loadConfigsFilesystem();
		}
		return $this->objConfig;
	}

	/**
	 * Managing access to the session object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_session
	 */
	public function getObjSession() {
		//Do we have to generate the object?
		if($this->objSession == null) {
			$this->objSession = class_session::getInstance();
		}
		return $this->objSession;
	}


	/**
	 * Managing access to the template object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_template
	 */
	public function getObjTemplate() {
		//Do we have to generate the object?
		if($this->objTemplate == null) {
			$this->objTemplate = class_template::getInstance();
		}
		return $this->objTemplate;
	}

	/**
	 * Managing access to the text object. Use ONLY this method to
	 * get an instance!
	 *
	 * @return class_texte
	 */
	public function getObjText() {
		//Do we have to generate the object?
		if($this->objText == null) {
			$this->objText = class_texte::getInstance();
		}
		return $this->objText;
	}


	/**
	 * Managing access to the toolkit object. Use ONLY this method to
	 * get an instance!
	 *
	 * @param string $strArea
	 * @return class_toolkit_admin or class_toolkit_portal
	 */
	public function getObjToolkit($strArea) {
		//Do we have to generate the object?
		if($strArea == "admin") {
			//Get the object
			if($this->objToolkitAdmin == null) {
                //decide which class to load
                $strAdminToolkitClass = $this->getObjConfig()->getConfig("admintoolkit");
                if($strAdminToolkitClass == "")
                    $strAdminToolkitClass = "class_toolkit_admin";

				require_once(_adminpath_."/".$strAdminToolkitClass.".php");
				$this->objToolkitAdmin = new $strAdminToolkitClass();
			}
			return $this->objToolkitAdmin;
		}
		elseif ($strArea == "portal") {
			if($this->objToolkitPortal == null) {
				require_once(_portalpath_."/class_toolkit_portal.php");
				$this->objToolkitPortal = new class_toolkit_portal();
			}
			return $this->objToolkitPortal;
		}
	}

    /**
     * Returns all params passed to the system, including $_GET, $_POST; $_FILES
     * @return array
     */
    public static function getAllParams() {
        if(self::$arrParams == null)
            self::$arrParams = array_merge(getArrayGet(), getArrayPost(), getArrayFiles());

        return self::$arrParams;
    }

}
?>