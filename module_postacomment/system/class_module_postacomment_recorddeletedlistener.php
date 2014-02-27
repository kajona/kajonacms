<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Removes comments added to the passed systemid
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 */
class class_module_postacomment_recorddeletedlistener implements interface_genericevent_listener {


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
    public function handleEvent($strEventName, array $arrArguments) {
        //unwrap arguments
        list($strSystemid, $strSourceClass) = $arrArguments;

        $bitReturn = true;
        //module installed?
        if($strSourceClass == "class_module_postacomment_post" || class_module_system_module::getModuleByName("postacomment") == null)
            return true;

        //ok, so search for a records matching
        $arrPosts1 = class_module_postacomment_post::loadPostList(false, $strSystemid);
        $arrPosts2 = class_module_postacomment_post::loadPostList(false, "", $strSystemid);

        //and delete
        foreach($arrPosts1 as $objOnePost) {
            $bitReturn = $bitReturn && $objOnePost->deleteObject();
        }

        foreach($arrPosts2 as $objOnePost) {
            $bitReturn = $bitReturn && $objOnePost->deleteObject();
        }

        return $bitReturn;
    }

    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, new class_module_postacomment_recorddeletedlistener());
    }

}

class_module_postacomment_recorddeletedlistener::staticConstruct();