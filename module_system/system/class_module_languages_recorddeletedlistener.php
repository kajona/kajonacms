<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Removes a languageset-entry if the matching record is deleted
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 *
 */
class class_module_languages_recorddeletedlistener implements interface_genericevent_listener {


    /**
     * Searches for languagesets containing the current systemid. either as a language or a referenced record.
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
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

        if($strSourceClass == "class_module_languages_language") {
            //if we have just one language remaining, set this one as default
            $arrObjLanguages = class_module_languages_language::getObjectList();
            if(count($arrObjLanguages) == 1) {
                $objOneLanguage = $arrObjLanguages[0];
                $objOneLanguage->setBitDefault(1);
                $objOneLanguage->updateObjectToDb();
            }



            //check if the current active one was deleted. if, then reset. #kajona trace id 613
            $objLanguage = new class_module_languages_language();
            $arrLangs = class_module_languages_language::getObjectList();
            $arrFiltered = array_filter($arrLangs, function(class_module_languages_language $objSingleLang) use ($objLanguage) {
                return $objSingleLang->getStrName() == $objLanguage->getAdminLanguage();
            });

            if(count($arrFiltered) == 0 && count($arrLangs) > 0) {
                $objLanguage->setStrAdminLanguageToWorkOn($arrLangs[0]->getStrName());
            }
        }


        //fire a plain query on the database, much faster then searching for matching records
        $strQuery = "DELETE FROM "._dbprefix_."languages_languageset
                  WHERE languageset_language = ?
                     OR languageset_systemid = ?";

        class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid, $strSystemid));

        return true;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     * @return void
     */
    public static function staticConstruct() {
        class_core_eventdispatcher::getInstance()->removeAndAddListener(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED_LOGICALLY, new class_module_languages_recorddeletedlistener());
    }
}

class_module_languages_recorddeletedlistener::staticConstruct();