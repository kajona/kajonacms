<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Systemtasks;

use Kajona\Stats\System\StatsWorker;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\SystemModule;


/**
 * Resets erroneous hostnames
 *
 * @package module_stats
 */
class SystemtaskStatsHostnamelookupreset extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * contructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setStrTextBase("stats");
    }

    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier()
    {
        return "stats";
    }

    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName()
    {
        return "statshostnamelookupreset";
    }

    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_hostnamelookupreset_name");
    }

    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("stats")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $strReturn = "";
        $objWorker = new StatsWorker("");
        $objWorker->hostnameLookupResetHostnames();

        $strReturn .= $this->objToolkit->getTextRow($this->getLang("worker_lookupReset_end"));

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtast::getAdminForm()
     * @return string
     */
    public function getAdminForm()
    {
        return "";
    }

}
