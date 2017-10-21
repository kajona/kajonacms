<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

namespace Kajona\Faqs\Installer;

use Kajona\Faqs\System\FaqsCategory;
use Kajona\Faqs\System\FaqsFaq;
use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;

/**
 * Class providing an installer for the faqs module
 *
 * @package module_faqs
 * @moduleId _faqs_module_id_
 */
class InstallerFaqs extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {
        $strReturn = "";
        $objSchemamanager = new OrmSchemamanager();

        //faqs cat-------------------------------------------------------------------------------------
        $strReturn .= "Installing table faqs_category...\n";
        $objSchemamanager->createTable("Kajona\\Faqs\\System\\FaqsCategory");

        //faqs----------------------------------------------------------------------------------
        $strReturn .= "Installing table faqs...\n";
        $objSchemamanager->createTable("Kajona\\Faqs\\System\\FaqsFaq");


        //register the module
        $this->registerModule("faqs", _faqs_module_id_, "FaqsPortal.php", "FaqsAdmin.php", $this->objMetadata->getStrVersion(), true);

        //Table for page-element
        $strReturn .= "Installing faqs-element table...\n";

        $arrFields = array();
        $arrFields["content_id"] = array("char20", false);
        $arrFields["faqs_category"] = array("char20", true);
        $arrFields["faqs_template"] = array("char254", true);

        if (!$this->objDB->createTable("element_faqs", $arrFields, array("content_id"))) {
            $strReturn .= "An error occurred! ...\n";
        }

        //Register the element
        $strReturn .= "Registering faqs-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("faqs");
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("faqs");
            $objElement->setStrClassAdmin("ElementFaqsAdmin.php");
            $objElement->setStrClassPortal("ElementFaqsPortal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        } else {
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
        $objElement = PagesElement::getElement("faqs");
        if ($objElement != null) {
            $strReturn .= "Deleting page-element 'faqs'...\n";
            $objElement->deleteObjectFromDatabase();
        } else {
            $strReturn .= "Error finding page-element 'faqs', aborting.\n";
            return false;
        }

        //delete all faqs and categories
        /** @var FaqsCategory $objOneCategory */
        foreach (FaqsCategory::getObjectListFiltered() as $objOneCategory) {
            $strReturn .= "Deleting category '" . $objOneCategory->getStrDisplayName() . "' ...\n";
            if (!$objOneCategory->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting category, aborting.\n";
                return false;
            }
        }

        /** @var FaqsFaq $objOneFaq */
        foreach (FaqsFaq::getObjectListFiltered(null) as $objOneFaq) {
            $strReturn .= "Deleting faq '" . $objOneFaq->getStrDisplayName() . "' ...\n";
            if (!$objOneFaq->deleteObjectFromDatabase()) {
                $strReturn .= "Error deleting faq, aborting.\n";
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
        foreach (array("faqs_category", "faqs", "faqs_member", "element_faqs") as $strOneTable) {
            $strReturn .= "Dropping table " . $strOneTable . "...\n";
            if (!$this->objDB->_pQuery("DROP TABLE " . $this->objDB->encloseTableName(_dbprefix_ . $strOneTable) . "", array())) {
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

        $strReturn .= "Version found:\n\t Module: " . $arrModule["module_name"] . ", Version: " . $arrModule["module_version"] . "\n\n";


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("faqs", "4.7");
            $this->updateElementVersion("faqs", "4.7");
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7.5") {
            $strReturn .= "Updating to 5.0...\n";
            $this->updateModuleVersion("faqs", "5.0");
            $this->updateElementVersion("faqs", "5.0");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating to 5.1...\n";
            $this->updateModuleVersion("faqs", "5.1");
            $this->updateElementVersion("faqs", "5.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.1") {
            $strReturn .= "Updating to 6.2...\n";
            $this->updateModuleVersion("faqs", "6.2");
            $this->updateElementVersion("faqs", "6.2");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "6.2") {
            $strReturn .= "Updating to 6.2.1...\n";
            $this->updateModuleVersion("faqs", "6.2.1");
            $this->updateElementVersion("faqs", "6.2.1");
        }

        return $strReturn . "\n\n";
    }



    private function update_47_475()
    {
        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Changing assignment table...\n";
        Carrier::getInstance()->getObjDB()->removeColumn("faqs_member", "faqsmem_id");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "4.7.5");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("faqs", "4.7.5");
        return $strReturn;
    }


}
