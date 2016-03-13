<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Installer;

use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\Pages\System\PagesElement;

/**
 * Installer of the pages-module
 *
 * @package module_pages
 * @moduleId _pages_modul_id_
 */
class InstallerPages extends InstallerBase implements InstallerInterface {

	public function install() {

		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";
        $objManager = new OrmSchemamanager();

		$strReturn .= "Installing table pages...\n";
        $objManager->createTable("Kajona\\Pages\\System\\PagesPage");

		$strReturn .= "Installing table page_folder...\n";
        $objManager->createTable("Kajona\\Pages\\System\\PagesFolder");

        //folder_properties
        $strReturn .= "Installing table page_properties...\n";

		$arrFields = array();
		$arrFields["pageproperties_id"] 		= array("char20", false);
		$arrFields["pageproperties_browsername"]= array("char254", true);
		$arrFields["pageproperties_keywords"] 	= array("char254", true);
		$arrFields["pageproperties_description"]= array("char254", true);
		$arrFields["pageproperties_template"] 	= array("char254", true);
		$arrFields["pageproperties_seostring"] 	= array("char254", true);
		$arrFields["pageproperties_language"] 	= array("char20", true);
		$arrFields["pageproperties_alias"] 	    = array("char254", true);
        $arrFields["pageproperties_path"] 	    = array("char254", true);
        $arrFields["pageproperties_target"] 	= array("char254", true);

		if(!$this->objDB->createTable("page_properties", $arrFields, array("pageproperties_id", "pageproperties_language"), array("pageproperties_language")))
			$strReturn .= "An error occurred! ...\n";

		$strReturn .= "Installing table element...\n";
        $objManager->createTable("Kajona\\Pages\\System\\PagesElement");

		$strReturn .= "Installing table page_element...\n";
        $objManager->createTable("Kajona\\Pages\\System\\PagesPageelement");


		//Now we have to register module by module

		//the pages
		$this->registerModule("pages", _pages_modul_id_, "PagesPortalController.php", "PagesAdminController.php", $this->objMetadata->getStrVersion(), true);
		//The pages_content
		$this->registerModule("pages_content", _pages_content_modul_id_, "", "PagesContentAdmin.php", $this->objMetadata->getStrVersion(), false);


		$strReturn .= "Registering system-constants...\n";
		$this->registerConstant("_pages_templatechange_", "false", SystemSetting::$int_TYPE_BOOL, _pages_modul_id_);
		$this->registerConstant("_pages_indexpage_", "index", SystemSetting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_errorpage_", "error", SystemSetting::$int_TYPE_PAGE, _pages_modul_id_);
		$this->registerConstant("_pages_defaulttemplate_", "standard.tpl", SystemSetting::$int_TYPE_STRING, _pages_modul_id_);
		//2.1.1: overall cachetime
		$this->registerConstant("_pages_cacheenabled_", "true", SystemSetting::$int_TYPE_BOOL, _pages_modul_id_);
		//2.1.1: possibility, to create new pages disabled
		$this->registerConstant("_pages_newdisabled_", "false", SystemSetting::$int_TYPE_BOOL, _pages_modul_id_);
		//portaleditor
        $this->registerConstant("_pages_portaleditor_", "true", SystemSetting::$int_TYPE_BOOL, _pages_modul_id_);

        $strReturn .= "Shifting pages to third position...\n";
        $objModule = SystemModule::getModuleByName("pages");
        $objModule->setAbsolutePosition(3);



        $strReturn .= "Installing universal element table...\n";

        $arrFields = array();
        $arrFields["content_id"]= array("char20", false);
        $arrFields["char1"]		= array("char254", true);
        $arrFields["char2"] 	= array("char254", true);
        $arrFields["char3"]		= array("char254", true);
        $arrFields["int1"]		= array("int", true);
        $arrFields["int2"]		= array("int", true);
        $arrFields["int3"]		= array("int", true);
        $arrFields["text"]		= array("text", true);

        if(!$this->objDB->createTable("element_universal", $arrFields, array("content_id")))
            $strReturn .= "An error occurred! ...\n";

        //Table for paragraphes
        $strReturn .= "Installing paragraph table...\n";
        $objManager->createTable("Kajona\\Pages\\Admin\\Elements\\ElementParagraphAdmin");

        //Table for page-element
        $strReturn .= "Installing gallery-element table...\n";
        $objManager->createTable("Kajona\\Mediamanager\\Admin\\Elements\\ElementGalleryAdmin");

        //Table for page-element
        $strReturn .= "Installing downloads-element table...\n";
        $objManager->createTable("Kajona\\Mediamanager\\Admin\\Elements\\ElementDownloadsAdmin");

        //Table for images
        $strReturn .= "Installing image table...\n";
        $objManager->createTable("Kajona\\Pages\\Admin\\Elements\\ElementImageAdmin");


        $arrElements = array(
//            "row" => array("ElementRowAdmin.php", "ElementRowPortal.php"),
//            "paragraph" => array("ElementParagraphAdmin.php", "ElementParagraphPortal.php"),
            "image" => array("ElementImageAdmin.php", "ElementImagePortal.php"),
            "downloads" => array("ElementDownloadsAdmin.php", "ElementDownloadsPortal.php"),
            "gallery" => array("ElementGalleryAdmin.php", "ElementGalleryPortal.php"),
            "galleryRandom" => array("ElementGalleryRandomAdmin.php", "ElementGalleryPortal.php"),
            "blocks" => array("ElementBlocksAdmin.php", "ElementBlocksPortal.php"),
            "block" => array("ElementBlockAdmin.php", "ElementBlockPortal.php"),
            "date" => array("ElementDateAdmin.php", "ElementDatePortal.php"),
            "plaintext" => array("ElementPlaintextAdmin.php", "ElementPlaintextPortal.php"),
            "richtext" => array("ElementRichtextAdmin.php", "ElementRichtextPortal.php"),
        );

        foreach($arrElements as $strOneElement => $arrConfig) {

            //Register the element
            $strReturn .= "Registering element ".$strOneElement."...\n";
            //check, if not already existing
            $objElement = PagesElement::getElement($strOneElement);
            if ($objElement == null) {
                $objElement = new PagesElement();
                $objElement->setStrName($strOneElement);
                $objElement->setStrClassAdmin($arrConfig[0]);
                $objElement->setStrClassPortal($arrConfig[1]);
                $objElement->setIntCachetime(3600);
                $objElement->setIntRepeat(1);
                $objElement->setStrVersion($this->objMetadata->getStrVersion());
                $objElement->updateObjectToDb();
                $strReturn .= "Element registered...\n";
            }
            else {
                $strReturn .= "Element already installed!...\n";
            }

        }




        $strReturn .= "Setting aspect assignments...\n";
        if(SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

		return $strReturn;

	}


	protected function updateModuleVersion($strModuleName, $strVersion) {
		parent::updateModuleVersion("pages", $strVersion);
        parent::updateModuleVersion("pages_content", $strVersion);
	}


	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating 4.6 to 4.6.1...\n";
            $this->updateModuleVersion("", "4.6.1");
            $this->updateElementVersion("row", "4.6.1");
            $this->updateElementVersion("paragraph", "4.6.1");
            $this->updateElementVersion("image", "4.6.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.1") {
            $strReturn = "Updating 4.6.1 to 4.6.2...\n";
            $this->updateModuleVersion("", "4.6.2");
            $this->updateElementVersion("row", "4.6.2");
            $this->updateElementVersion("paragraph", "4.6.2");
            $this->updateElementVersion("image", "4.6.2");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.2") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion("", "4.7");
            $this->updateElementVersion("row", "4.7");
            $this->updateElementVersion("paragraph", "4.7");
            $this->updateElementVersion("image", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn = "Updating to 4.7.1...\n";
            $this->updateModuleVersion("", "4.7.1");
            $this->updateElementVersion("row", "4.7.1");
            $this->updateElementVersion("paragraph", "4.7.1");
            $this->updateElementVersion("image", "4.7.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.1") {
            $strReturn = "Updating to 4.7.2...\n";
            $this->updateModuleVersion("", "4.7.2");
            $this->updateElementVersion("row", "4.7.2");
            $this->updateElementVersion("paragraph", "4.7.2");
            $this->updateElementVersion("image", "4.7.2");
            $this->updateElementVersion("gallery", "4.7.2");
            $this->updateElementVersion("galleryRandom", "4.7.2");
            $this->updateElementVersion("downloads", "4.7.2");
        }

        return $strReturn."\n\n";
	}



}
