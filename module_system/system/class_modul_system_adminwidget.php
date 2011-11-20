<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Class to represent a single adminwidget
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_modul_system_adminwidget extends class_model implements interface_model {

    private $strClass = "";
    private $strContent = "";


	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "module_system";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]              = _dbprefix_."adminwidget";
		$arrModul["modul"]              = "system";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."adminwidget" => "adminwidget_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "adminwidget ".$this->getStrClass();
    }

    /**
     * Inits the object by loading the values from the db
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
        						   "._dbprefix_."system
        				WHERE system_id = adminwidget_id
        				  AND system_id = ? ";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        if(count($arrRow) > 0) {
            $this->setStrClass($arrRow["adminwidget_class"]);
            $this->setStrContent($arrRow["adminwidget_content"]);
        }
    }

    /**
     * Updates the values of the current widget to the db
     * @todo: was dbsafeString($this->getStrContent(), false) / false still required?
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                   SET adminwidget_class = ?,
                       adminwidget_content = ?
                 WHERE adminwidget_id = ? ";
        return $this->objDB->_pQuery($strQuery, array($this->getStrClass(), $this->getStrContent(), $this->getSystemid()), array(true, false, true) );
    }

    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    public function deleteObjectFromDb() {
        class_logger::getInstance()->addLogRow("deleted adminwidget ".$this->getSystemid(), class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    $strQuery = "DELETE FROM ".$this->arrModule["table"]."
                             WHERE adminwidget_id = ?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($objRoot->deleteSystemRecord($this->getSystemid()))
                return true;
        }
        return false;
    }



    /**
     * Looks up all widgets available in the filesystem.
     * ATTENTION: returns the class-name representation of a file, NOT the filename itself.
     *
     * @return array
     */
    public function getListOfWidgetsAvailable() {
        $arrReturn = array();

        $objFilesystem = new class_filesystem();

        $arrFiles = $objFilesystem->getFilelist("/admin/widgets/", array(".php"));

        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "interface_adminwidget.php" && $strOneFile != "class_adminwidget.php") {
                $arrReturn[] = uniStrReplace(".php", "", $strOneFile);
            }
        }

        return $arrReturn;
    }

    /**
     * Creates the concrete widget represented by this model-element
     *
     * @return class_adminwidget
     */
    public function getConcreteAdminwidget() {
        $objWidget = new $this->strClass();
        //Pass the field-values
        $objWidget->setFieldsAsString($this->getStrContent());
        $objWidget->setSystemid($this->getSystemid());
        return $objWidget;
    }

//--- GETTERS / SETTERS ---------------------------------------------------------------------------------

    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }
    public function setStrContent($strContent) {
        $this->strContent = $strContent;
    }

    public function getStrClass() {
        return $this->strClass;
    }
    public function getStrContent() {
        return $this->strContent;
    }

}


?>