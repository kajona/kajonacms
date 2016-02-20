<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\Ldap\Admin\Systemtasks;

use Kajona\Ldap\System\Usersources\UsersourcesSourceLdap;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\SystemModule;


/**
 * Syncs the ldap-userbase with the data from the directory
 *
 * @package module_ldap
 * @author sidler@mulchprod.de
 */
class SystemtaskLdapsync extends SystemtaskBase implements AdminSystemtaskInterface
{

    /**
     * contructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setStrTextBase("ldap");
    }

    /**
     * @see AdminSystemtaskInterface::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier()
    {
        return "ldap";
    }

    /**
     * @see AdminSystemtaskInterface::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName()
    {
        return "ldapsync";
    }

    /**
     * @see AdminSystemtaskInterface::getStrTaskName()
     * @return string
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_ldapsync_name");
    }

    /**
     * @see AdminSystemtaskInterface::executeTask()
     * @return string
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("ldap")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $objUsersources = new UsersourcesSourceLdap();
        $bitSync = $objUsersources->updateUserData();


        if ($bitSync) {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_ldapsync_success"));
        } else {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_ldapsync_error"));
        }
    }


}
