<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Admin-GUI of the packageserver.
 * Provides all interfaces to manage the packages available for other systems
 *
 * @package module_packageserver
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packageserver_admin extends class_module_mediamanager_admin implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setStrLangBase("packageserver");

        $this->setArrModuleEntry("modul", "packageserver");
        $this->setArrModuleEntry("moduleId", _packageserver_module_id_);
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("actionList"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "logs", "", $this->getLang("actionListLogs"), "", "", true, "adminnavi"));

        return $arrReturn;
    }



    /**
     * Generic list of all packages available on the local filesystem
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {

        if($this->getSystemid() == "")
            $this->setSystemid(_packageserver_repo_id_);

        $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount(_packageserver_repo_id_));
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_mediamanager_file::loadFilesDB(_packageserver_repo_id_));

        return $this->renderList($objIterator);
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        return "";
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_mediamanager_file) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->getArrModule("modul"), "showInfo", "&systemid=".$objListEntry->getSystemid(), $this->getLang("package_info"), $this->getLang("package_info"), "icon_folderActionOpen.gif")
                )
            );
        }
        return parent::renderAdditionalActions($objListEntry);
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->getLang("commons_error_permissions");
    }

    /**
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        return $this->getLang("commons_error_permissions");
    }

}
