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
use Kajona\System\System\Filesystem;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;


/**
 * Restores the database from the filesystem using the current db-driver
 *
 * @package module_system
 */
class SystemtaskDbimport extends SystemtaskBase implements AdminSystemtaskInterface
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
        return "dbimport";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_dbimport_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {
        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        if (Carrier::getInstance()->getObjDB()->importDb($this->getParam("dbImportFile"))) {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbimport_success"));
        }
        else {
            return $this->objToolkit->getTextRow($this->getLang("systemtask_dbimport_error"));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {
        $strReturn = "";
        //show dropdown to select db-dump
        $objFilesystem = new Filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/dbdumps/", array(".sql", ".gz"));
        $arrOptions = array();
        foreach ($arrFiles as $strOneFile) {
            $arrDetails = $objFilesystem->getFileDetails(_projectpath_."/dbdumps/".$strOneFile);

            $strTimestamp = "";
            if (StringUtil::indexOf($strOneFile, "_") !== false) {
                $strTimestamp = uniSubstr($strOneFile, StringUtil::lastIndexOf($strOneFile, "_") + 1, (StringUtil::indexOf($strOneFile, ".") - StringUtil::lastIndexOf($strOneFile, "_")));
            }

            if (uniStrlen($strTimestamp) > 9 && is_numeric($strTimestamp)) {
                $arrOptions[$strOneFile] = $strOneFile." (".bytesToString($arrDetails["filesize"]).")"
                    ."<br />".$this->getLang("systemtask_dbimport_datefilename")." ".timeToString($strTimestamp)
                    ."<br />".$this->getLang("systemtask_dbimport_datefileinfo")." ".timeToString($arrDetails['filechange']);
            }

            else {
                $arrOptions[$strOneFile] = $strOneFile." (".bytesToString($arrDetails["filesize"]).")"
                    ."<br />".$this->getLang("systemtask_dbimport_datefileinfo")." ".timeToString($arrDetails['filechange']);
            }
        }

        $strReturn .= $this->objToolkit->formInputRadiogroup("dbImportFile", $arrOptions, $this->getLang("systemtask_dbimport_file"));

        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function getSubmitParams()
    {
        return "&dbImportFile=".$this->getParam("dbImportFile");
    }
}
