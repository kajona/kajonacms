<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
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
class SystemtaskFiledump extends SystemtaskBase implements AdminSystemtaskInterface
{

    private $arrFoldersToInclude = array(
        "/files",
        "/project",
        "/templates"
    );


    private $arrFilesToInclude = array(
        "/.htaccess"
    );


    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "filedump";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_filedump_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        $strFilename = "/backup_".time().".zip";

        $objZip = new Zip();
        $objZip->openArchiveForWriting($strFilename);
        foreach ($this->arrFoldersToInclude as $strOneFolder) {
            $objZip->addFolder($strOneFolder);
        }

        foreach ($this->arrFilesToInclude as $strOneFile) {
            $objZip->addFile($strOneFile);
        }

        if ($objZip->closeArchive()) {
            return $this->getLang("systemtask_filedump_success").$strFilename;
        }
        else {
            return $this->getLang("systemtask_filedump_error");
        }
    }

}
