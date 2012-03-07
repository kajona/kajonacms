<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a element. This is the "raw"-element, not the element on a page
 * Elements DON'T have systemids!
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 *
 * @todo make real records out of the element-records, so with a matching systemid
 */
class class_module_pages_element extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn element_name
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn element_class_portal
     */
    private $strClassPortal = "";

    /**
     * @var string
     * @tableColumn element_class_admin
     */
    private $strClassAdmin = "";

    /**
     * @var string
     * @tableColumn element_repeat
     */
    private $intRepeat = "";

    /**
     * @var string
     * @tableColumn element_cachetime
     */
    private $intCachetime = "";

    /**
     * @var string
     * @tableColumn element_version
     */
    private $strVersion = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "pages");
        $this->setArrModuleEntry("moduleId", _pages_modul_id_);

		//base class
		parent::__construct("");

        $this->setSystemid($strSystemid);
        if($strSystemid != "")
            $this->initObjectInternal();

    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    public function rightView() {
        return true;
    }

    public function rightEdit() {
        return true;
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elemente");
        if($strName == "!element_".$this->getStrName()."_name!")
            $strName = $this->getStrName();
        else
            $strName .= " (".$this->getStrName().")";
        return $strName;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_dot.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return " V ".$this->getStrVersion()." (".($this->getIntCachetime() == "-1" ? "<b>".$this->getIntCachetime()."</b>" : $this->getIntCachetime()).")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        $objAdminInstance = $this->getAdminElementInstance();
        return $objAdminInstance->getElementDescription();
    }


    /**
     * Initalises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."element WHERE element_id=?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        $this->setStrName($arrRow["element_name"]);
        $this->setStrClassAdmin($arrRow["element_class_admin"]);
        $this->setStrClassPortal($arrRow["element_class_portal"]);
        $this->setIntCachetime($arrRow["element_cachetime"]);
        $this->setIntRepeat($arrRow["element_repeat"]);
        $this->setStrVersion($arrRow["element_version"]);

        $this->setSystemid($arrRow["element_id"]);
    }


    /**
     * Updates the current object to the database
     * @overwrites class_model::updateObjectToDb()
     * @param bool $strPrevId
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        if($this->getSystemid() == "") {

            $strElementid = generateSystemid();
            $this->setSystemid($strElementid);

            $strQuery = "INSERT INTO "._dbprefix_."element
					(element_id, element_name, element_class_portal, element_class_admin, element_repeat, element_cachetime, element_version) VALUES
					(?, ?, ?, ?, ?, ?, ?)";

            return $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $this->getStrName(), $this->getStrClassPortal(), $this->getStrClassAdmin(), (int)$this->getIntRepeat(), (int)$this->getIntCachetime(), $this->getStrVersion() ));
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element SET
                            element_name = ?,
                            element_class_portal = ?,
                            element_class_admin = ?,
                            element_cachetime = ?,
                            element_repeat = ?,
                            element_version = ?
                            WHERE element_id= ?";
            return $this->objDB->_pQuery($strQuery, array( $this->getStrName(), $this->getStrClassPortal(), $this->getStrClassAdmin(), $this->getIntCachetime(), $this->getIntRepeat(), $this->getStrVersion(), $this->getSystemid()));
        }

    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Loads all installed Elements
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return class_module_pages_element[]
     * @static
     */
	public static function getAllElements($intStart = null, $intEnd = null) {
		$strQuery = "SELECT element_id FROM "._dbprefix_."element ORDER BY element_name";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_module_pages_element($arrOneId["element_id"]);

		return $arrReturn;
	}

    /**
     * Counts the number of elements available
     * @static
     * @return int
     */
    public static function getElementCount() {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element";
        $arrReturn = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrReturn["COUNT(*)"];
    }

	/**
	 * Returns the element using the given element-name
	 *
	 * @param string $strName
	 * @return class_module_pages_element
	 */
	public static function getElement($strName) {
		$strQuery = "SELECT element_id FROM "._dbprefix_."element WHERE element_name=?";
		$arrId = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
		if(isset($arrId["element_id"]))
            return new class_module_pages_element($arrId["element_id"]);
        else
            return null;
	}

	/**
	 * Deletes one element
     * Overwrites the base-method, since there's no entry in the system-table
	 *
	 * @return bool
	 */
	public function deleteObject() {
	    $strQuery = "DELETE FROM "._dbprefix_."element WHERE element_id=?";
	    return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
	}


    /**
     * Factory method, creates an instance of the admin-element represented by this page-element.
     * The admin-element won't get initialized by a systemid, so you shouldn't retrieve
     * it for further usings.
     *
     * @return object An instance of the admin-class linked by the current element
     */
    public function getAdminElementInstance() {
        //Build the class-name
        $strElementClass = str_replace(".php", "", $this->getStrClassAdmin());
        //and finally create the object
        if(class_exists($strElementClass)) {
            $objElement = new $strElementClass();
            return $objElement;
        }
        else {
            throw new class_exception("element class ".$strElementClass." not existing", class_exception::$level_FATALERROR);
        }
    }


    /**
     * @fieldMandatory
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @return string
     * @fieldType dropdown
     */
    public function getStrClassPortal() {
        return $this->strClassPortal;
    }

    /**
     * @return string
     * @fieldType dropdown
     */
    public function getStrClassAdmin() {
        return $this->strClassAdmin;
    }

    /**
     * @return int
     * @fieldType yesno
     */
    public function getIntRepeat() {
        return (int)$this->intRepeat;
    }

    /**
     * @fieldMandatory
     * @fieldValidator numeric
     * @return string
     */
    public function getIntCachetime() {
        return $this->intCachetime;
    }

    /**
     * Returns a readable representation of the current elements' name.
     * Searches the lang-file for an entry element_NAME_name.
     *
     * @return string
     * @deprecated use getStrDisplayName()
     *
     * @fixme remove me
     */
    public function getStrReadableName() {
        $strName = class_carrier::getInstance()->getObjLang()->getLang("element_".$this->getStrName()."_name", "elemente");
        if($strName == "!element_".$this->getStrName()."_name!")
            $strName = $this->getStrName();
        return $strName;
    }


    public function setStrName($strName) {
        $this->strName = $strName;
    }
    public function setStrClassPortal($strClassPortal) {
        $this->strClassPortal = $strClassPortal;
    }
    public function setStrClassAdmin($strClassAdmin) {
        $this->strClassAdmin = $strClassAdmin;
    }
    public function setIntRepeat($intRepeat) {
        $this->intRepeat = $intRepeat;
    }
    public function setIntCachetime($intCachetime) {
        $this->intCachetime = $intCachetime;
    }

    public function getStrVersion() {
        return $this->strVersion;
    }

    public function setStrVersion($strVersion) {
        $this->strVersion = $strVersion;
    }


}
