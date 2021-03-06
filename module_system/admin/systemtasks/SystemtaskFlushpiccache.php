<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\System\Filesystem;
use Kajona\System\System\SystemModule;


/**
 * Flushes all images saved to the cache
 *
 * @package module_system
 */
class SystemtaskFlushpiccache extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "cache";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "flushpiccache";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_flushpiccache_name");
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
        //fetch the number of images to be deleted
        $objFilesystem = new Filesystem();
        $arrFiles = $objFilesystem->getFilelist(_images_cachepath_, array());
        $intFilesDeleted = 0;
        $intTotalFiles = count($arrFiles);
        foreach ($arrFiles as $strOneFile) {
            if ($objFilesystem->fileDelete(_images_cachepath_."/".$strOneFile)) {
                $intFilesDeleted++;
            }
        }

        //build the return string
        $strReturn .= $this->getLang("systemtask_flushpiccache_done");
        $strReturn .= $this->getLang("systemtask_flushpiccache_deleted").$intFilesDeleted;
        $strReturn .= $this->getLang("systemtask_flushpiccache_skipped").($intTotalFiles - $intFilesDeleted);
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
