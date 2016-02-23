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
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "stats";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "browscapupdate";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_browscapupdate_name");
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getAdminForm()
    {
        return "";
    }

}
