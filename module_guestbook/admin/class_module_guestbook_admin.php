<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Admin class to handle all guestbook-stuff like creating guestbook, deleting posts, ...
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 *
 * @objectList class_module_guestbook_guestbook
 * @objectNew class_module_guestbook_guestbook
 * @objectEdit class_module_guestbook_guestbook
 *
 * @objectListPost class_module_guestbook_post
 * @objectEditPost class_module_guestbook_post
 */
class class_module_guestbook_admin extends class_admin_evensimpler implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("moduleId", _guestbook_module_id_);
        $this->setArrModuleEntry("modul", "guestbook");
        parent::__construct();
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getLang("actionNew"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=" . $this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($this->getObjModule()->rightEdit() && $this->getStrCurObjectTypeName() != "Post") {
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        }
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_guestbook_guestbook) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->arrModule["modul"], "listPost", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("actionViewGuestbook"), "icon_bookLens.png")
                )
            );
        }
    }

}

