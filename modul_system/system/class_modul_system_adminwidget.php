<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
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
    
    public function initObject() {
        
    }
    
    public function updateObjectToDb() {
        
    }
    
    public function deleteObjectFromDb() {
       //TODO: MUST BE IMPLEMENTED! CALLED BY VIEW
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
                    ('".dbsafeString($strWidgetId)."', '".dbsafeString($this->getStrClass())."', '".dbsafeString($this->getStrContent())."')";

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
