<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\Postacomment\Event;

use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistRestriction;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;


/**
 * Removes comments added to the passed systemid
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 */
class PostacommentRecorddeletedlistener implements GenericeventListenerInterface
{


    /**
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
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

        $bitReturn = true;
        //module installed?
        if ($strSourceClass == "Kajona\\Postacomment\\System\\PostacommentPost" || SystemModule::getModuleByName("postacomment") == null) {
            return true;
        }

        $objOrm = new OrmObjectlist();
        $objOrm->setObjHandleLogicalDeleted(OrmDeletedhandlingEnum::INCLUDED);
        $objOrm->addWhereRestriction(new OrmObjectlistRestriction(" AND (postacomment_page = ? OR  postacomment_systemid = ? ) ", array($strSystemid, $strSystemid)));
        $arrComments = $objOrm->getObjectList("Kajona\\Postacomment\\System\\PostacommentPost");

        foreach ($arrComments as $objPost) {

            if ($strEventName == SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY) {
                $objPost->deleteObject();
            }

            if ($strEventName == SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED) {
                $objPost->deleteObjectFromDatabase();
            }

        }

        return $bitReturn;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, new PostacommentRecorddeletedlistener());
        CoreEventdispatcher::getInstance()->removeAndAddListener(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new PostacommentRecorddeletedlistener());
    }

}

PostacommentRecorddeletedlistener::staticConstruct();