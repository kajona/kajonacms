<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Admin class of the demo-module.
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 *
 * @objectList class_module_demo_demo
 * @objectNew class_module_demo_demo
 * @objectEdit class_module_demo_demo
 * @objectListOtherObject class_module_demo_other_object
 * @objectNewOtherObject class_module_demo_other_object
 * @objectEditOtherObject class_module_demo_other_object
 * @objectListSubObject class_module_demo_sub_object
 * @objectNewSubObject class_module_demo_sub_object
 * @objectEditSubObject class_module_demo_sub_object
 *
 * @autoTestable list,new,listOtherObject,newOtherObject,listSubObject,newSubObject
 *
 * @module demo
 * @moduleId _demo_module_id_
 */
class class_module_demo_admin extends class_admin_evensimpler implements interface_admin {

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("edit", getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("module_action_new"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "listOtherObject", "", $this->getLang("list_other_object"), "", "", true, "adminnavi"));
        return $arrReturn;
    }



    /**
     * Returns an additional set of action-buttons rendered right after the edit-action.
     *
     * @param class_model $objListEntry
     * @return array
     */
    protected function renderAdditionalActions(class_model $objListEntry) {

        $bitPeMode = $this->getParam("pe") != "";
        if($objListEntry instanceof class_module_demo_demo) {
            $arrReturn[] = $this->objToolkit->listButton(
                getLinkAdmin("demo", "listSubObject", "&systemid=".$objListEntry->getSystemid()."&pe=".$this->getParam("pe"), "", $this->getLang("liste_subtyp"), "icon_excel")
            );
            return $arrReturn;
        }
        else
            return parent::renderAdditionalActions($objListEntry);

    }


}

