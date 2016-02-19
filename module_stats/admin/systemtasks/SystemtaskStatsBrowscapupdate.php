<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

namespace Kajona\Stats\Admin\Systemtasks;

use Kajona\Stats\System\Browscap;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\SystemModule;


/**
 *
 * @package module_stats
 */
class SystemtaskStatsBrowscapupdate extends SystemtaskBase implements AdminSystemtaskInterface
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
        return "browscapupdate";
    }

    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_browscapupdate_name");
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

        $objBrowscap = new Browscap();
        $objBrowscap->updateBrowscap();

        return $this->objToolkit->getTextRow($this->getLang("browscapupdate_end"));
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
