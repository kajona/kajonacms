<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Formular\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemModule;

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @author sidler@mulchprod.de
 * @moduleId _formular_module_id_
 */
class InstallerFormular extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {
        $strReturn = "";

        //Table for page-element
        $strReturn .= "Installing formular-element table...\n";
        $objManager = new OrmSchemamanager();
        $objManager->createTable("Kajona\\Formular\\Admin\\Elements\\ElementFormularAdmin");


        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _formular_module_id_, "", "", $this->objMetadata->getStrVersion(), false);


        //Register the element
        $strReturn .= "Registering formular-element...\n";
        $objElement = PagesElement::getElement("form");
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("form");
            $objElement->setStrClassAdmin("ElementFormularAdmin.php");
            $objElement->setStrClassPortal("ElementFormularPortal.php");
            $objElement->setIntCachetime(-1);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";

            if ($objElement->getStrVersion() < 5) {
                $strReturn .= "Updating element version!...\n";
                $objElement->setStrVersion("5.0");
                $objElement->updateObjectToDb();
            }
        }
        return $strReturn;
    }

    public function remove(&$strReturn)
    {

        //delete the tables
        foreach (array("element_formular") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable), array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }
        }

        //delete the page-element
        $objElement = PagesElement::getElement("form");
        if ($objElement != null) {
            $strReturn .= "Deleting page-element 'form'...\n";
            $objElement->deleteObjectFromDatabase();
        }
        else {
            $strReturn .= "Error finding page-element 'form', aborting.\n";
            return false;
        }

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if (!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }

        return true;
    }


    /**
     * @return string
     */
    public function update()
    {
        $strReturn = "";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating 5.0 to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
            $this->updateElementVersion("form", "5.1");
        }

        return $strReturn;
    }

    /**
     * @inheritDoc
     */
    public function isRemovable()
    {
        return true;
    }
}
