<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\Objectfactory;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemWorker;


/**
 * Runs a few kajona-specific checks to ensure the db-integrity
 *
 * @package module_system
 */
class SystemtaskDbconsistency extends SystemtaskBase implements AdminSystemtaskInterface
{


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
        return "dbconsistency";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_dbconsistency_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        $strReturn = "";
        $objWorker = new SystemWorker();

        //chec 1.level nodes
        $arrCorruptedRecords = $objWorker->checkFirstLevelNodeConsistency();

        //create the output tables
        if (count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_firstlevel_error"), getImageAdmin("icon_disabled"), "");
            foreach ($arrCorruptedRecords as $arrRow) {
                $objRecord = Objectfactory::getInstance()->getObject($arrRow["system_id"]);
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrRow["system_id"]." (".StringUtil::truncate(($objRecord != null ? $objRecord->getStrDisplayName() : ""), 20).")", "", "");
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_firstlevel_ok"), getImageAdmin("icon_enabled"), "");
            $strReturn .= $this->objToolkit->listFooter();
        }

        //Check system_prev_id => system_id relations
        $arrCorruptedRecords = $objWorker->checkSystemTableCurPrevRelations();

        //create the output tables
        if (count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_curprev_error"), getImageAdmin("icon_disabled"), "");
            foreach ($arrCorruptedRecords as $strID => $strComment) {
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $strID." (".$strComment.")", "", "");
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_curprev_ok"), getImageAdmin("icon_enabled"), "");
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every right-record has a system-record
        $arrCorruptedRecords = $objWorker->chekRightSystemRelations();
        //create the output tables
        if (count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_right_error"), getImageAdmin("icon_disabled"), "");
            foreach ($arrCorruptedRecords as $arrOneRecords) {
                $objRecord = Objectfactory::getInstance()->getObject($arrOneRecords["system_id"]);
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrOneRecords["right_id"]." (".StringUtil::truncate(($objRecord != null ? $objRecord->getStrDisplayName() : ""), 20).")", "", "");
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_right_ok"), getImageAdmin("icon_enabled"), "");
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every date-record has a system-record
        $arrCorruptedRecords = $objWorker->checkDateSystemRelations();
        //create the output tables
        if (count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_date_error"), getImageAdmin("icon_disabled"), "");
            foreach ($arrCorruptedRecords as $arrOneRecords) {
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrOneRecords["system_date_id"], "", "");
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_date_ok"), getImageAdmin("icon_enabled"), "");
            $strReturn .= $this->objToolkit->listFooter();
        }

        return $strReturn;

    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        return "";
    }

}
