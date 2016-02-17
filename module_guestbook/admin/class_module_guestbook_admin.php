<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
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
 *
 * @autoTestable list,new
 *
 * @module guestbook
 * @moduleId _guestbook_module_id_
 */
class class_module_guestbook_admin extends class_admin_evensimpler implements interface_admin {


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($this->getObjModule()->rightEdit() && $this->getStrCurObjectTypeName() != "Post") {
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        }
        return "";
    }

    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry) {
        if($objListEntry instanceof class_module_guestbook_guestbook) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->arrModule["modul"], "listPost", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("action_view_guestbook"), "icon_bookLens")
                )
            );
        }
    }

}

