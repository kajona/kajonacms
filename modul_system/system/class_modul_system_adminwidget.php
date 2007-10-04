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
*	$Id: class_rights.php 1565 2007-06-14 09:54:52Z sidler $	                                        *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Class to represent a single adminwidget
 * 
 * @package modul_system
 */
class class_modul_system_adminwidget extends class_model implements interface_model {
    
	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;

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
}


?>
