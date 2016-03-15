<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Faqs\Event;

use Kajona\System\System\Carrier;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\SystemEventidentifier;


/**
 * Removes category-assignments on record-deletions
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 */
class FaqsRecorddeletedlistener implements GenericeventListenerInterface
{


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        if ($strSourceClass == "Kajona\\Faqs\\System\\FaqsCategory") {
            return Carrier::getInstance()->getObjDB()->_pQuery("DELETE FROM " . _dbprefix_ . "faqs_member WHERE faqsmem_category = ? ", array($strSystemid));
        }
        return true;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new FaqsRecorddeletedlistener());
    }


}

FaqsRecorddeletedlistener::staticConstruct();