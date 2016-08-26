<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flash\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\SystemModule;

/**
 * Installer to install a flash-element to use in the portal
 *
 * @author jschroeter@kajona.de
 * @moduleId _flash_module_id_
 */
class InstallerFlash extends InstallerBase implements InstallerRemovableInterface
{

    public function install()
    {

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _flash_module_id_, "", "", $this->objMetadata->getStrVersion(), false);


        $strReturn = "";
        //Register the element
        $strReturn .= "Registering flash-element...\n";
        $objElement = PagesElement::getElement($this->objMetadata->getStrTitle());
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName($this->objMetadata->getStrTitle());
            $objElement->setStrClassAdmin("ElementFlashAdmin.php");
            $objElement->setStrClassPortal("ElementFlashPortal.php");
            $objElement->setIntCachetime(3600);
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

    /**
     * @return string
     */
    public function update()
    {
        $strReturn = "";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "4.7") {
            $strReturn .= "Updating 4.7 to 5.0...\n";
            $this->updateElementAndModule("5.0");
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating 5.1...\n";
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


    /**
     * @inheritdoc
     */
    public function remove(&$strReturn)
    {
        return $this->removeModuleAndElement($strReturn);
    }


}
