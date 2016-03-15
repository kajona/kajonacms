<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Directorybrowser\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\SystemModule;

/**
 * Installer to install a directorybrowser-element to use in the portal
 *
 * @moduleId _directorybrowser_module_id_
 */
class InstallerDirectorybrowser extends InstallerBase implements InstallerRemovableInterface
{

    /**
     * @inheritdoc
     */
    public function install()
    {
        $strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _directorybrowser_module_id_, "", "", $this->objMetadata->getStrVersion(), false);

        //Register the element
        $strReturn .= "Registering directorybrowser-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement($this->objMetadata->getStrTitle());
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName($this->objMetadata->getStrTitle());
            $objElement->setStrClassAdmin("ElementDirectorybrowserAdmin.php");
            $objElement->setStrClassPortal("ElementDirectorybrowserPortal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->objMetadata->getStrVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";

            if ($objElement->getStrVersion() < 2) {
                $strReturn .= "Updating element version!...\n";
                $objElement->setStrVersion("2.0");
                $objElement->updateObjectToDb();
            }
        }
        return $strReturn;
    }


    public function update()
    {
        $strReturn = "";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "2.0") {
            $strReturn .= "Updating 2.0 to 2.1...\n";
            $this->updateElementAndModule("2.1");
        }

        return $strReturn."\n\n";
    }


    /**
     * @inheritdoc
     */
    public function isRemovable()
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function remove(&$strReturn)
    {
        return $this->removeModuleAndElement($strReturn);
    }


}
