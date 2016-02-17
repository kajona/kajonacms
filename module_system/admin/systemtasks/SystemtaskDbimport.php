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
 * Restores the database from the filesystem using the current db-driver
 *
 * @package module_system
 */
class SystemtaskDbimport extends class_systemtask_base implements interface_admin_systemtask {


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
        return "dbimport";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_dbimport_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        if(class_carrier::getInstance()->getObjDB()->importDb($this->getParam("dbImportFile")))
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbimport_success"));
        else
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbimport_error"));
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        $strReturn = "";
        //show dropdown to select db-dump
        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));
        $arrOptions = array();
        foreach($arrFiles as $strOneFile) {
            $arrDetails = $objFilesystem->getFileDetails(_projectpath_."/dbdumps/".$strOneFile);

            $strTimestamp = "";
            if(uniStrpos($strOneFile, "_") !== false)
                $strTimestamp = uniSubstr($strOneFile, uniStrrpos($strOneFile, "_") + 1, (uniStrpos($strOneFile, ".") - uniStrrpos($strOneFile, "_")));

            if(uniStrlen($strTimestamp) > 9 && is_numeric($strTimestamp))
                $arrOptions[$strOneFile] = $strOneFile." (".bytesToString($arrDetails["filesize"]).")"
                                ."<br />".$this->getLang("systemtask_dbimport_datefilename")." ".timeToString($strTimestamp)
                                ."<br />".$this->getLang("systemtask_dbimport_datefileinfo")." ".timeToString($arrDetails['filechange']);

            else
                $arrOptions[$strOneFile] = $strOneFile." (".bytesToString($arrDetails["filesize"]).")"
                                ."<br />".$this->getLang("systemtask_dbimport_datefileinfo")." ".timeToString($arrDetails['filechange']);
        }

        $strReturn .= $this->objToolkit->formInputRadiogroup("dbImportFile", $arrOptions, $this->getLang("systemtask_dbimport_file"));

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {
        return "&dbImportFile=".$this->getParam("dbImportFile");
    }
}
