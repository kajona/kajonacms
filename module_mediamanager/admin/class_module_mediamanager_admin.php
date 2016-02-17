<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                        *
********************************************************************************************************/
use Kajona\System\System\ModelInterface;

/**
 * Admin class of the mediamanager-module. Used to sync the repos with the filesystem and to upload / manage
 * files.
 * Successor and combination of v3s' filemanager, galleries and download modules
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @objectList class_module_mediamanager_repo
 * @objectEdit class_module_mediamanager_repo
 * @objectNew class_module_mediamanager_repo
 *
 * @objectEditFile class_module_mediamanager_file
 *
 * @autoTestable list,new
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_mediamanager_admin extends class_admin_evensimpler implements interface_admin  {

    const INT_LISTTYPE_FOLDER = "INT_LISTTYPE_FOLDER";
    const INT_LISTTYPE_FOLDERVIEW = "INT_LISTTYPE_FOLDERVIEW";

    /**
     * @return array
     */
    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", class_link::getLinkAdmin($this->getArrModule("modul"), "massSync", "", $this->getLang("action_mass_sync"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", class_link::getLinkAdmin($this->getArrModule("modul"), "logbook", "", $this->getLang("action_logbook"), "", "", true, "adminnavi"));
        return $arrReturn;
    }



    /**
     * @param \Kajona\System\System\Model|class_module_mediamanager_repo|class_module_mediamanager_file $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {

        if($objListEntry instanceof class_module_mediamanager_repo && $objListEntry->rightView())
            return array($this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&sync=true&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
            ));

        else if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER && $objListEntry->rightView())
            return array($this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
            ));

        else if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {

            $arrReturn = array();
            //add a crop icon?
            $arrMime  = $this->objToolkit->mimeType($objListEntry->getStrFilename());
            if(($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") && $objListEntry->rightEdit()) {
                $arrReturn[] = $this->objToolkit->listButton(
                    class_link::getLinkAdminDialog($this->getArrModule("modul"), "imageDetails", "&file=".$objListEntry->getStrFilename(), "", $this->getLang("action_edit_image"), "icon_crop", $objListEntry->getStrDisplayName())
                );
            }

            if($objListEntry->rightRight2()) {
                $arrReturn[] = $this->objToolkit->listButton(
                    class_link::getLinkAdminManual("href='"._webpath_."/download.php?systemid=".$objListEntry->getSystemid()."'", $this->getLang("action_download"), $this->getLang("action_download"), "icon_downloads")
                );
            }

            return $arrReturn;
        }

        return array();
    }

    /**
     * @param \Kajona\System\System\ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry) {
        if($objListEntry instanceof class_module_mediamanager_repo) {
            if($objListEntry->rightDelete()) {

                $objLockmanager = $objListEntry->getLockManager();
                if(!$objLockmanager->isAccessibleForCurrentUser()) {
                    return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteLocked", $this->getLang("commons_locked")));
                }

                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("delete_question_repo", $objListEntry->getArrModule("modul")),
                    class_link::getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon())
                );
            }
            else
                return "";
        }
        else
            return parent::renderDeleteAction($objListEntry);
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {

        if($strListIdentifier == class_module_mediamanager_admin::INT_LISTTYPE_FOLDER || $strListIdentifier == class_module_mediamanager_admin::INT_LISTTYPE_FOLDERVIEW) {
            if(validateSystemid($this->getSystemid())) {
                $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());
                if($objCur->rightEdit())
                    return $this->objToolkit->listButton(class_link::getLinkAdminManual("href=\"javascript:init_fm_newfolder_dialog();\"", "", $this->getLang("commons_create_folder"), "icon_new"));
            }

        }
        else {
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        }

        return "";
    }

    /**
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == class_module_mediamanager_admin::INT_LISTTYPE_FOLDER) {
            $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());

            if($objCur instanceof class_module_mediamanager_file)
                return $this->objToolkit->listButton(class_link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objCur->getPrevId(), "..", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup"));
            else if($objCur instanceof class_module_mediamanager_repo)
                return $this->objToolkit->listButton(class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", "..", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup"));
        }
        if($strListIdentifier == self::INT_LISTTYPE_FOLDERVIEW) {
            $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());
            $strTargetId = $objCur->getPrevId();

            if($strTargetId == $this->getObjModule()->getSystemid())
                $strTargetId = "";

            return $this->objToolkit->listButton(
                class_link::getLinkAdmin($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$this->getParam("form_element")."&systemid=".$strTargetId, "", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup")
            );
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(\Kajona\System\System\Model $objListEntry, $bitDialog = false) {
        if($objListEntry instanceof class_module_mediamanager_file) {
            if($objListEntry->rightEdit()) {
                if($this->getStrPeAddon() != "")
                    return $this->objToolkit->listButton(
                        class_link::getLinkAdmin(
                            $objListEntry->getArrModule("modul"), "editFile", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon(), $this->getLang("commons_list_edit"), $this->getLang("commons_list_edit"), "icon_edit"
                        )
                    );
                else
                    return $this->objToolkit->listButton(
                        class_link::getLinkAdminDialog(
                            $objListEntry->getArrModule("modul"), "editFile", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon(), $this->getLang("commons_list_edit"), $this->getLang("commons_list_edit"), "icon_edit"
                        )
                    );
            }

            return "";
        }
        else
            return parent::renderEditAction($objListEntry, $bitDialog);
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(\Kajona\System\System\Model $objListEntry) {
        if($objListEntry instanceof class_module_mediamanager_file) {
            return "";
        }
        return parent::renderCopyAction($objListEntry);
    }


    /**
     * A general action to delete a record.
     * This method may be overwritten by subclasses.
     *
     * @permissions delete
     * @throws class_exception
     * @return void
     */
    protected function actionDelete() {
        $objRecord = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $strPrevid = $objRecord->getPrevId();

        if($objRecord != null && $objRecord->rightDelete()) {

            if($objRecord instanceof class_module_mediamanager_file) {
                $this->setParam("mediamanagerDeleteFileFromFilesystem", true);
            }


            if(!$objRecord->deleteObject())
                throw new class_exception("error deleting object ".$objRecord->getStrDisplayName(), class_exception::$level_ERROR);

            $this->actionMassSync();

            if($objRecord instanceof class_module_mediamanager_repo)
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list"));
            else
                $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&systemid=".$strPrevid));
        }
        else
            throw new class_exception("error loading object ".$this->getSystemid(), class_exception::$level_ERROR);
    }

    /**
     * @param \Kajona\System\System\Model|interface_admin_listable|\Kajona\System\System\ModelInterface $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    public function getActionIcons($objOneIterable, $strListIdentifier = "") {
        if($strListIdentifier == self::INT_LISTTYPE_FOLDERVIEW) {
            $strTargetfield = $this->getParam("form_element");

            if($objOneIterable instanceof class_module_mediamanager_file && $objOneIterable->rightView()) {

                if($objOneIterable->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER) {
                    return $this->objToolkit->listButton(
                        class_link::getLinkAdmin($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$strTargetfield."&systemid=".$objOneIterable->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
                    );
                }
                else if($objOneIterable->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {
                    return $this->objToolkit->listButton(
                        "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strTargetfield."', '".$objOneIterable->getStrFilename()."']]);\">".class_adminskin_helper::getAdminImage("icon_accept")."</a>"
                    );
                }

            }

            return "";
        }
        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }


    /**
     * Loads the content of a folder
     * If requested, loads subactions,too
     *
     * @return string
     * @permissions view
     */
    protected function actionOpenFolder() {

        $strJsCode = "";
        if($this->getParam("sync") == "true" && class_objectfactory::getInstance()->getObject($this->getSystemid())->rightRight1()) {

            $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager");

            $strJsCode = <<<HTML
            <script type="text/javascript">
                KAJONA.admin.loader.loadFile('{$strCore}/module_mediamanager/admin/scripts/mediamanager.js', function() {
                    KAJONA.admin.ajax.genericAjaxCall("mediamanager", "syncRepo", "{$this->getSystemid()}", function(data, status, jqXHR) {
                        if(status == 'success') {
                            if(data.indexOf("<repo>0</repo>") == -1) {
                                //show a dialog to reload the current page
                                jsDialog_1.setTitle('{$this->getLang('repo_change')}'); jsDialog_1.setContent('{$this->getLang('repo_change_hint')}', '{$this->getLang('repo_reload')}', 'javascript:document.location.reload();'); jsDialog_1.init();
                            }
                        }
                        else {
                            KAJONA.admin.statusDisplay.messageError("<b>Request failed!</b>")
                        }
                    })
                });

            </script>
HTML;
        }

        $strActions = "";
        $strActions .= $this->actionUploadFileInternal();
        $strActions .= $this->generateNewFolderDialogCode();

        $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid()));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $strJsCode.$strActions.$this->renderFloatingGrid($objIterator, class_module_mediamanager_admin::INT_LISTTYPE_FOLDER);


    }


    /**
     * Generates the code to delete a folder via ajax
     * @return string
     */
    private function generateNewFolderDialogCode() {

        if(!class_objectfactory::getInstance()->getObject($this->getSystemid())->rightRight1())
            return "";

        $strReturn = "";

        //Build code for create-dialog
        $strDialog = $this->objToolkit->formInputText("folderName", $this->getLang("commons_name"));

        $strReturn .= "<script type=\"text/javascript\">\n
                        KAJONA.admin.loader.loadFile('".class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager")."/module_mediamanager/admin/scripts/mediamanager.js');
                        function init_fm_newfolder_dialog() {
                            jsDialog_1.setTitle('".$this->getLang("folder_new_dialogHeader")."');
                            jsDialog_1.setContent('".uniStrReplace(array("\r\n", "\n"), "", addslashes($strDialog))."',
                                                  '".$this->getLang("commons_create_folder")."',
                                                  'javascript:KAJONA.admin.mediamanager.createFolder(\'folderName\', \'".$this->getSystemid()."\'); jsDialog_1.hide();');
                                    jsDialog_1.init(); }\n
                      ";

        $strReturn .= "</script>";
        return $strReturn;
    }



    /**
     * Uploads or shows the form to upload a file
     *
     * @return string
     */
    private function actionUploadFileInternal() {

        if(!class_objectfactory::getInstance()->getObject($this->getSystemid())->rightRight1())
            return "";

        $strReturn = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objCurFile */
        $objCurFile = class_objectfactory::getInstance()->getObject($this->getSystemid());

        while(!$objCurFile instanceof class_module_mediamanager_repo && validateSystemid($this->getSystemid()))
            $objCurFile = class_objectfactory::getInstance()->getObject($objCurFile->getPrevId());

        $strReturn .= $this->objToolkit->formInputUploadMultiple("mediamanager_upload", $objCurFile->getStrUploadFilter(), $this->getSystemid());

        return $strReturn;
    }


    /**
     * Synchronizes all repos available
     *
     * @return string
     * @permission edit
     * @autoTestable
     */
    protected function actionMassSync() {

        /** @var $arrRepos class_module_mediamanager_repo[] */
        $arrRepos = class_module_mediamanager_repo::getObjectList();
        $arrSyncs = array("insert" => 0, "delete" => 0);
        foreach($arrRepos as $objOneRepo) {
            if($objOneRepo->rightEdit()) {
                $arrTemp = $objOneRepo->syncRepo();
                $arrSyncs["insert"] += $arrTemp["insert"];
                $arrSyncs["delete"] += $arrTemp["delete"];
            }
        }
        $strReturn = $this->getLang("sync_end");
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("sync_add").$arrSyncs["insert"].$this->getLang("sync_del").$arrSyncs["delete"]);

        //Flush cache
        $this->flushCompletePagesCache();

        return $strReturn;
    }



    /**
     * @return string
     * @permissions edit
     */
    protected function actionSaveFile() {

        $this->setStrCurObjectTypeName("file");
        $this->setCurObjectClassName("class_module_mediamanager_file");
        parent::actionSave();

        $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());

        $this->flushCompletePagesCache();
        if($this->getParam("source") != "")
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&systemid=".$objFile->getPrevId()));
        else
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&peClose=1&blockAction=1&systemid=".$objFile->getPrevId()));
        return "";


    }


    /**
     * Returns details and additional functions handling the current image.
     *
     * @return string
     */
    protected function actionImageDetails() {
        $strReturn = "";

        //overlay-mode
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strFile = $this->getParam("file");
        $strFile = uniStrReplace(_webpath_, "", $strFile);


        if(is_file(_realpath_.$strFile)) {

            $objFilesystem = new class_filesystem();
            $arrDetails = $objFilesystem->getFileDetails($strFile);
            $arrSize = getimagesize(_realpath_.$strFile);


            //Generate Dimensions
            $intHeight = $arrSize[1];
            $intWidth = $arrSize[0];

            while($intWidth > 500 || $intHeight > 400) {
                $intWidth *= 0.8;
                $intHeight *= 0.8;
            }
            //Round
            $intWidth = number_format($intWidth, 0);
            $intHeight = number_format($intHeight, 0);
            $strImage = "<img src=\""._webpath_."/image.php?image=".urlencode($strFile)."&amp;maxWidth=".$intWidth."&amp;maxHeight=".$intHeight."\" id=\"fm_mediamanagerPic\" style=\"max-width: none;\" />";


            $arrActions = array();
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showRealSize(); return false;\"", "", $this->getLang("showRealsize"), "icon_zoom_in")
            );
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual(
                    "href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showPreview(); return false;\"",
                    "",
                    $this->getLang("showPreview"),
                    "icon_zoom_out"
                )
            )." ";
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.rotate(90); return false;\"", "", $this->getLang("rotateImageLeft"), "icon_rotate_left")
            );
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.rotate(270); return false;\"", "", $this->getLang("rotateImageRight"), "icon_rotate_right")
            )." ";
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showCropping(); return false;\"", "", $this->getLang("cropImage"), "icon_crop")
            );
            $arrActions[] = $this->objToolkit->listButton(
                class_link::getLinkAdminManual("href=\"#\" id=\"accept_icon\"  onclick=\"KAJONA.admin.mediamanager.imageEditor.saveCropping(); return false;\"", "", $this->getLang("cropImageAccept"), "icon_crop_acceptDisabled")
            )." ";


            $strReturn .= $this->objToolkit->getContentToolbar($arrActions);

            $strReturn .= "<div class=\"imageContainer\"><div class=\"image\">".$strImage."</div></div>";

            $strJs = "<script type=\"text/javascript\">
                KAJONA.admin.loader.loadFile([
                    '".class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager")."/module_mediamanager/admin/scripts/mediamanager.js',
                    '".class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager")."/module_mediamanager/admin/scripts/jcrop/jquery.Jcrop.js',
                    '".class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager")."/module_mediamanager/admin/scripts/jcrop/css/jquery.Jcrop.min.css'
                ]);

                var fm_image_rawurl = '"._webpath_."/image.php?image=".urlencode($strFile)."&quality=80';
                var fm_image_scaledurl = '"._webpath_."/image.php?image=".urlencode($strFile)."&maxWidth=__width__&maxHeight=__height__';
                var fm_image_scaledMaxWidth = $intWidth;
                var fm_image_scaledMaxHeight = $intHeight;
                var fm_image_isScaled = true;
                var fm_file = '".$strFile."' ;

                function init_fm_crop_save_warning_dialog() { jsDialog_1.setTitle('".$this->getLang("cropWarningDialogHeader")."'); jsDialog_1.setContent('".$this->getLang("cropWarningSaving")."', '".$this->getLang("cropWarningCrop")."', 'javascript:KAJONA.admin.mediamanager.imageEditor.saveCroppingToBackend()'); jsDialog_1.init(); }
                function init_fm_screenlock_dialog() { jsDialog_3.init(); }
                function hide_fm_screenlock_dialog() { jsDialog_3.hide(); }


                KAJONA.admin.strCropEnabled= '".addslashes(class_adminskin_helper::getAdminImage("icon_crop_accept", $this->getLang("cropImageAccept")))."';
                KAJONA.admin.strCropDisabled = '".addslashes(class_adminskin_helper::getAdminImage("icon_crop_acceptDisabled", $this->getLang("cropImageAccept")))."';

                </script>";

            $strJs .= "<input type=\"hidden\" name=\"fm_int_realwidth\" id=\"fm_int_realwidth\" value=\"".$arrSize[0]."\" />";
            $strJs .= "<input type=\"hidden\" name=\"fm_int_realheight\" id=\"fm_int_realheight\" value=\"".$arrSize[1]."\" />";

            $strReturn .= $strJs;

            $arrTable = array();
            $arrTable[] = array($this->getLang("commons_path"), $strFile);

            $arrTable[] = array($this->getLang("image_dimensions"), $arrSize[0]." x ".$arrSize[1]);
            $arrTable[] = array($this->getLang("file_size"), bytesToString($arrDetails["filesize"]));
            $arrTable[] = array($this->getLang("file_editdate"), timeToString($arrDetails["filechange"]));
            $strReturn .= $this->objToolkit->divider().$this->objToolkit->dataTable(null, $arrTable);

        }
        return $strReturn;
    }


    /**
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrEntries = parent::getArrOutputNaviEntries();

        //remove the duplicated link to the repo-list http://trace.kajona.de/view.php?id=856
        if(isset($arrEntries[2]))
            unset($arrEntries[2]);

        return $arrEntries;
    }

    /**
     * @param \Kajona\System\System\ModelInterface|\Kajona\System\System\Model $objInstance
     *
     * @return string
     */
    protected function getOutputNaviEntry(ModelInterface $objInstance) {
        return class_link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrDisplayName());
    }


    /**
     * Loads the content of a folder
     * If requested, loads subactions,too
     *
     * SPECIAL MODE FOR MODULE FOLDERVIEW
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionFolderContentFolderviewMode() {
        $strReturn = "<script type='text/javascript'>KAJONA.admin.loader.loadFile('".class_resourceloader::getInstance()->getCorePathForModule("module_mediamanager")."/module_mediamanager/admin/scripts/mediamanager.js');</script>";

        //if set, save CKEditors CKEditorFuncNum parameter to read it again in KAJONA.admin.folderview.selectCallback()
        //so we don't have to pass through the param with all requests
        if ($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = ".(int)$this->getParam("CKEditorFuncNum").";</script>";
        }

        $strTargetfield = $this->getParam("form_element");

        $this->setArrModuleEntry("template", "/folderview.tpl");

        //list repos or contents?
        if($this->getSystemid() == "") {
            //Load the repos
            $arrObjRepos = class_module_mediamanager_repo::getObjectList();
            $intI = 0;
            //Print every repo
            /** @var class_module_mediamanager_repo $objOneRepo */
            foreach($arrObjRepos as $objOneRepo) {
                //check rights
                if($objOneRepo->rightView()) {
                    $strActions = "";
                    $strActions .= $this->objToolkit->listButton(
                        class_link::getLinkAdmin(
                            $this->getArrModule("modul"),
                            "folderContentFolderviewMode",
                            "&form_element=".$strTargetfield."&systemid=".$objOneRepo->getSystemid(),
                            "",
                            $this->getLang("action_open_folder"),
                            "icon_folderActionOpen"
                        )
                    );

                    $strReturn .= $this->objToolkit->simpleAdminList($objOneRepo, $strActions, $intI++);
                }
            }

            if(uniStrlen($strReturn) != 0)
                $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

            if(count($arrObjRepos) == 0)
                $strReturn .= $this->getLang("commons_list_empty");
        }
        else {
            $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());
            if($objFile === null || !$objFile->rightView())
                return $this->getLang("commons_error_permissions");

            $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid()));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

            $strReturn .= $this->actionUploadFileInternal();
            $strReturn .= $this->generateNewFolderDialogCode();
            $strReturn .= $this->renderFloatingGrid($objIterator, class_module_mediamanager_admin::INT_LISTTYPE_FOLDERVIEW, "&form_element=".$this->getParam("form_element"), false);
        }

        return $strReturn;
    }

    /**
     * @param interface_admin_listable $objOneIterable
     * @param string $strListIdentifier
     *
     * @return string
     */
    protected function renderGridEntryClickAction($objOneIterable, $strListIdentifier) {
        if($strListIdentifier == self::INT_LISTTYPE_FOLDERVIEW && $objOneIterable instanceof class_module_mediamanager_file) {

            if($objOneIterable->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER) {
                return "onclick=\"document.location='".class_link::getLinkAdminHref($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$this->getParam("form_element")."&systemid=".$objOneIterable->getSystemid())."'\"";
            }
            else if($objOneIterable->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {

                $strValue = $objOneIterable->getStrFilename();
                $arrMime  = $this->objToolkit->mimeType($strValue);
                $bitImage = false;
                if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif")
                    $bitImage = true;

                if ($bitImage && $this->getParam("form_element") == "ckeditor") {
                    $strValue = _webpath_."/image.php?image=".$strValue;
                } else {
                    $strValue = _webpath_.$strValue;
                }


                return "onclick=\"KAJONA.admin.folderview.selectCallback([['".$this->getParam("form_element")."', '".$strValue."']]);\"";
            }

            return "";
        }
        return parent::renderGridEntryClickAction($objOneIterable, $strListIdentifier);
    }


    /**
     * Generates a view to browse the filesystem directly.
     * By default, the methods takes two params into account: folder and form_element
     *
     * @return string
     * @autoTestable
     */
    protected function actionFolderListFolderview() {

        $this->setArrModuleEntry("template", "/folderview.tpl");
        $strReturn = "";

        //param inits
        $strFolder = "/files";
        if($this->getParam("folder") != "")
            $strFolder = $this->getParam("folder");

        $arrExcludeFolder = array(0 => ".", 1 => "..");
        $strFormElement = $this->getParam("form_element");


        $objFilesystem = new class_filesystem();
        $arrContent = $objFilesystem->getCompleteList($strFolder, array(), array(), $arrExcludeFolder, true, false);

        $strReturn .= $this->objToolkit->listHeader();
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("commons_path"), "", $strFolder, 1);
        $strReturn .= $this->objToolkit->listFooter();
        $strReturn .= $this->objToolkit->divider();

        $intCounter = 0;
        //Show Folders
        //Folder to jump one back up
        $arrFolderStart = array("/files");
        $strReturn .= $this->objToolkit->listHeader();
        $bitHit = false;
        if(!in_array($strFolder, $arrFolderStart) && $bitHit == false) {
            $strAction = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    $this->getArrModule("modul"),
                    "folderListFolderview",
                    "&folder=".uniSubstr($strFolder, 0, uniStrrpos($strFolder, "/"))."&form_element=".$strFormElement,
                    $this->getLang("commons_one_level_up"),
                    $this->getLang("commons_one_level_up"),
                    "icon_folderActionLevelup"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", class_adminskin_helper::getAdminImage("icon_folderOpen"), $strAction, $intCounter++);
        }
        if($arrContent["nrFolders"] != 0) {
            foreach($arrContent["folders"] as $strFolderCur) {
                $strAction  = $this->objToolkit->listButton(
                    class_link::getLinkAdmin(
                        $this->getArrModule("modul"),
                        "folderListFolderview",
                        "&folder=".$strFolder."/".$strFolderCur."&form_element=".$strFormElement,
                        $this->getLang("action_open_folder"),
                        $this->getLang("action_open_folder"),
                        "icon_folderActionOpen"
                    )
                );
                $strAction .= $this->objToolkit->listButton(
                    "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".$strFolder."/".$strFolderCur."']]);\">"
                    .class_adminskin_helper::getAdminImage("icon_accept")
                );
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $strFolderCur, class_adminskin_helper::getAdminImage("icon_folderOpen"), $strAction, $intCounter++);
            }
        }
        if($bitHit)
            $strReturn .= $this->objToolkit->listFooter();

        return $strReturn;
    }


    /**
     * Show a logbook of all downloads
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionLogbook() {
        $strReturn = "";

        $intNrOfRecordsPerPage = 25;

        $strReturn .= $this->objToolkit->getTextRow(class_link::getLinkAdmin($this->getArrModule("modul"), "logbookFlush", "", $this->getLang("action_logbook_flush"), "")."<br />");

        $objLogbook = new class_module_mediamanager_logbook();
        $objArraySectionIterator = new class_array_section_iterator($objLogbook->getLogbookDataCount());
        $objArraySectionIterator->setIntElementsPerPage($intNrOfRecordsPerPage);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objLogbook->getLogbookData($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrLogs = array();
        foreach($objArraySectionIterator as $intKey => $arrOneLog) {
            $arrLogs[$intKey][0] = $arrOneLog["downloads_log_id"];
            $arrLogs[$intKey][1] = timeToString($arrOneLog["downloads_log_date"]);
            $arrLogs[$intKey][2] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][3] = $arrOneLog["downloads_log_user"];
            $arrLogs[$intKey][4] = $arrOneLog["downloads_log_ip"];
            
            $strUtraceLinkMap = "href=\"http://www.utrace.de/ip-adresse/".$arrOneLog["downloads_log_ip"]."\" target=\"_blank\"";
            $strUtraceLinkText = "href=\"http://www.utrace.de/whois/".$arrOneLog["downloads_log_ip"]."\" target=\"_blank\"";
            if($arrOneLog["downloads_log_ip"] != "127.0.0.1" && $arrOneLog["downloads_log_ip"] != "::1") {
                $arrLogs[$intKey][5]	= class_link::getLinkAdminManual($strUtraceLinkMap, "", $this->getLang("login_utrace_showmap", "user"), "icon_earth")
                    . " " . class_link::getLinkAdminManual($strUtraceLinkText, "", $this->getLang("login_utrace_showtext", "user"), "icon_text");
            }
            else 
                $arrLogs[$intKey][5] = class_adminskin_helper::getAdminImage("icon_earthDisabled", $this->getLang("login_utrace_noinfo", "user")) ." "
                    .class_adminskin_helper::getAdminImage("icon_textDisabled", $this->getLang("login_utrace_noinfo", "user"));
        }
        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = $this->getLang("header_id");
        $arrHeader[1] = $this->getLang("commons_date");
        $arrHeader[2] = $this->getLang("header_file");
        $arrHeader[3] = $this->getLang("header_user");
        $arrHeader[4] = $this->getLang("header_ip");
        $arrHeader[5] = $this->getLang("login_utrace", "user");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);
        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $this->getArrModule("modul"), "logbook");

        return $strReturn;
    }

    /**
     * Shows a form or deltes a timeintervall from the logs
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions edit
     * @autoTestable
     */
    protected function actionLogbookFlush() {
        $strReturn = "";
        if($this->getParam("flush") == "") {
            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul"), "logbookFlush", "flush=1"));
            $strReturn .= $this->objToolkit->formTextRow($this->getLang("logbook_hint_date"));
            $strReturn .= $this->objToolkit->formDateSingle("date", $this->getLang("commons_date"), new \Kajona\System\System\Date());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        }
        elseif ($this->getParam("flush") == "1") {
            //Build the date
            $objDate = new \Kajona\System\System\Date();
            $objDate->generateDateFromParams("date", $this->getAllParams());

            if(!class_module_mediamanager_logbook::deleteFromLogs($objDate->getTimeInOldStyle()))
                throw new class_exception("Error deleting log-rows", class_exception::$level_ERROR);

            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "logbook"));
        }
        return $strReturn;
    }
}

