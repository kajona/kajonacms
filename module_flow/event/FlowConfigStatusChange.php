<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\Flow\Event;

use Kajona\Flow\System\FlowConfig;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;


/**
 * Unlocks all records currently locked by the user.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class FlowConfigStatusChange implements GenericeventListenerInterface
{
    /**
     * @param string $strEventName
     * @param array $arrArguments
     */
    public function handleEvent($strEventName, array $arrArguments)
    {
        list($strSystemid, $objObject, $intOldStatus, $intNewStatus) = $arrArguments;

        if ($objObject instanceof FlowConfig && $intNewStatus === 1) {
            // set all other handler which have the same target class to 0
            $arrConfig = FlowConfig::getObjectListFiltered();
            foreach ($arrConfig as $objConfig) {
                if ($objConfig->getStrTargetClass() == $objObject->getStrTargetClass() && $objConfig->getSystemid() != $objObject->getSystemid() && $objConfig->getIntRecordStatus() == 1) {
                    $objConfig->setIntRecordStatus(0);
                    $objConfig->updateObjectToDb();
                }
            }
        }
    }

    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_STATUSCHANGED, new self());
    }
}

FlowConfigStatusChange::staticConstruct();
