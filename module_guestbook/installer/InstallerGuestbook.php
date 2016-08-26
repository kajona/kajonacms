<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Installer;

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;


/**
 * Installer of the guestbook module
 *
 * @package module_guestbook
 * @moduleId _guestbook_module_id_
 */
class InstallerGuestbook extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {

        $strReturn = "";
        $objManager = new OrmSchemamanager();

        $strReturn .= "Installing table guestbook_book...\n";
        $objManager->createTable("Kajona\\Guestbook\\System\\GuestbookGuestbook");

        $strReturn .= "Installing table guestbook_post...\n";
        $objManager->createTable("Kajona\\Guestbook\\System\\GuestbookPost");

        //register the module
        $this->registerModule("guestbook", _guestbook_module_id_, "GuestbookPortal.php", "GuestbookAdmin.php", $this->objMetadata->getStrVersion(), true);

        //Table for page-element
        $strReturn .= "Installing guestbook-element table...\n";
        $objManager->createTable("Kajona\\Guestbook\\Admin\\Elements\\ElementGuestbookAdmin");

        //Register the element
        $strReturn .= "Registering guestbook-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("guestbook");
        if ($objElement === null) {
            $objElement = new PagesElement();
            $objElement->setStrName("guestbook");
            $objElement->setStrClassAdmin("ElementGuestbookAdmin.php");
            $objElement->setStrClassPortal("ElementGuestbookPortal.php");
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
        if (SystemAspect::getAspectByName("content") != null) {
            $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle());
            $objModule->setStrAspect(SystemAspect::getAspectByName("content")->getSystemid());
            $objModule->updateObjectToDb();
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
    public function isRemovable()
    {
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
    public function remove(&$strReturn)
    {

        //delete the page-element
        $objElement = PagesElement::getElement("guestbook");
        if ($objElement != null) {
            $strReturn .= "Deleting page-element 'guestbook'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'guestbook', aborting.\n";
            return false;
        }

        /** @var GuestbookGuestbook $objOneObject */
        foreach (GuestbookGuestbook::getObjectListFiltered() as $objOneObject) {
            $strReturn .= "Deleting object '".$objOneObject->getStrDisplayName()."' ...\n";
            if (!$objOneObject->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting object, aborting.\n";
                return false;
            }
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if (!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        //delete the tables
        foreach (array("guestbook_post", "guestbook_book", "element_guestbook") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }

        }

        return true;
    }


    public function update()
    {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.6") {
            $strReturn = "Updating to 4.7...\n";
            $this->updateModuleVersion("guestbook", "4.7");
            $this->updateElementVersion("guestbook", "4.7");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7") {
            $strReturn = "Updating to 5.0...\n";
            $this->updateModuleVersion("guestbook", "5.0");
            $this->updateElementVersion("guestbook", "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.0") {
            $strReturn = "Updating to 5.1...\n";
            $this->updateModuleVersion("guestbook", "5.1");
            $this->updateElementVersion("guestbook", "5.1");
        }

        return $strReturn."\n\n";
    }


}
