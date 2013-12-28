<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_module_languages_recorddeletedlistener implements interface_recorddeleted_listener {


    /**
     * Searches for languagesets containing the current systemid. either as a language or a referenced record.
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param string $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        //fire a plain query on the database, much faster then searching for matching records
        $strQuery = "DELETE FROM " . _dbprefix_ . "languages_languageset
                      WHERE languageset_language = ?
                         OR languageset_systemid = ?";

        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid, $strSystemid));
    }


}
