<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\Carrier;
use Kajona\System\System\Rights;
use Kajona\System\System\UserGroup;

/**
 * @author christoph.kappestein@artemeon.de
 * @module flow
 */
trait FlowModelTrait
{
    /**
     * Method which should be called in the updateObjectToDb to change the user groups according to the current status
     */
    protected function calcPermissions()
    {
        $objConfig = FlowConfig::getByModelClass(get_class($this));
        if ($objConfig instanceof FlowConfig) {
            $objStatus = $objConfig->getStatusByIndex($this->getIntRecordStatus());
            if ($objStatus instanceof FlowStatus) {
                $objRights = Carrier::getInstance()->getObjRights();
                $arrSelfPermission = [];
                $arrSelfPermission[Rights::$STR_RIGHT_INHERIT] = 0;
                $arrSelfPermission[Rights::$STR_RIGHT_VIEW] = $this->getShortIdArrayFromUserGroups($objStatus->getArrViewGroups());
                $arrSelfPermission[Rights::$STR_RIGHT_EDIT] = $this->getShortIdArrayFromUserGroups($objStatus->getArrEditGroups());
                $arrSelfPermission[Rights::$STR_RIGHT_DELETE] = $this->getShortIdArrayFromUserGroups($objStatus->getArrDeleteGroups());
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT] = $this->getShortIdArrayFromUserGroups($objStatus->getArrRightGroups());
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT1] = "";
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT2] = "";
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT3] = "";
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT4] = "";
                $arrSelfPermission[Rights::$STR_RIGHT_RIGHT5] = "";
                $arrSelfPermission[Rights::$STR_RIGHT_CHANGELOG] = "";

                $objRights->setRights($arrSelfPermission, $this->getSystemid());
            }
        }
    }

    private function getShortIdArrayFromUserGroups($arrData)
    {
        if (empty($arrData)) {
            return "";
        }

        $arrResult = [];
        foreach ($arrData as $objObject) {
            if ($objObject instanceof UserGroup) {
                $arrResult[] = UserGroup::getShortIdForGroupId($objObject->getSystemid());
            }
        }
        return implode(",", $arrResult);
    }
}
