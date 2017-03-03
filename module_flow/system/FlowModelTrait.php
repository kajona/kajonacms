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
     * Method which calculates the rights depending on the status of the model
     */
    public function calcPermissions()
    {
        $objConfig = FlowConfig::getByModelClass(get_class($this));
        if ($objConfig instanceof FlowConfig) {
            $objStatus = $objConfig->getStatusByIndex($this->getIntRecordStatus());
            if ($objStatus instanceof FlowStatus) {
                $objRights = Carrier::getInstance()->getObjRights();

                $arrSelfPermission = $objRights->getArrayRights($this->getSystemid());
                $arrSelfPermission[Rights::$STR_RIGHT_INHERIT] = 0;
                $arrSelfPermission[Rights::$STR_RIGHT_EDIT] = $this->buildPermissionRow($objStatus->getArrEditGroups());

                $objRights->setRights($arrSelfPermission, $this->getSystemid());
            }
        }
    }

    protected function buildPermissionRow($arrGroups) : string
    {
        return implode(",", $this->convertPermissionToShortIds($this->getPermissionGroupIds($arrGroups)));
    }

    protected function getPermissionGroupIds($arrGroups) : array
    {
        if (empty($arrGroups)) {
            return [];
        }

        $arrResult = [];
        foreach ($arrGroups as $objObject) {
            if ($objObject instanceof UserGroup) {
                $arrResult[] = $objObject->getSystemid();
            } elseif (is_string($objObject) && validateSystemid($objObject)) {
                $arrResult[] = $objObject;
            }
        }

        return $arrResult;
    }

    protected function convertPermissionToShortIds(array $arrGroups) : array
    {
        return array_map(function($strSystemId) {
            return UserGroup::getShortIdForGroupId($strSystemId);
        }, $arrGroups);
    }
}
