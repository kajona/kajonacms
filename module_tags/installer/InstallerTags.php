<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\Tags\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\Tags\System\TagsFavorite;
use Kajona\Tags\System\TagsTag;

/**
 * Class providing an install for the tags module
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @moduleId _tags_modul_id_
 */
class InstallerTags extends InstallerBase implements InstallerRemovableInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();

		//tags_tag --------------------------------------------------------------------------------------
		$strReturn .= "Installing table tags_tag...\n";
        $objManager->createTable("Kajona\\Tags\\System\\TagsTag");

		$strReturn .= "Installing table tags_member...\n";
        $arrFields = array();
		$arrFields["tags_memberid"]     = array("char20", false);
		$arrFields["tags_systemid"] 	= array("char20", false);
		$arrFields["tags_tagid"]        = array("char20", false);
		$arrFields["tags_attribute"]    = array("char254", true);
		$arrFields["tags_owner"]        = array("char20", true);

		if(!$this->objDB->createTable("tags_member", $arrFields, array("tags_memberid"), array("tags_systemid", "tags_tagid", "tags_attribute", "tags_owner")))
			$strReturn .= "An error occurred! ...\n";

        $strReturn .= "Installing table tags_favorite...\n";
        $objManager->createTable("Kajona\\Tags\\System\\TagsFavorite");

		//register the module
		$this->registerModule(
            "tags",
            _tags_modul_id_,
            "",
            "TagsAdmin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "TagsAdminXml.php"
        );

		$strReturn .= "Registering system-constants...\n";
        $this->registerConstant("_tags_defaultprivate_", "false", SystemSetting::$int_TYPE_BOOL, _tags_modul_id_);

        //Register the element
        $strReturn .= "Registering tags-element...\n";

        //check, if not already existing
        if(SystemModule::getModuleByName("pages") !== null && PagesElement::getElement("tags") == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("tags");
            $objElement->setStrClassAdmin("ElementTagsAdmin.php");
            $objElement->setStrClassPortal("ElementTagsPortal.php");
            $objElement->setIntCachetime(3600*24*30);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }

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

        $strReturn .= "Removing settings...\n";
        if(SystemSetting::getConfigByName("_tags_defaultprivate_") != null)
            SystemSetting::getConfigByName("_tags_defaultprivate_")->deleteObjectFromDatabase();

        //delete the page-element
        if(SystemModule::getModuleByName("pages") !== null && PagesElement::getElement("tags") != null) {
            $objElement = PagesElement::getElement("tags");
            if($objElement != null) {
                $strReturn .= "Deleting page-element 'tags'...\n";
                $objElement->deleteObjectFromDatabase();
            }
            else {
                $strReturn .= "Error finding page-element 'guestbook', tags.\n";
                return false;
            }
        }

        /** @var TagsFavorite $objOneObject */
        foreach(TagsFavorite::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if(!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        /** @var TagsTag $objOneObject */
        foreach(TagsTag::getObjectListFiltered() as $objOneObject) {
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
        foreach(array("tags_tag", "tags_member", "tags_favorite") as $strOneTable) {
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
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.7");
            $this->objDB->flushQueryCache();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "5.0");
            $this->objDB->flushQueryCache();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "5.1");
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}


}
