<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The core eventmanager is used to trigger and fire internal events such as status-changed or record-deleted-events.
 * Therefore the corresponding interface-implementers are called and notified.
 *
 * Since version 4.4, the eventdispatcher is totally generic. Hardcoded dependencies are no longer required.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_core_eventdispatcher {

    /**
     * @var class_model[]
     */
    private static $arrListeners = array();


    /**
     * Returns all classes (here: instances of the class) implementing a given interface
     * @param string $strInterface
     *
     * @return class_model
     */
    public static function getEventListeners($strInterface) {
        if(!isset(self::$arrListeners[$strInterface]))
            self::$arrListeners[$strInterface] = self::loadInterfaceImplementers($strInterface);

        return self::$arrListeners[$strInterface];
    }


    /**
     * Generic function to notify a set of event-listeners.
     * Therefore the implementing interface, the name of the notify-method and an array of arguments sent to the handler
     * are required.
     *
     * @param string $strInterface
     * @param string $strMethodname
     * @param array $arrArguments
     *
     * @return bool
     */
    public static function notifyListeners($strInterface, $strMethodname,  $arrArguments) {
        if(!isset(self::$arrListeners[$strInterface]))
            self::$arrListeners[$strInterface] = self::loadInterfaceImplementers($strInterface);

        $bitReturn = true;
        foreach(self::$arrListeners[$strInterface] as $objOneListener) {
            $bitReturn = $bitReturn && call_user_func_array(array($objOneListener, $strMethodname), $arrArguments);
        }

        return $bitReturn;
    }


    /**
     * Triggers all model-classes implementing the interface interface_statuschanged_listener and notifies
     * about a new status set.
     *
     * @param string $strSystemid
     * @param int $intNewStatus
     *
     * @return bool
     * @static
     * @see interface_statuschanged_listener
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyStatusChangedListeners($strSystemid, $intNewStatus) {
        return self::notifyListeners("interface_statuschanged_listener", "handleStatusChangedEvent", array($strSystemid, $intNewStatus));
    }

    /**
     * Triggers all model-classes implementing the interface interface_recorddeleted_listener and notifies them about a
     * deleted record.
     *
     * @param string $strSystemid
     * @param string $strClass
     *
     * @return bool
     *
     * @see interface_recorddeleted_listener
     * @static
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyRecordDeletedListeners($strSystemid, $strClass) {
        return self::notifyListeners("interface_recorddeleted_listener", "handleRecordDeletedEvent", array($strSystemid, $strClass));
    }


    /**
     * Triggers all model-classes implementing the interface interface_previdchanged_listener and notifies them about a
     * deleted record.
     *
     * @param string $strSystemid
     * @param string $strOldPrevid
     * @param string $strNewPrevid
     *
     * @static
     * @return bool
     * @see interface_previdchanged_listener
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyPrevidChangedListeners($strSystemid, $strOldPrevid, $strNewPrevid) {
        return self::notifyListeners("interface_previdchanged_listener", "handlePrevidChangedEvent", array($strSystemid, $strOldPrevid, $strNewPrevid));
    }


    /**
     * Triggers all model-classes implementing the interface interface_recordcopied_listener and notifies them about a
     * copied record.
     *
     * @param string $strOldSystemid
     * @param string $strNewSystemid
     *
     * @static
     * @return bool
     * @see interface_recordcopied_listener
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyRecordCopiedListeners($strOldSystemid, $strNewSystemid) {
        return self::notifyListeners("interface_recordcopied_listener", "handleRecordCopiedEvent", array($strOldSystemid, $strNewSystemid));
    }


    /**
     * Triggers all model-classes implementing the interface interface_userfirstlogin_listener and notifies them about a
     * users first login.
     *
     * @param string $strUserid
     *
     * @static
     * @return bool
     * @see interface_userfirstlogin_listener
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyUserFirstLoginListeners($strUserid) {
        return self::notifyListeners("interface_userfirstlogin_listener", "handleUserFirstLoginEvent", array($strUserid));
    }


    /**
     * Loads all business-objects implementing the passed interface
     *
     * @param string $strTargetInterface
     *
     * @static
     * @return array
     */
    private static function loadInterfaceImplementers($strTargetInterface) {
        //load classes in system-folders
        return class_resourceloader::getInstance()->getFolderContent("/system", array(".php"), false, function(&$strOneFile) use ($strTargetInterface) {
            if(uniStripos($strOneFile, "class_module_") !== false) {
                $objReflection = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
                if(!$objReflection->isAbstract() && $objReflection->implementsInterface($strTargetInterface)) {
                    $strOneFile = $objReflection->newInstance();
                    return true;
                }
            }
            return false;
        });

    }
}

