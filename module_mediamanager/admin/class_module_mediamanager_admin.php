<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                                            *
********************************************************************************************************/

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
 */
class class_module_mediamanager_admin extends class_admin_evensimpler implements interface_admin  {

    const INT_LISTTYPE_FOLDER = "INT_LISTTYPE_FOLDER";

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
        $this->setArrModuleEntry("modul", "mediamanager");
        parent::__construct();

    }


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getLang("action_new"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "massSync", "", $this->getLang("action_mass_sync"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "logbook", "", $this->getLang("action_logbook"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }



    /**
     * @param class_model|class_module_mediamanager_repo|class_module_mediamanager_file $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {

        if($objListEntry instanceof class_module_mediamanager_repo && $objListEntry->rightView())
            return array($this->objToolkit->listButton(
                getLinkAdmin($this->getArrModule("modul"), "openFolder", "&sync=true&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
            ));

        else if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER && $objListEntry->rightView())
            return array($this->objToolkit->listButton(
                getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
            ));

        else if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE && $objListEntry->rightEdit()) {
            //add a crop icon?
            $arrMime  = $this->objToolkit->mimeType($objListEntry->getStrFilename());
            if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif") {
                return array($this->objToolkit->listButton(
                    getLinkAdminDialog($this->getArrModule("modul"), "imageDetails", "&file=".$objListEntry->getStrFilename(), "", $this->getLang("action_edit_image"), "icon_crop", $objListEntry->getStrDisplayName())
                ));
            }

        }

        return array();
    }

    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_mediamanager_repo) {
            if($objListEntry->rightDelete()) {

                $objLockmanager = $objListEntry->getLockManager();
                if(!$objLockmanager->isAccessibleForCurrentUser()) {
                    return $this->objToolkit->listButton(getImageAdmin("icon_deleteLocked", $this->getLang("commons_locked")));
                }

                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("delete_question_repo", $objListEntry->getArrModule("modul")),
                    getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon())
                );
            }
            else
                return "";
        }
        else
            return parent::renderDeleteAction($objListEntry);
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier != class_module_mediamanager_admin::INT_LISTTYPE_FOLDER)
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        else if($strListIdentifier == class_module_mediamanager_admin::INT_LISTTYPE_FOLDER) {
            if(validateSystemid($this->getSystemid())) {
                $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());
                if($objCur->rightEdit())
                    return $this->objToolkit->listButton(getLinkAdminManual("href=\"javascript:init_fm_newfolder_dialog();\"", "", $this->getLang("commons_create_folder"), "icon_new"));
            }

            //href=\"javascript:init_fm_newfolder_dialog();\"", $this->getLang("commons_create_folder"), "", "", "", "", "", "btn"
        }

        return "";
    }

    protected function renderLevelUpAction($strListIdentifier) {
        if($strListIdentifier == class_module_mediamanager_admin::INT_LISTTYPE_FOLDER) {
            $objCur = class_objectfactory::getInstance()->getObject($this->getSystemid());

            if($objCur instanceof class_module_mediamanager_file)
                return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objCur->getPrevId(), "..", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup"));
            else if($objCur instanceof class_module_mediamanager_repo)
                return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "list", "", "..", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup"));
        }
        return parent::renderLevelUpAction($strListIdentifier);
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry instanceof class_module_mediamanager_file) {
            if($objListEntry->rightEdit()) {
                if($this->getStrPeAddon() != "")
                    return $this->objToolkit->listButton(
                        getLinkAdmin(
                            $objListEntry->getArrModule("modul"), "editFile", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon(), $this->getLang("commons_list_edit"), $this->getLang("commons_list_edit"), "icon_edit"
                        )
                    );
                else
                    return $this->objToolkit->listButton(
                        getLinkAdminDialog(
                            $objListEntry->getArrModule("modul"), "editFile", "&systemid=".$objListEntry->getSystemid().$this->getStrPeAddon(), $this->getLang("commons_list_edit"), $this->getLang("commons_list_edit"), "icon_edit"
                        )
                    );
            }

            return "";
        }
        else
            return parent::renderEditAction($objListEntry, $bitDialog);
    }

    protected function renderCopyAction(class_model $objListEntry) {
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
     */
    protected function actionDelete() {
        $objRecord = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $strPrevid = $objRecord->getPrevId();

        if($objRecord != null && $objRecord->rightDelete()) {
            if(!$objRecord->deleteObject())
                throw new class_exception("error deleting object ".$objRecord->getStrDisplayName(), class_exception::$level_ERROR);

            $this->actionMassSync();

            if($objRecord instanceof class_module_mediamanager_repo)
                $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
            else
                $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&systemid=".$strPrevid));
        }
        else
            throw new class_exception("error loading object ".$this->getSystemid(), class_exception::$level_ERROR);
    }


    protected function getAdminForm(interface_model $objInstance) {
        if($objInstance instanceof class_module_mediamanager_repo) {
            $objForm = new class_admin_formgenerator("repo", $objInstance);
            $objForm->addDynamicField("strTitle");
            $objField = $objForm->addDynamicField("strPath")->setStrHint($this->getLang("mediamanager_path_h"));
            $objField->setStrOpener(
                getLinkAdminDialog(
                    "mediamanager",
                    "folderListFolderview",
                    "&form_element=".$objField->getStrEntryName(),
                    $this->getLang("commons_open_browser"),
                    $this->getLang("commons_open_browser"),
                    "icon_externalBrowser",
                    $this->getLang("commons_open_browser")
                )
            );
            $objForm->addDynamicField("uploadFilter")->setStrHint($this->getLang("mediamanager_upload_filter_h"));
            $objForm->addDynamicField("viewFilter")->setStrHint($this->getLang("mediamanager_view_filter_h"));

            return $objForm;
        }
        else if($objInstance instanceof class_module_mediamanager_file) {
            if($this->getStrPeAddon() == "" && $this->getParam("source") != "search")
                $this->setArrModuleEntry("template", "/folderview.tpl");

            $objForm = parent::getAdminForm($objInstance);
            $objForm->addField(new class_formentry_hidden("", "source"))->setStrValue($this->getParam("source"));
            return $objForm;
        }
        else
            return parent::getAdminForm($objInstance);
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
            $strJsCode = <<<HTML
            <script type="text/javascript">
                KAJONA.admin.loader.loadFile('/core/module_mediamanager/admin/scripts/mediamanager.js', function() {
                    KAJONA.admin.ajax.genericAjaxCall("mediamanager", "syncRepo", "{$this->getSystemid()}", function(data, status, jqXHR) {
                        if(status == 'success') {
                            console.log("sync response: "+data);
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
            $strJsCode .= $this->objToolkit->jsDialog(1);
        }

        $strActions = "";
        $strActions .= $this->actionUploadFileInternal();
        $strActions .= $this->generateNewFolderDialogCode();

        $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid()));
        $objIterator->setIntElementsPerPage(class_module_mediamanager_file::getFileCount($this->getSystemid()));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid()));

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
                        KAJONA.admin.loader.loadFile('/core/module_mediamanager/admin/scripts/mediamanager.js');
                        function init_fm_newfolder_dialog() {
                            jsDialog_1.setTitle('".$this->getLang("folder_new_dialogHeader")."');
                            jsDialog_1.setContent('".uniStrReplace(array("\r\n", "\n"), "", addslashes($strDialog))."',
                                                  '".$this->getLang("commons_create_folder")."',
                                                  'javascript:KAJONA.admin.mediamanager.createFolder(\'folderName\', \'".$this->getSystemid()."\'); jsDialog_1.hide();');
                                    jsDialog_1.init(); }\n
                      ";

        $strReturn .= "</script>";
        $strReturn .= $this->objToolkit->jsDialog(1);
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
        $strPath = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objCurFile */
        $objCurFile = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objCurFile instanceof class_module_mediamanager_file)
            $strPath = $objCurFile->getStrFilename();
        if($objCurFile instanceof class_module_mediamanager_repo)
            $strPath = $objCurFile->getStrPath();

        while(!$objCurFile instanceof class_module_mediamanager_repo && validateSystemid($this->getSystemid()))
            $objCurFile = class_objectfactory::getInstance()->getObject($objCurFile->getPrevId());

        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], $this->getAction(), "datei_upload_final=1"), "formUpload", "multipart/form-data");
        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputHidden("mutliuploadSystemid", $this->getSystemid());

        $strReturn .= $this->objToolkit->formInputUploadMultiple("mediamanager_upload", $this->getLang("mediamanager_upload"), $objCurFile->getStrUploadFilter());
        $strReturn .= $this->objToolkit->formClose();

        if($this->getParam("datei_upload_final") != "") {
            //Handle the fileupload
            $arrSource = $this->getParam("mediamanager_upload");

            $strTarget = $strPath."/".createFilename($arrSource["name"]);
            $objFilesystem = new class_filesystem();

            //Check file for correct filters
            $arrAllowed = explode(",", $objCurFile->getStrUploadFilter());
            $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
            if($objCurFile->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                    $strReturn .= $this->getLang("upload_erfolg");

                    $objCurFile->syncRepo();

                    class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);
                }
                else
                    $strReturn .= $this->getLang("upload_fehler");
            }
            else {
                @unlink($arrSource["tmp_name"]);
                $strReturn .= $this->getLang("upload_fehler_filter");
            }
        }

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
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&systemid=".$objFile->getPrevId()));
        else
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "openFolder", "&peClose=1&systemid=".$objFile->getPrevId()));
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

        $arrTemplate = array();

        if(is_file(_realpath_.$strFile)) {

            $objFilesystem = new class_filesystem();
            $arrDetails = $objFilesystem->getFileDetails($strFile);

            $arrTemplate["file_name"] = $arrDetails["filename"];
            $arrTemplate["file_path"] = $strFile;
            $arrTemplate["file_path_title"] = $this->getLang("commons_path");

            $arrSize = getimagesize(_realpath_.$strFile);
            $arrTemplate["file_dimensions"] = $arrSize[0]." x ".$arrSize[1];
            $arrTemplate["file_dimensions_title"] = $this->getLang("image_dimensions");

            $arrTemplate["file_size"] = bytesToString($arrDetails["filesize"]);
            $arrTemplate["file_size_title"] = $this->getLang("file_size");

            $arrTemplate["file_lastedit"] = timeToString($arrDetails["filechange"]);
            $arrTemplate["file_lastedit_title"] = $this->getLang("file_editdate");

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
            $arrTemplate["file_image"] = "<img src=\""._webpath_."/image.php?image=".urlencode($strFile)."&amp;maxWidth=".$intWidth."&amp;maxHeight=".$intHeight."\" id=\"fm_mediamanagerPic\" />";

            $arrTemplate["file_actions"] = "";
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showRealSize(); return false;\"", "", $this->getLang("showRealsize"), "icon_zoom_in")
            );
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showPreview(); return false;\"", "", $this->getLang("showPreview"), "icon_zoom_out")
            )." ";
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.rotate(90); return false;\"", "", $this->getLang("rotateImageLeft"), "icon_rotate_left")
            );
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.rotate(270); return false;\"", "", $this->getLang("rotateImageRight"), "icon_rotate_right")
            )." ";
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.showCropping(); return false;\"", "", $this->getLang("cropImage"), "icon_crop")
            );
            $arrTemplate["file_actions"] .= $this->objToolkit->listButton(
                getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.mediamanager.imageEditor.saveCropping(); return false;\"", "", $this->getLang("cropImageAccept"), "icon_crop_accept", "accept_icon")
            )." ";

            $arrTemplate["filemanager_image_js"] = "<script type=\"text/javascript\">
                KAJONA.admin.loader.loadFile([
                    '/core/module_mediamanager/admin/scripts/mediamanager.js',
                    '/core/module_mediamanager/admin/scripts/jcrop/jquery.Jcrop.js',
                    '/core/module_mediamanager/admin/scripts/jcrop/css/jquery.Jcrop.min.css'
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

                </script>";

            $arrTemplate["filemanager_image_js"] .= $this->objToolkit->jsDialog(1);
            $arrTemplate["filemanager_image_js"] .= $this->objToolkit->jsDialog(3);

            $arrTemplate["filemanager_internal_code"] = "<input type=\"hidden\" name=\"fm_int_realwidth\" id=\"fm_int_realwidth\" value=\"".$arrSize[0]."\" />";
            $arrTemplate["filemanager_internal_code"] .= "<input type=\"hidden\" name=\"fm_int_realheight\" id=\"fm_int_realheight\" value=\"".$arrSize[1]."\" />";

        }
        $strReturn .= $this->objToolkit->getMediamanagerImageDetails($arrTemplate);
        return $strReturn;
    }


    protected function getOutputNaviEntry(interface_model $objInstance) {
        return getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrDisplayName());
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
        $strReturn = "<script type='text/javascript'>KAJONA.admin.loader.loadFile('/core/module_mediamanager/admin/scripts/mediamanager.js');</script>";

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
                        getLinkAdmin(
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
                $strReturn .= $this->getLang("liste_leer");
        }
        else {
            $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());
            if($objFile->rightView()) {

                $arrSubfiles = class_module_mediamanager_file::loadFilesDB($this->getSystemid());
                $intI = 0;

                if($objFile instanceof class_module_mediamanager_repo)
                    $strReturn .= $this->objToolkit->genericAdminList(
                        generateSystemid(),
                        "..",
                        getImageAdmin("icon_folderOpen"),
                        $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$strTargetfield, "", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup")),
                        $intI++
                    );
                else
                    $strReturn .= $this->objToolkit->genericAdminList(
                        generateSystemid(),
                        "..",
                        getImageAdmin("icon_folderOpen"),
                        $this->objToolkit->listButton(
                            getLinkAdmin($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$strTargetfield."&systemid=".$objFile->getPrevId(), "", $this->getLang("commons_one_level_up"), "icon_folderActionLevelup")
                        ),
                        $intI++
                    );

                foreach($arrSubfiles as $objOneFile) {

                    if($objOneFile->rightView()) {
                        $strActions = "";

                        if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER)
                            $strActions .= $this->objToolkit->listButton(
                                getLinkAdmin($this->getArrModule("modul"), "folderContentFolderviewMode", "&form_element=".$strTargetfield."&systemid=".$objOneFile->getSystemid(), "", $this->getLang("action_open_folder"), "icon_folderActionOpen")
                            );

                        $strValue = $objOneFile->getStrFilename();

                        $arrMime  = $this->objToolkit->mimeType($strValue);
                        $bitImage = false;
                        if($arrMime[1] == "jpg" || $arrMime[1] == "png" || $arrMime[1] == "gif")
                            $bitImage = true;

                        //add image.php if it's an image and file will be passed to CKEditor
                        //further processing is done in processWysiwygHtmlContent() when saving the content edited via CKEditor
                        if ($bitImage && $strTargetfield == "ckeditor") {
                            $strValue = _webpath_."/image.php?image=".$strValue;
                        } else {
                            $strValue = _webpath_.$strValue;
                        }

                        if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE)
                            $strActions .= $this->objToolkit->listButton("<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strTargetfield."', '".$strValue."']]);\">".getImageAdmin("icon_accept"));

                        $strReturn .= $this->objToolkit->simpleAdminList($objOneFile, $strActions, $intI++);
                    }
                }



                if(uniStrlen($strReturn) != 0)
                    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

                $strAddons = $this->generateNewFolderDialogCode();
                $strAddons .= getLinkAdminManual("href=\"javascript:init_fm_newfolder_dialog();\"", $this->getLang("commons_create_folder"), "", "", "", "", "", "btn");
                $strAddons .= $this->actionUploadFileInternal();

                $strReturn = $strAddons.$strReturn;

                if(count($arrSubfiles) == 0)
                    $strReturn .= $this->getLang("commons_list_empty");
            }
            else
                $strReturn = $this->getLang("commons_error_permissions");
        }

        return $strReturn;
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
                getLinkAdmin(
                    $this->arrModule["modul"],
                    "folderListFolderview",
                    "&folder=".uniSubstr($strFolder, 0, uniStrrpos($strFolder, "/"))."&form_element=".$strFormElement,
                    $this->getLang("commons_one_level_up"),
                    $this->getLang("commons_one_level_up"),
                    "icon_folderActionLevelup"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "..", getImageAdmin("icon_folderOpen"), $strAction, $intCounter++);
        }
        if($arrContent["nrFolders"] != 0) {
            foreach($arrContent["folders"] as $strFolderCur) {
                $strAction  = $this->objToolkit->listButton(
                    getLinkAdmin(
                        $this->arrModule["modul"],
                        "folderListFolderview",
                        "&folder=".$strFolder."/".$strFolderCur."&form_element=".$strFormElement,
                        $this->getLang("action_open_folder"),
                        $this->getLang("action_open_folder"),
                        "icon_folderActionOpen"
                    )
                );
                $strAction .= $this->objToolkit->listButton(
                    "<a href=\"#\" title=\"".$this->getLang("commons_accept")."\" rel=\"tooltip\" onclick=\"KAJONA.admin.folderview.selectCallback([['".$strFormElement."', '".$strFolder."/".$strFolderCur."']]);\">"
                    .getImageAdmin("icon_accept")
                );
                $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $strFolderCur, getImageAdmin("icon_folderOpen"), $strAction, $intCounter++);
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

        $strReturn .= $this->objToolkit->getTextRow(getLinkAdmin($this->getArrModule("modul"), "logbookFlush", "", $this->getLang("action_logbook_flush"), "")."<br />");

        $objLogbook = new class_module_mediamanager_logbook();
        $objArraySectionIterator = new class_array_section_iterator($objLogbook->getLogbookDataCount());
        $objArraySectionIterator->setIntElementsPerPage($intNrOfRecordsPerPage);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objLogbook->getLogbookData($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->getArrModule("modul"), "logbook");

        $arrLogsRaw = $arrPageViews["elements"];
        $arrLogs = array();
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            $arrLogs[$intKey][0] = $arrOneLog["downloads_log_id"];
            $arrLogs[$intKey][1] = timeToString($arrOneLog["downloads_log_date"]);
            $arrLogs[$intKey][2] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][3] = $arrOneLog["downloads_log_user"];
            $arrLogs[$intKey][4] = $arrOneLog["downloads_log_ip"];
            
            $strUtraceLinkMap = "http://www.utrace.de/ip-adresse/".$arrOneLog["downloads_log_ip"];
            $strUtraceLinkText = "http://www.utrace.de/whois/".$arrOneLog["downloads_log_ip"];
            if($arrOneLog["downloads_log_ip"] != "127.0.0.1" && $arrOneLog["downloads_log_ip"] != "::1") {
                $arrLogs[$intKey][5]	= getLinkAdminRaw($strUtraceLinkMap, "", $this->getLang("login_utrace_showmap", "user"), "icon_earth", "_blank")
                    . " " . getLinkAdminRaw($strUtraceLinkText, "", $this->getLang("login_utrace_showtext", "user"), "icon_text", "_blank");
            }
            else 
                $arrLogs[$intKey][5] = getImageAdmin("icon_earthDisabled", $this->getLang("login_utrace_noinfo", "user")) ." "
                    .getImageAdmin("icon_textDisabled", $this->getLang("login_utrace_noinfo", "user"));
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
        $strReturn .= $arrPageViews["pageview"];

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
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->getArrModule("modul"), "logbookFlush", "flush=1"));
            $strReturn .= $this->objToolkit->formTextRow($this->getLang("logbook_hint_date"));
            $strReturn .= $this->objToolkit->formDateSingle("date", $this->getLang("commons_date"), new class_date());
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
        }
        elseif ($this->getParam("flush") == "1") {
            //Build the date
            $objDate = new class_date();
            $objDate->generateDateFromParams("date", $this->getAllParams());

            if(!class_module_mediamanager_logbook::deleteFromLogs($objDate->getTimeInOldStyle()))
                throw new class_exception("Error deleting log-rows", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "logbook"));
        }
        return $strReturn;
    }
}

