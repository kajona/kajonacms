<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

/**
 * Runs a few kajona-specific checks to ensure the db-integrity
 *
 * @package modul_system
 */
class class_systemtask_dbconsistency extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @see interface_admin_systemtast::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "database";
    }
    
    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "dbconsistency";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_dbconsistency_name");
    }
    
    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
    	$intI = 0;
    	$strReturn = "";
        include_once(_systempath_."/class_modul_system_worker.php");
        $objWorker = new class_modul_system_worker();

        //chec 1.level nodes
        $arrCorruptedRecords = $objWorker->checkFirstLevelNodeConsistency();

        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_disabled.gif"), $this->getText("systemtask_dbconsistency_firstlevel_error"), "", $intI++);
            foreach($arrCorruptedRecords as $arrRow)  {
                $strReturn .= $this->objToolkit->listRow2Image("", $arrRow["system_id"]." (".uniStrTrim($arrRow["system_comment"], 20).")" , "", 0);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_enabled.gif"), $this->getText("systemtask_dbconsistency_firstlevel_ok"), "", $intI++);
            $strReturn .= $this->objToolkit->listFooter();
        }
        
        //Check system_prev_id => system_id relations
        $arrCorruptedRecords = $objWorker->checkSystemTableCurPrevRelations();

        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_disabled.gif"), $this->getText("systemtask_dbconsistency_curprev_error"), "", $intI++);
            foreach($arrCorruptedRecords as $strID => $strComment)  {
                $strReturn .= $this->objToolkit->listRow2Image("", $strID." (".$strComment.")" , "", 0);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_enabled.gif"), $this->getText("systemtask_dbconsistency_curprev_ok"), "", $intI++);
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every right-record has a system-record
        $arrCorruptedRecords = $objWorker->chekRightSystemRelations();
        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_disabled.gif"), $this->getText("systemtask_dbconsistency_right_error"), "", $intI++);
            foreach($arrCorruptedRecords as $arrOneRecords)  {
                $strReturn .= $this->objToolkit->listRow2Image("", $arrOneRecords["right_id"]." (".$arrOneRecords["system_comment"].")" , "", $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_enabled.gif"), $this->getText("systemtask_dbconsistency_right_ok"), "", $intI++);
            $strReturn .= $this->objToolkit->listFooter();
        }

        //check if every date-record has a system-record
        $arrCorruptedRecords = $objWorker->chekDateSystemRelations();
        //create the output tables
        if(count($arrCorruptedRecords) > 0) {
            //ohoh. errors found. create tow tables
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_disabled.gif"), $this->getText("systemtask_dbconsistency_date_error"), "", $intI++);
            foreach($arrCorruptedRecords as $arrOneRecords)  {
                $strReturn .= $this->objToolkit->listRow2Image("", $arrOneRecords["system_date_id"], "", $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            //no errors found
            $strReturn .= $this->objToolkit->listHeader();
            $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_enabled.gif"), $this->getText("systemtask_dbconsistency_date_ok"), "", $intI++);
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
?>