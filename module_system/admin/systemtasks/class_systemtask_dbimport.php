<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Restores the database from the filesystem using the current db-driver
 *
 * @package module_system
 */
class class_systemtask_dbimport extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
    }

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
                $arrOptions[$strOneFile] = $strOneFile." (".timeToString($strTimestamp)." - ".bytesToString($arrDetails["filesize"]).")";
            else
                $arrOptions[$strOneFile] = $strOneFile." (".bytesToString($arrDetails["filesize"]).")";

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
