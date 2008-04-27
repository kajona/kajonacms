<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_system_adminwidget.php																	*
* 	Class to manage the various widgets available 														*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Class to represent a single adminwidget
 * 
 * @package modul_system
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
        $arrModul["name"] 				= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
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
     * Inits the object by loading the values from the db
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
        						   "._dbprefix_."system 
        				WHERE system_id = adminwidget_id
        				  AND system_id = '".dbsafeString($this->getSystemid())."'";
        
        $arrRow = $this->objDB->getRow($strQuery);
        if(count($arrRow) > 0) {
            $this->setStrClass($arrRow["adminwidget_class"]);
            $this->setStrContent($arrRow["adminwidget_content"]);
        }
    }
    
    /**
     * Updates the values of the current widget to the db
     *
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated adminwidget ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                   SET adminwidget_class = '".dbsafeString($this->getStrClass())."',
                       adminwidget_content = '".dbsafeString($this->getStrContent(), false)."'
                 WHERE adminwidget_id = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
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
                             WHERE adminwidget_id = '".dbsafeString($this->getSystemid())."'";
        if($this->objDB->_query($strQuery)) {
            if($objRoot->deleteSystemRecord($this->getSystemid()))
                return true;
        }
        return false;
    }
    
    /**
     * Saves an adminwidget to the database.
     *
     */
    public function saveObjectToDb() {
        
        $this->objDB->transactionBegin();

        $strWidgetId = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "widget: ".$this->strClass);
        $this->setSystemid($strWidgetId);
        
        class_logger::getInstance()->addLogRow("new widget ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (adminwidget_id, adminwidget_class, adminwidget_content) VALUES
                    ('".dbsafeString($strWidgetId)."', '".dbsafeString($this->getStrClass())."', '".dbsafeString($this->getStrContent(), false)."')";

        if($this->objDB->_query($strQuery)) {
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            $this->objDB->transactionRollback();
            return false;
        }
    }
    
    /**
     * Looks up all widgets available in the filesystem.
     * ATTENTION: returns the class-name representation of a file, NOT the filename itself.
     * includes all files to be able to work on the immediatelly
     *
     * @return array
     */
    public function getListOfWidgetsAvailable() {
        $arrReturn = array();
        
        include_once(_systempath_."/class_filesystem.php");
        $objFilesystem = new class_filesystem();
        
        $arrFiles = $objFilesystem->getFilelist("/admin/widgets/", array(".php"));
        
        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "interface_adminwidget.php" && $strOneFile != "class_adminwidget.php") {
                $arrReturn[] = uniStrReplace(".php", "", $strOneFile);
                include_once(_adminpath_."/widgets/".$strOneFile);
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
        include_once(_adminpath_."/widgets/".$this->getStrClass().".php");
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