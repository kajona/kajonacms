<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\Carrier;
use Kajona\System\System\SystemModule;


/**
 * Dumps the database to the filesystem using the current db-driver
 *
 * @package module_system
 */
class SystemtaskDbdump extends SystemtaskBase implements AdminSystemtaskInterface
{

    private $arrTablesToExlucde = array(
        "stats_data", "stats_ip2country", "cache", "search_ix_document", "search_ix_content"
    );

    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "database";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "dbdump";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_dbexport_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        $arrToExclude = array();
        if ($this->getParam("excludeTables") == "1") {
            $arrToExclude = $this->arrTablesToExlucde;
        }

        if (Carrier::getInstance()->getObjDB()->dumpDb($arrToExclude)) {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbexport_success"));
        }
        else {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbexport_error"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("systemtask_dbexport_exclude_intro"));
        $strReturn .= $this->objToolkit->formInputDropdown("dbExcludeTables", array(0 => $this->getLang("commons_no"), 1 => $this->getLang("commons_yes")), $this->getLang("systemtask_dbexport_excludetitle"));
        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function getSubmitParams()
    {
        return "&excludeTables=".$this->getParam("dbExcludeTables");
    }

}
