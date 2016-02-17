<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;



/**
 * Runs a few kajona-specific checks to ensure the db-integrity
 *
 * @package module_system
 */
class SystemtaskDbconsistency extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "database";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "dbconsistency";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_dbconsistency_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

    	$intI = 0;
    	$strReturn = "";
        $objWorker = new class_module_system_worker();

        //chec 1.level nodes
        $arrCorruptedRecords = $objWorker->checkFirstLevelNodeConsistency();

        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_firstlevel_error"), getImageAdmin("icon_disabled"), "", ++$intI);
            foreach($arrCorruptedRecords as $arrRow)  {
                $objRecord = class_objectfactory::getInstance()->getObject($arrRow["system_id"]);
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrRow["system_id"]." (".uniStrTrim(($objRecord != null ? $objRecord->getStrDisplayName() : $arrRow["system_comment"]), 20).")", "", "", $intI);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_firstlevel_ok"), getImageAdmin("icon_enabled"), "", ++$intI);
            $strReturn .= $this->objToolkit->listFooter();
        }

        //Check system_prev_id => system_id relations
        $arrCorruptedRecords = $objWorker->checkSystemTableCurPrevRelations();

        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_curprev_error"), getImageAdmin("icon_disabled"), "", ++$intI);
            foreach($arrCorruptedRecords as $strID => $strComment)  {
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $strID." (".$strComment.")", "", "", $intI);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_curprev_ok"), getImageAdmin("icon_enabled"), "", ++$intI);
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every right-record has a system-record
        $arrCorruptedRecords = $objWorker->chekRightSystemRelations();
        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_right_error"), getImageAdmin("icon_disabled"), "", ++$intI);
            foreach($arrCorruptedRecords as $arrOneRecords)  {
                $objRecord = class_objectfactory::getInstance()->getObject($arrOneRecords["system_id"]);
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrOneRecords["right_id"]." (".uniStrTrim(($objRecord != null ? $objRecord->getStrDisplayName() : $arrOneRecords["system_comment"]), 20).")", "", "", $intI);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_right_ok"), getImageAdmin("icon_enabled"), "", ++$intI);
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every date-record has a system-record
        $arrCorruptedRecords = $objWorker->checkDateSystemRelations();
        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_date_error"), getImageAdmin("icon_disabled"), "", ++$intI);
            foreach($arrCorruptedRecords as $arrOneRecords)  {
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $arrOneRecords["system_date_id"], "", "", $intI);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("systemtask_dbconsistency_date_ok"), getImageAdmin("icon_enabled"), "", ++$intI);
            $strReturn .= $this->objToolkit->listFooter();
        }

        return $strReturn;

    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        return "";
    }

}
