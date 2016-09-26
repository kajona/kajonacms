<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

namespace Kajona\Statustransition\Installer;

use Kajona\News\System\NewsCategory;
use Kajona\News\System\NewsFeed;
use Kajona\News\System\NewsNews;
use Kajona\Pages\System\PagesElement;
use Kajona\Statustransition\System\StatustransitionFlow;
use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\System\System\Carrier;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Class providing an install for the news module
 *
 * @package module_statustransition
 * @moduleId _statustransition_module_id_
 */
class InstallerStatustransition extends InstallerBase
{
    public function install()
    {
        $strReturn = "";
        $objManager = new OrmSchemamanager();

        $strReturn .= "Installing table flow ...\n";
        $objManager->createTable(StatustransitionFlow::class);

        $strReturn .= "Installing table flow_step...\n";
        $objManager->createTable(StatustransitionFlowStep::class);

        //register the module
        $this->registerModule(
            "statustransition",
            _statustransition_module_id_,
            "",
            "StatustransitionAdmin.php",
            $this->objMetadata->getStrVersion(),
            true,
            "",
            "StatustransitionAdminXml.php"
        );

        return $strReturn;
    }

    public function update()
    {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        return $strReturn;
    }
}
