<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Maps\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\SystemModule;

/**
 * Installer to install a form-element (provides a basic contact form)
 *
 * @author jschroeter@kajona.de
 * @moduleId _maps_module_id_
 */
class InstallerMaps extends InstallerBase implements InstallerInterface
{

    public function install()
    {
        $strReturn = "";

        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _maps_module_id_, "", "", $this->objMetadata->getStrVersion(), false);

        //Register the element
        $strReturn .= "Registering maps-element...\n";
        //check, if not already existing
        $objElement = PagesElement::getElement("maps");
        if ($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("maps");
            $objElement->setStrClassAdmin("ElementMapsAdmin.php");
            $objElement->setStrClassPortal("ElementMapsPortal.php");
            $objElement->setIntCachetime(3600 * 24 * 30);
            $objElement->setIntRepeat(1);
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
        if ($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating 5.0 to 5.1...\n";
            $this->updateElementAndModule("5.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.1") {
            $strReturn .= "Updating 5.1 to 5.1.1...\n";
            $this->updateElementAndModule("5.1.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.1.1") {
            $strReturn .= "Updating 5.1.1 to 6.2...\n";
            $this->updateElementAndModule("6.2");
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
