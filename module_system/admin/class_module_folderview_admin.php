<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                            *
********************************************************************************************************/


/**
 * This class provides a list-view of the folders created in the database / filesystem.
 * Since Kajona 3.4.1 this class is deprecated. All methods have been moved to the appropriate source-modules.
 * It only remains as a switch between different browsers.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @deprecated
 */
class class_module_folderview_admin extends class_admin implements interface_admin {

    /**
     * Constructor, doing nothing but a few inits

     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "folderview");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);
        $this->setArrModuleEntry("template", "/folderview.tpl");
        parent::__construct();
        $this->setStrLangBase("mediamanager");

    }


    protected function getOutputModuleTitle() {
        return $this->getLang("moduleFolderviewTitle");
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionBrowserChooser() {
        $strReturn = "";

        if($this->getParam("CKEditorFuncNum") != "") {
            $strReturn .= "<script type=\"text/javascript\">window.opener.KAJONA.admin.folderview.selectCallbackCKEditorFuncNum = " . (int)$this->getParam("CKEditorFuncNum") . ";</script>";
        }

        $intCounter = 1;
        $strReturn .= $this->objToolkit->listHeader();

        $strAction = $this->objToolkit->listButton(
            getLinkAdmin(
                "pages",
                "pagesFolderBrowser",
                "&pages=1&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                $this->getLang("wysiwygPagesBrowser"),
                $this->getLang("wysiwygPagesBrowser"),
                "icon_folderActionOpen.png"
            )
        );
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygPagesBrowser"), "", $strAction, $intCounter++);

        $strAction = $this->objToolkit->listButton(
            getLinkAdmin(
                "mediamanager",
                "folderContentFolderviewMode",
                "&systemid=" . _mediamanager_default_filesrepoid_ . "&form_element=" . $this->getParam("form_element") . "&bit_link=1",
                $this->getLang("wysiwygFilesBrowser"),
                $this->getLang("wysiwygFilesBrowser"),
                "icon_folderActionOpen.png"
            )
        );
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang("wysiwygFilesBrowser"), "", $strAction, $intCounter++);

        $strReturn .= $this->objToolkit->listFooter();
        return $strReturn;
    }

}
