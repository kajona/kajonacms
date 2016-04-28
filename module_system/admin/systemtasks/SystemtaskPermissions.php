<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Admin\Systemtasks;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryCheckbox;
use Kajona\System\Admin\Formentries\FormentryDropdown;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\System\Carrier;
use Kajona\System\System\Rights;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemModule;
use Kajona\System\System\UserGroup;


/**
 * A systemtask to set the permissions recursively
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.7
 */
class SystemtaskPermissions extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "permissions";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_permissions_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {
        if (!SystemModule::getModuleByName("system")->rightRight2()) {
            return $this->getLang("commons_error_permissions");
        }

        //try to load and update the systemrecord

        $arrPermissions = array();
        $arrPermissions[Rights::$STR_RIGHT_VIEW] = $this->getParam(Rights::$STR_RIGHT_VIEW) != "";
        $arrPermissions[Rights::$STR_RIGHT_EDIT] = $this->getParam(Rights::$STR_RIGHT_EDIT) != "";
        $arrPermissions[Rights::$STR_RIGHT_DELETE] = $this->getParam(Rights::$STR_RIGHT_DELETE) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT] = $this->getParam(Rights::$STR_RIGHT_RIGHT) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT1] = $this->getParam(Rights::$STR_RIGHT_RIGHT1) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT2] = $this->getParam(Rights::$STR_RIGHT_RIGHT2) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT3] = $this->getParam(Rights::$STR_RIGHT_RIGHT3) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT4] = $this->getParam(Rights::$STR_RIGHT_RIGHT4) != "";
        $arrPermissions[Rights::$STR_RIGHT_RIGHT5] = $this->getParam(Rights::$STR_RIGHT_RIGHT5) != "";
        $arrPermissions[Rights::$STR_RIGHT_CHANGELOG] = $this->getParam(Rights::$STR_RIGHT_CHANGELOG) != "";

        $this->updateRecord($this->getParam("recordid"), $this->getParam("groupid"), $arrPermissions, true);
        return $this->getLang("systemtask_permissions_finished");
    }

    /**
     * @param $strSystemid
     * @param $strGroupId
     * @param $arrPermissions
     * @param bool $bitForce
     */
    private function updateRecord($strSystemid, $strGroupId, $arrPermissions, $bitForce = false)
    {
        $objRights = Carrier::getInstance()->getObjRights();
        $objCommon = new SystemCommon();

        foreach ($arrPermissions as $strPermission => $bitIsGiven) {

            if (!$objRights->isInherited($strSystemid) || $bitForce) {

                if ($bitIsGiven) {
                    $objRights->addGroupToRight($strGroupId, $strSystemid, $strPermission);
                }
                else {
                    $objRights->removeGroupFromRight($strGroupId, $strSystemid, $strPermission);
                }
            }
        }

        foreach ($objCommon->getChildNodesAsIdArray($strSystemid) as $strOneId) {
            $this->updateRecord($strOneId, $strGroupId, $arrPermissions);
        }
    }

    /**
     * @inheritdoc
     */
    public function getAdminForm()
    {

        $strFormName = "permissions";
        $objForm = new AdminFormgenerator($strFormName, new SystemCommon());

        $arrGroups = array();
        foreach (UserGroup::getObjectListFiltered() as $objOneGroup) {
            $arrGroups[$objOneGroup->getSystemid()] = $objOneGroup->getStrDisplayName();
        }

        $objForm->addField(new FormentryPlaintext())->setStrValue($this->objToolkit->warningBox($this->getLang("systemtask_permissions_hint")));
        $objForm->addField(new FormentryDropdown("", "groupid"))->setStrLabel($this->getLang("systemtask_permissions_groupid"))->setBitMandatory(true)->setArrKeyValues($arrGroups);
        $objForm->addField(new FormentryText("", "recordid"))->setStrLabel($this->getLang("systemtask_permissions_systemid"))->setBitMandatory(true);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_VIEW))->setStrLabel(Rights::$STR_RIGHT_VIEW);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_EDIT))->setStrLabel(Rights::$STR_RIGHT_EDIT);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_DELETE))->setStrLabel(Rights::$STR_RIGHT_DELETE);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT))->setStrLabel(Rights::$STR_RIGHT_RIGHT);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT1))->setStrLabel(Rights::$STR_RIGHT_RIGHT1);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT2))->setStrLabel(Rights::$STR_RIGHT_RIGHT2);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT3))->setStrLabel(Rights::$STR_RIGHT_RIGHT3);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT4))->setStrLabel(Rights::$STR_RIGHT_RIGHT4);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_RIGHT5))->setStrLabel(Rights::$STR_RIGHT_RIGHT5);
        $objForm->addField(new FormentryCheckbox("", Rights::$STR_RIGHT_CHANGELOG))->setStrLabel(Rights::$STR_RIGHT_CHANGELOG);

        return $objForm;

    }

    /**
     * @inheritdoc
     */
    public function getSubmitParams()
    {

        $strParams = "";
        foreach (
            array(
                Rights::$STR_RIGHT_VIEW,
                Rights::$STR_RIGHT_EDIT,
                Rights::$STR_RIGHT_DELETE,
                Rights::$STR_RIGHT_RIGHT,
                Rights::$STR_RIGHT_RIGHT1,
                Rights::$STR_RIGHT_RIGHT2,
                Rights::$STR_RIGHT_RIGHT3,
                Rights::$STR_RIGHT_RIGHT4,
                Rights::$STR_RIGHT_RIGHT5,
                Rights::$STR_RIGHT_CHANGELOG
            ) as $strOnePermission) {
            $strParams .= "&".$strOnePermission."=".$this->getParam($strOnePermission);
        }

        return "&groupid=".$this->getParam("groupid")."&recordid=".$this->getParam("recordid").$strParams;
    }
}
