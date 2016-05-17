<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_element_downloads_toplist.php 4161 2011-10-29 12:03:12Z sidler $                     *
********************************************************************************************************/

namespace Kajona\Languageredirect\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\SystemModule;


/**
 *
 * @author sidler@mulchprod.de
 *
 * @moduleId _pages_content_modul_id_
 */
class InstallerElementLanguageredirect extends InstallerBase implements InstallerInterface
{


    public function install() {
        //register the module
        $this->registerModule($this->objMetadata->getStrTitle(), _languageredirect_module_id_, "", "", $this->objMetadata->getStrVersion(), false);
        
        
        $strReturn = "";
        //Register the element
        $strReturn .= "Registering languageredirect-element...\n";
        //check, if not already existing
        $objElement = null;
        $objElement = PagesElement::getElement("languageredirect");
        if($objElement == null) {
            $objElement = new PagesElement();
            $objElement->setStrName("languageredirect");
            $objElement->setStrClassAdmin('ElementLanguageredirectAdmin.php');
            $objElement->setStrClassPortal('ElementLanguageredirectPortal.php   ');
            $objElement->setIntCachetime(60);
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


    public function update() {
        $strReturn = "";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if ($arrModule["module_version"] == "5.0") {
            $strReturn .= "Updating 5.0 to 5.1...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1");
            $this->updateElementVersion("form", "5.1");
        }

        return $strReturn;
    }

}
