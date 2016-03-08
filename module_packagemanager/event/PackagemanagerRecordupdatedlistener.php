<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packagemanager\Event;

use Kajona\Packagemanager\System\PackagemanagerTemplate;
use Kajona\System\System\Cache;
use Kajona\System\System\CacheManager;
use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistSystemstatusRestriction;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemSetting;


/**
 * Updates the default templatepack
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 */
class PackagemanagerRecordupdatedlistener implements GenericeventListenerInterface
{


    /**
     * Triggered as soon as a record is updated
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {

        $objRecord = $arrArguments[0];

        if ($objRecord instanceof PackagemanagerTemplate) {

            if ($objRecord->getIntRecordStatus() == 1) {

                $objOrm = new OrmObjectlist();
                $objOrm->addWhereRestriction(new OrmObjectlistSystemstatusRestriction(OrmComparatorEnum::Equal(), 1));
                $arrPacks = $objOrm->getObjectList("Kajona\\Packagemanager\\System\\PackagemanagerTemplate");

                foreach ($arrPacks as $objPack) {
                    if ($objPack->getSystemid() != $objRecord->getSystemid()) {
                        $objPack->setIntRecordStatus(0);
                        $objPack->updateObjectToDb();
                    }
                }

                //update the active-pack constant
                $objSetting = SystemSetting::getConfigByName("_packagemanager_defaulttemplate_");
                if ($objSetting !== null) {
                    $objSetting->setStrValue($objRecord->getStrName());
                    $objSetting->updateObjectToDb();
                }

                /** @var CacheManager $objCache */
                $objCache = Carrier::getInstance()->getContainer()->offsetGet("cache_manager");
                $objCache->flushCache();
            }
        }

        return true;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDUPDATED, new PackagemanagerRecordupdatedlistener());
    }


}

//register the listener
PackagemanagerRecordupdatedlistener::staticConstruct();