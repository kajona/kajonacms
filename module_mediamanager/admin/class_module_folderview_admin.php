<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * This class provides a list-view of the folders created in the database / filesystem.
 * Since Kajona 3.4.1 this class is deprecated. All methods have been moved to the appropriate source-modules.
 * It only remains as a switch between different browsers.
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @deprecated
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_folderview_admin extends class_admin_controller implements interface_admin {

    /**
     * Constructor, doing nothing but a few inits
     */
    public function __construct() {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        parent::__construct();
        $this->setStrLangBase("mediamanager");

    }


    /**
     * @return string
     */
    protected function getOutputModuleTitle() {
        return $this->getLang("moduleFolderviewTitle");
    }

    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionBrowserChooser() {
        $strReturn = "";

        if($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = " . (int)$this->getParam("CKEditorFuncNum") . ";</script>";
        }

        $intCounter = 1;
        $strReturn .= $this->objToolkit->listHeader();

        if(class_module_system_module::getModuleByName("pages") !== null) {
            $strAction = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    "pages",
                    "pagesFolderBrowser",
                    "&pages=1&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygPagesBrowser"),
                    $this->getLang("wysiwygPagesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygPagesBrowser"), "", $strAction, $intCounter++);
        }

        $strRepoId = class_module_system_setting::getConfigValue("_mediamanager_default_filesrepoid_");
        if(validateSystemid($strRepoId) && class_module_system_module::getModuleByName("mediamanager") !== null && class_objectfactory::getInstance()->getObject($strRepoId) !== null) {
            $strAction = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&systemid=" .$strRepoId. "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygFilesBrowser"),
                    $this->getLang("wysiwygFilesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygFilesBrowser"), "", $strAction, $intCounter++);
        }

        $strRepoId = class_module_system_setting::getConfigValue("_mediamanager_default_imagesrepoid_");
        if(validateSystemid($strRepoId) && class_module_system_module::getModuleByName("mediamanager") !== null && class_objectfactory::getInstance()->getObject($strRepoId) !== null) {
            $strAction = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&systemid=" .$strRepoId. "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygImagesBrowser"),
                    $this->getLang("wysiwygImagesBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygImagesBrowser"), "", $strAction, $intCounter++);
        }

        if(class_module_system_module::getModuleByName("mediamanager") !== null) {
            $strAction = $this->objToolkit->listButton(
                class_link::getLinkAdmin(
                    "mediamanager",
                    "folderContentFolderviewMode",
                    "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                    $this->getLang("wysiwygRepoBrowser"),
                    $this->getLang("wysiwygRepoBrowser"),
                    "icon_folderActionOpen"
                )
            );
            $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygRepoBrowser"), "", $strAction, $intCounter++);
        }

        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }

}
