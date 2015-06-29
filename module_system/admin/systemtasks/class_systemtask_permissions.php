<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A systemtask to set the permissions recursively
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class class_systemtask_permissions extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "permissions";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_permissions_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        //try to load and update the systemrecord

        $arrPermissions = array();
        $arrPermissions[class_rights::$STR_RIGHT_VIEW     ] = $this->getParam(class_rights::$STR_RIGHT_VIEW     ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_EDIT     ] = $this->getParam(class_rights::$STR_RIGHT_EDIT     ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_DELETE   ] = $this->getParam(class_rights::$STR_RIGHT_DELETE   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT    ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT    ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT1   ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT1   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT2   ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT2   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT3   ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT3   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT4   ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT4   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_RIGHT5   ] = $this->getParam(class_rights::$STR_RIGHT_RIGHT5   ) != "";
        $arrPermissions[class_rights::$STR_RIGHT_CHANGELOG] = $this->getParam(class_rights::$STR_RIGHT_CHANGELOG) != "";

        $this->updateRecord($this->getParam("recordid"), $this->getParam("groupid"), $arrPermissions, true);
        return $this->getLang("systemtask_permissions_finished");
    }

    /**
     * @param $strSystemid
     * @param $strGroupId
     * @param $arrPermissions
     * @param bool $bitForce
     */
    private function updateRecord($strSystemid, $strGroupId, $arrPermissions, $bitForce = false) {
        $objRights = class_carrier::getInstance()->getObjRights();
        $objCommon = new class_module_system_common();

        foreach($arrPermissions as $strPermission => $bitIsGiven) {

            if(!$objRights->isInherited($strSystemid) || $bitForce) {

                if($bitIsGiven) {
                    $objRights->addGroupToRight($strGroupId, $strSystemid, $strPermission);
                }
                else {
                    $objRights->removeGroupFromRight($strGroupId, $strSystemid, $strPermission);
                }
            }
        }

        foreach($objCommon->getChildNodesAsIdArray($strSystemid) as $strOneId) {
            $this->updateRecord($strOneId, $strGroupId, $arrPermissions);
        }
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {

        $strFormName = "permissions";
        $objForm = new class_admin_formgenerator($strFormName, new class_module_system_common());

        $arrGroups = array();
        foreach(class_module_user_group::getObjectList() as $objOneGroup) {
            $arrGroups[$objOneGroup->getSystemid()] = $objOneGroup->getStrDisplayName();
        }

        $objForm->addField(new class_formentry_plaintext())->setStrValue($this->objToolkit->warningBox($this->getLang("systemtask_permissions_hint")));
        $objForm->addField(new class_formentry_dropdown("", "groupid"))->setStrLabel($this->getLang("systemtask_permissions_groupid"))->setBitMandatory(true)->setArrKeyValues($arrGroups);
        $objForm->addField(new class_formentry_text("", "recordid"))->setStrLabel($this->getLang("systemtask_permissions_systemid"))->setBitMandatory(true);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_VIEW     ))->setStrLabel(class_rights::$STR_RIGHT_VIEW);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_EDIT     ))->setStrLabel(class_rights::$STR_RIGHT_EDIT);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_DELETE   ))->setStrLabel(class_rights::$STR_RIGHT_DELETE);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT    ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT1   ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT1);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT2   ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT2);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT3   ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT3);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT4   ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT4);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_RIGHT5   ))->setStrLabel(class_rights::$STR_RIGHT_RIGHT5);
        $objForm->addField(new class_formentry_checkbox("", class_rights::$STR_RIGHT_CHANGELOG))->setStrLabel(class_rights::$STR_RIGHT_CHANGELOG);

        return $objForm;


    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams() {

        $strParams = "";
        foreach(
            array(
                class_rights::$STR_RIGHT_VIEW,
                class_rights::$STR_RIGHT_EDIT,
                class_rights::$STR_RIGHT_DELETE,
                class_rights::$STR_RIGHT_RIGHT,
                class_rights::$STR_RIGHT_RIGHT1,
                class_rights::$STR_RIGHT_RIGHT2,
                class_rights::$STR_RIGHT_RIGHT3,
                class_rights::$STR_RIGHT_RIGHT4,
                class_rights::$STR_RIGHT_RIGHT5,
                class_rights::$STR_RIGHT_CHANGELOG
            ) as $strOnePermission) {
            $strParams .= "&".$strOnePermission."=".$this->getParam($strOnePermission);
        }

        return "&groupid=".$this->getParam("groupid")."&recordid=".$this->getParam("recordid").$strParams;
    }
}
