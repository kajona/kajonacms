<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Mediamanager\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\StringUtil;


/**
 * Model for a mediamanagers repo itself
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @targetTable mediamanager_repo.repo_id
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 *
 * @formGenerator Kajona\Mediamanager\Admin\MediamanagerRepoFormgenerator
 */
class MediamanagerRepo extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_title
     * @tableColumnDatatype char254
     * @listOrder
     * @fieldLabel commons_title
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     *
     * @addSearchIndex
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_path
     * @tableColumnDatatype char254
     * @fieldMandatory
     * @fieldValidator Kajona\System\System\Validators\FolderValidator
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel commons_path
     *
     * @addSearchIndex
     */
    private $strPath = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_upload_filter
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_repo_uploadFilter
     */
    private $strUploadFilter = "";

    /**
     * @var string
     * @tableColumn mediamanager_repo.repo_view_filter
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_repo_viewFilter
     */
    private $strViewFilter = "";


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_folderOpen";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return $this->getStrPath();
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrTitle();
    }

    /**
     * Syncs the complete repo with the filesystem. Adds new files and removes delete files to and
     * from the db.
     *
     * @return array [insert, delete]
     */
    public function syncRepo()
    {
        return MediamanagerFile::syncRecursive($this->getSystemid(), $this->getStrPath());
    }

    public function deleteObjectFromDatabase()
    {
        $this->setParam("deleteMediamanagerRepo", true);
        return parent::deleteObjectFromDatabase();
    }


    public function getStrPath()
    {
        return $this->strPath;
    }

    public function getStrTitle()
    {
        return $this->strTitle;
    }


    public function setStrPath($strPath)
    {
        if (StringUtil::indexOf(uniSubstr($strPath, 0, 7), "files") === false) {
            $strPath = "/files".$strPath;
        }

        $this->strPath = $strPath;
    }

    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * @param string $strUploadFilter
     */
    public function setStrUploadFilter($strUploadFilter)
    {
        $this->strUploadFilter = $strUploadFilter;
    }

    /**
     * @return string
     */
    public function getStrUploadFilter()
    {
        return $this->strUploadFilter;
    }

    /**
     * @param string $strViewFilter
     */
    public function setStrViewFilter($strViewFilter)
    {
        $this->strViewFilter = $strViewFilter;
    }

    /**
     * @return string
     */
    public function getStrViewFilter()
    {
        return $this->strViewFilter;
    }

}

