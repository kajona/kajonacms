<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

namespace Kajona\News\Installer;

use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Class providing an install for the news module
 *
 * @package module_news
 * @moduleId _news_module_id_
 */
class InstallerNews extends InstallerBase implements InstallerRemovableInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();

		$strReturn .= "Installing table news_category...\n";
        $objManager->createTable("Kajona\\News\\System\\NewsCategory");

		$strReturn .= "Installing table news...\n";
        $objManager->createTable("Kajona\\News\\System\\NewsNews");

		$strReturn .= "Installing table news_feed...\n";
        $objManager->createTable("Kajona\\News\\System\\NewsFeed");

		//register the module
		$this->registerModule(
            "news",
            _news_module_id_,
            "NewsPortal.php",
            "NewsAdmin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "NewsPortalXml.php"
        );

        $strReturn .= "Installing news-element table...\n";
        $objManager->createTable("Kajona\\News\\Admin\\Elements\\ElementNewsAdmin");


        //Register the element
        $strReturn .= "Registering news-element...\n";
        //check, if not already existing
        if(PagesElement::getElement("news") == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("news");
            $objElement->setStrClassAdmin("ElementNewsAdmin.php");
            $objElement->setStrClassPortal("ElementNewsPortal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

        $strReturn .= "Setting aspect assignments...\n";
        if(SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
        }

        $strReturn .= "Registering config settings...\n";
        $this->registerConstant("_news_news_datetime_", "false", SystemSetting::$int_TYPE_BOOL, _news_module_id_);

		return $strReturn;

	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {

        //delete the page-element
        $objElement = PagesElement::getElement("news");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'news'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'news', aborting.\n";
            return false;
        }

        /** @var NewsFeed $objOneObject */
        foreach(NewsFeed::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var NewsCategory $objOneObject */
        foreach(NewsCategory::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var NewsNews $objOneObject */
        foreach(NewsNews::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach(array("news_category", "news", "news_member", "news_feed", "element_news") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if(!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("news", "4.7");
            $this->updateElementVersion("news", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7" || $arrModule["module_version"] == "4.7.1" || $arrModule["module_version"] == "4.7.2") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.5") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion("news", "5.0");
            $this->updateElementVersion("news", "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating to 5.0.1...\n";
            $this->updateModuleVersion("news", "5.0.1");
            $this->updateElementVersion("news", "5.0.1");
        }
        
        return $strReturn."\n\n";
	}
    

    private function update_47_475() {
        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Changing assignment table...\n";
        Carrier::getInstance()->getObjDB()->removeColumn("news_member", "newsmem_id");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("news", "4.7.5");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("news", "4.7.5");
        return $strReturn;
    }
}
