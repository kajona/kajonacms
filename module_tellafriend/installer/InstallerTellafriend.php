<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Tellafriend\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemModule;

/**
 * Installer to install a tellafriend-element to use in the portal
 *
 * @author sidler@mulchprod.de
 * @moduleId _tellafriend_module_id_
 */
class InstallerTellafriend extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {
        $strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _tellafriend_module_id_, "", "", $this->objMetadata->getStrVersion(), false);


        //Table for page-element
        $strReturn .= "Installing tellafriend-element table...\n";
        $objManager = new OrmSchemamanager();
        $objManager->createTable("Kajona\\Tellafriend\\Admin\\Elements\\ElementTellafriendAdmin");

        //Register the element
        $strReturn .= "Registering tellafriend-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("tellafriend");
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("tellafriend");
            $objElement->setStrClassAdmin("ElementTellafriendAdmin.php");
            $objElement->setStrClassPortal("ElementTellafriendPortal.php");
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
        foreach (array("element_tellafriend") as $strOneTable) {
            $strReturn .= "Dropping table ".$strOneTable."...\n";
            if (!$this->objDB->_pQuery("DROP TABLE ".$this->objDB->encloseTableName(_dbprefix_.$strOneTable)."", array())) {
                $strReturn .= "Error deleting table, aborting.\n";
                return false;
            }
        }

        return $this->removeModuleAndElement($strReturn);
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
            $this->updateElementAndModule("5.1");
        }

        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function isRemovable()
    {
        return true;
    }

}
