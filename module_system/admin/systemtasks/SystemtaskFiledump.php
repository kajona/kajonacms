<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\SystemModule;
use Kajona\System\System\Zip;


/**
 * Creates a zip-archive of all relevant folders in the system
 *
 * @package module_system
 */
class SystemtaskFiledump extends SystemtaskBase implements AdminSystemtaskInterface {

    private $arrFoldersToInclude = array(
        "/files",
        "/project",
        "/templates"
    );


    private $arrFilesToInclude = array(
        "/.htaccess"
    );


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "filedump";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_filedump_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!SystemModule::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        $strFilename = "/backup_" . time() . ".zip";

        $objZip = new Zip();
        $objZip->openArchiveForWriting($strFilename);
        foreach($this->arrFoldersToInclude as $strOneFolder) {
            $objZip->addFolder($strOneFolder);
        }

        foreach($this->arrFilesToInclude as $strOneFile) {
            $objZip->addFile($strOneFile);
        }

        if($objZip->closeArchive()) {
            return $this->getLang("systemtask_filedump_success") . $strFilename;
        }
        else {
            return $this->getLang("systemtask_filedump_error");
        }
    }

}
