<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\V4skin\Installer;

use Kajona\Pages\System\PagesElement;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\Votings\System\VotingsVoting;

/**
 * Class providing an installer for the votings module
 *
 * @author sidler@mulchprod.de
 * @moduleId _v4skin_module_id_
 */
class InstallerSkin extends InstallerBase implements InstallerInterface
{

    public function install() {
        $strReturn = "Registering skin module...\n";
		$this->registerModule($this->objMetadata->getStrTitle(), _v4skin_module_id_, "", "SkinAdminController.php", $this->objMetadata->getStrVersion(), false);
		return $strReturn;
	}


    public function update() {
	    $strReturn = "";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "6.2") {
            $strReturn .= "Updating 6.2 to 6.3...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.3");
        }

        return $strReturn."\n\n";
	}
    


}
