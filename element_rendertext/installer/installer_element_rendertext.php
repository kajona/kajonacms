<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_element_rendertext.php																	*
* 	Installer of the form element																		*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_element_formular.php 1562 2007-06-12 20:49:14Z rsr $                                 *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");
include_once(_systempath_."/class_modul_pages_element.php");


/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @package modul_pages
 */
class class_installer_element_rendertext extends class_installer_base implements interface_installer {

    /**
     * Constructor
     *
     */
	public function __construct() {
		$arrModule["version"]       = "3.1.0";
		$arrModule["name"] 			= "element_rendertext";
		$arrModule["name_lang"] 	= "Element Render Text";
		$arrModule["nummer2"] 		= _pages_inhalte_modul_id_;
		$arrModule["tabellen"][]    = _dbprefix_."element_rendertext";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
        return "3.0.9";
    }

    public function hasPostInstalls() {
        //needed: pages
        try {
            $objModule = class_modul_system_module::getModuleByName("pages");
        }
        catch (class_exception $objE) {
            return false;
        }

        //check, if not already existing
        try {
            $objElement = class_modul_pages_element::getElement("rendertext");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null)
            return true;

        return false;
    }

    public function install() {
    }

    public function postInstall() {
    	$strReturn = "";

        //Register the element
        $strReturn .= "Registering rendertext-element...\n";
        //check, if not already existing
        try {
            $objElement = class_modul_pages_element::getElement("rendertext");
        }
        catch (class_exception $objEx)  {
        }
        if($objElement == null) {
        	
        	$strReturn .= "Installing rendertext-element-table...\n";
        	
        	$arrFields = array();
            $arrFields["content_id"]                    = array("char20", false);
            $arrFields["rendertext_text"]               = array("char254", true);
            $arrFields["rendertext_title"]              = array("char254", true);
            $arrFields["rendertext_width"]              = array("int", true);
            $arrFields["rendertext_height"]             = array("int", true);
            $arrFields["rendertext_font_family"]        = array("char254", true);
            $arrFields["rendertext_font_color"]         = array("char254", true);
            $arrFields["rendertext_font_size"]          = array("char254", true);
            $arrFields["rendertext_background_color"]   = array("char254", true);
            $arrFields["rendertext_transparency"]       = array("char254", true);
            $arrFields["rendertext_mode"]               = array("char254", true);
        
            if(!$this->objDB->createTable("element_rendertext", $arrFields, array("content_id")))
                $strReturn .= "An error occured! ...\n";
        	
        	
            $objElement = new class_modul_pages_element();
            $objElement->setStrName("rendertext");
            $objElement->setStrClassAdmin("class_element_rendertext.php");
            $objElement->setStrClassPortal("class_element_rendertext.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->saveObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }
        return $strReturn;
	}


	public function update() {
	}
}
?>