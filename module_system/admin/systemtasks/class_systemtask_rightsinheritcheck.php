<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/


/**
 * Checks the permission tree to find nodes breaking the inheritance but defining exactly the same
 * permissions as their parent node.
 *
 * @package module_system
 */
class class_systemtask_rightsinheritcheck extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * @see interface_admin_systemtask::getGroupIdentifier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "database";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "rightsinheritcheck";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getLang("systemtask_rightsinheritcheck_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {

        if(!class_module_system_module::getModuleByName("system")->rightRight2())
            return $this->getLang("commons_error_permissions");

        $arrReturn = array();
        $this->checkSingleLevel("0", $arrReturn);

        $strReturn = $this->objToolkit->warningBox($this->getLang("systemtask_rightsinheritcheck_intro"));

        if(count($arrReturn) > 0) {
            $strReturn .= $this->objToolkit->listHeader();
            foreach($arrReturn as $objOneEntry) {
                $strReturn .= $this->objToolkit->genericAdminList($objOneEntry->getSystemid(), $objOneEntry->getStrDisplayName(), "", "", 0, $objOneEntry->getSystemid(), get_class($objOneEntry));
            }
            $strReturn .= $this->objToolkit->listFooter();
        }
        else {
            $strReturn .= $this->objToolkit->warningBox($this->getLang("systemtask_rightsinheritcheck_empty"), "alert-info");
        }

        return $strReturn;
    }

    private function checkSingleLevel($strParentId, &$arrReturn) {
        $objRights = class_carrier::getInstance()->getObjRights();

        $arrParentRights = $objRights->getArrayRights($strParentId);

        //load the sub-ordinate nodes
        $objCommon = new class_module_system_common();
        $arrChildNodes = $objCommon->getChildNodesAsIdArray($strParentId);

        foreach($arrChildNodes as $strOneChildId) {
            if(!$objRights->isInherited($strOneChildId)) {
                $arrChildRights = $objRights->getArrayRights($strOneChildId);

                $bitIsDifferent = false;
                foreach($arrChildRights as $strPermission => $arrOneChildPermission) {

                    if($strPermission == class_rights::$STR_RIGHT_INHERIT)
                        continue;

                    if(count(array_diff($arrChildRights[$strPermission], $arrParentRights[$strPermission])) != 0) {
                        $bitIsDifferent = true;
                        break;
                    }
                }

                if(!$bitIsDifferent) {
                    $arrReturn[] = class_objectfactory::getInstance()->getObject($strOneChildId);
                    $objRights->setInherited(true, $strOneChildId);
                }
            }

            $this->checkSingleLevel($strOneChildId, $arrReturn);
        }
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm() {
        return "";
    }

}
