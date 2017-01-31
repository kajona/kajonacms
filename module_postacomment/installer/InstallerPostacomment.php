<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\Postacomment\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Postacomment\System\PostacommentPost;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Filesystem;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;


/**
 * Class providing an install for the postacomment module
 *
 * @package module_postacomment
 * @moduleId _postacomment_modul_id_
 */
class InstallerPostacomment extends InstallerBase implements InstallerRemovableInterface {

    public function install() {
		$strReturn = "";
        $objManager = new OrmSchemamanager();
		$strReturn .= "Installing table postacomment...\n";
        $objManager->createTable(PostacommentPost::class);

		//register the module
		$strSystemID = $this->registerModule(
            "postacomment",
		    _postacomment_modul_id_,
		    "PostacommentPortal.php",
		    "PostacommentAdmin.php",
            $this->objMetadata->getStrVersion()
        );

		//modify default rights to allow guests to post
		$strReturn .= "Modifying modules' rights node...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $strSystemID, "right1");
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $strSystemID, "right2");


        $strReturn .= "Registering postacomment-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("postacomment");
        if($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("postacomment");
            $objElement->setStrClassAdmin("ElementPostacommentAdmin.php");
            $objElement->setStrClassPortal("ElementPostacommentPortal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
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

        $strReturn .= "Creating moderated setting...\n";
        $this->registerConstant("_postacomment_post_moderated_", "false", SystemSetting::$int_TYPE_BOOL, _postacomment_modul_id_);

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
        $objElement = PagesElement::getElement("postacomment");
        if($objElement != null) {
            $strReturn .= "Deleting page-element 'postacomment'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'postacomment', aborting.\n";
            return false;
        }

        /** @var PostacommentPost $objOneObject */
        foreach(PostacommentPost::getObjectListFiltered() as $objOneObject) {
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
        foreach(array("postacomment") as $strOneTable) {
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
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating to 4.7.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.1");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "4.7.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.1") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0") {
            $strReturn = "Updating to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "5.1");
        }
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1") {
            $strReturn .= $this->update_51_to_511();
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.1") {
            $strReturn = "Updating to 6.2...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2");
            $this->updateElementVersion($this->objMetadata->getStrTitle(), "6.2");
        }

        return $strReturn."\n\n";
	}


    private function update_51_to_511()
    {
        $strReturn = "Updating to 5.1.1...\n";
        $strReturn .= "Creating moderated setting...\n";
        $this->registerConstant("_postacomment_post_moderated_", "false", SystemSetting::$int_TYPE_BOOL, _postacomment_modul_id_);

        if(in_array(_dbprefix_."guestbook_post", $this->objDB->getTables())) {

            //find all guestbook elements and the matching books in order to duplicate the entries
            foreach ($this->objDB->getPArray("SELECT * FROM "._dbprefix_."element_guestbook, "._dbprefix_."system, "._dbprefix_."page_element WHERE content_id = page_element_id AND content_id = system_id", array()) as $arrOneElement) {
                $strPostParentId = $arrOneElement["guestbook_id"];
                $strTargetLang = $arrOneElement["page_element_ph_language"];
                $strTargetPageId = null;

                $strSysQuery = "SELECT * FROM "._dbprefix_."system WHERE system_id = ?";
                $arrSysRecord = $this->objDB->getPRow($strSysQuery, array($arrOneElement["system_prev_id"]));
                while ($arrSysRecord["system_id"] != "0") {
                    if ($arrSysRecord["system_class"] == PagesPage::class) {
                        $strTargetPageId = $arrSysRecord["system_id"];
                        break;
                    }

                    $arrSysRecord = $this->objDB->getPRow($strSysQuery, array($arrSysRecord["system_prev_id"]));
                }


                if (validateSystemid($strTargetPageId) && validateSystemid($strPostParentId)) {
                    //all set up, lets start to copy the records
                    foreach ($this->objDB->getPArray("SELECT * FROM "._dbprefix_."system, "._dbprefix_."guestbook_post WHERE system_id = guestbook_post_id AND system_prev_id = ?", array($strPostParentId)) as $arrOnePost) {
                        $strReturn .= "Moving guestbook entry ".$arrOnePost["system_id"]." to pac...\n";

                        $objPostacomment = new PostacommentPost();
                        $objPostacomment->setStrTitle(trim($arrOnePost["guestbook_post_name"]." ".$arrOnePost["guestbook_post_page"]));
                        $objPostacomment->setStrComment($arrOnePost["guestbook_post_text"]);
                        $objPostacomment->setStrUsername($arrOnePost["guestbook_post_name"]);
                        $objPostacomment->setIntDate($arrOnePost["guestbook_post_date"]);
                        $objPostacomment->setStrAssignedPage($strTargetPageId);
                        $objPostacomment->setStrAssignedLanguage($strTargetLang);

                        $objPostacomment->updateObjectToDb();
                    }
                }
            }

            $strReturn .= "Shifting page-elements...\n";
            foreach ($this->objDB->getPArray("SELECT * FROM "._dbprefix_."element_guestbook", array()) as $arrOneRow) {
                $this->objDB->_pQuery("INSERT INTO "._dbprefix_."element_universal (content_id, char1) VALUES (?, ?)", array($arrOneRow["content_id"], 'postacomment_ajax.tpl'));
            }

            $strReturn .= "Updating element classes...\n";
            $objElement = PagesElement::getElement("guestbook");
            $objElement->setStrClassAdmin("ElementPostacommentAdmin.php");
            $objElement->setStrClassPortal("ElementPostacommentPortal.php");
            $objElement->updateObjectToDb();

            $strReturn .= "Removing entries...\n";
            $objCommons = new SystemCommon();
            foreach ($this->objDB->getPArray("SELECT * FROM "._dbprefix_."guestbook_post", array()) as $arrOneRow) {
                $objCommons->deleteSystemRecord($arrOneRow["guestbook_post_id"]);
            }

            foreach ($this->objDB->getPArray("SELECT * FROM "._dbprefix_."guestbook_book", array()) as $arrOneRow) {
                $objCommons->deleteSystemRecord($arrOneRow["guestbook_id"]);
            }

            $this->objDB->_pQuery("DROP TABLE "._dbprefix_."guestbook_post", array());
            $this->objDB->_pQuery("DROP TABLE "._dbprefix_."guestbook_book", array());
            $this->objDB->_pQuery("DROP TABLE "._dbprefix_."element_guestbook", array());

            $objModule = SystemModule::getModuleByName("guestbook");
            $objModule->deleteObjectFromDatabase();

            $strReturn .= "Cleaning filesystem...\n";
            $objFilesystem = new Filesystem();
            if(is_file(_realpath_."/core/module_guestbook.phar")) {
                $objFilesystem->fileDelete("/core/module_guestbook.phar");
            } elseif (is_dir(_realpath_."/core/module_guestbook")) {
                $objFilesystem->folderDeleteRecursive("/core/module_guestbook");
            }
            Classloader::getInstance()->flushCache();
        }

        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1.1");
        $this->updateElementVersion($this->objMetadata->getStrTitle(), "5.1.1");

        return $strReturn;
	}


}
