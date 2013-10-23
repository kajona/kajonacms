<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The core eventmanager is used to trigger and fire internal events such as status-changed or record-deleted-events.
 * Therefore the corresponding interface-implementers are called and notified.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_core_eventdispatcher {

    /**
     * @var interface_statuschanged_listener
     */
    private static $arrStatusChangedListener = null;

    /**
     * @var interface_recorddeleted_listener
     */
    private static $arrRecordDeletedListener = null;

    /**
     * @var interface_previdchanged_listener
     */
    private static $arrPrevidChangedListener = null;

    /**
     * @var interface_recordcopied_listener
     */
    private static $arrRecordCopiedListener = null;

    /**
     * @var interface_userfirstlogin_listener
     */
    private static $arrFirstLoginListener = null;

    /**
     * Triggers all model-classes implementing the interface interface_statuschanged_listener and notifies
     * about a new status set.
     *
     * @static
     * @param $strSystemid
     * @param $intNewStatus
     * @return bool
     *
     * @see interface_statuschanged_listener
     */
    public static function notifyStatusChangedListeners($strSystemid, $intNewStatus) {
        $bitReturn = true;
        $arrListener = self::getStatusChangedListeners();
        /** @var interface_statuschanged_listener $objOneListener */
        foreach($arrListener as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating statusChangedEvent to ".get_class($objOneListener)." sysid: ".$strSystemid." status: ".$intNewStatus, class_logger::$levelInfo);
            $bitReturn = $bitReturn && $objOneListener->handleStatusChangedEvent($strSystemid, $intNewStatus);
        }

        return $bitReturn;
    }

    /**
     * Triggers all model-classes implementing the interface interface_recorddeleted_listener and notifies them about a
     * deleted record.
     *
     * @static
     *
     * @param $strSystemid
     * @param $strClass
     *
     * @return bool
     *
     * @see interface_recorddeleted_listener
     */
    public static function notifyRecordDeletedListeners($strSystemid, $strClass) {
        $bitReturn = true;
        $arrListener = self::getRecordDeletedListeners();
        /** @var interface_recorddeleted_listener $objOneListener */
        foreach($arrListener as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating recordDeletedEvent to ".get_class($objOneListener)." sysid: ".$strSystemid, class_logger::$levelInfo);
            $bitReturn = $bitReturn && $objOneListener->handleRecordDeletedEvent($strSystemid, $strClass);
        }

        return $bitReturn;
    }


    /**
     * Triggers all model-classes implementing the interface interface_previdchanged_listener and notifies them about a
     * deleted record.
     *
     * @static
     *
     * @param $strSystemid
     * @param $strOldPrevid
     * @param $strNewPrevid
     *
     * @return bool
     * @see interface_previdchanged_listener
     */
    public static function notifyPrevidChangedListeners($strSystemid, $strOldPrevid, $strNewPrevid) {
        $bitReturn = true;
        $arrListener = self::getPrevidChangedListeners();
        /** @var interface_previdchanged_listener $objOneListener */
        foreach($arrListener as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating previdChangedEvent to ".get_class($objOneListener)." sysid: ".$strSystemid, class_logger::$levelInfo);
            $bitReturn = $bitReturn && $objOneListener->handlePrevidChangedEvent($strSystemid, $strOldPrevid, $strNewPrevid);
        }

        return $bitReturn;
    }


    /**
     * Triggers all model-classes implementing the interface interface_recordcopied_listener and notifies them about a
     * copied record.
     *
     * @static
     *
     * @param $strOldSystemid
     * @param $strNewSystemid
     *
     * @return bool
     * @see interface_recordcopied_listener
     */
    public static function notifyRecordCopiedListeners($strOldSystemid, $strNewSystemid) {
        $bitReturn = true;
        $arrListener = self::getRecordCopiedListeners();
        /** @var interface_recordcopied_listener $objOneListener */
        foreach($arrListener as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating recordCopiedEvent to ".get_class($objOneListener)." oldsysid: ".$strOldSystemid." newsysid: ".$strNewSystemid, class_logger::$levelInfo);
            $bitReturn = $bitReturn && $objOneListener->handleRecordCopiedEvent($strOldSystemid, $strNewSystemid);
        }

        return $bitReturn;
    }


    /**
     * Triggers all model-classes implementing the interface interface_userfirstlogin_listener and notifies them about a
     * users first login.
     *
     * @static
     *
     * @param $strUserid
     *
     * @return bool
     * @see interface_userfirstlogin_listener
     */
    public static function notifyUserFirstLoginListeners($strUserid) {
        $bitReturn = true;
        $arrListener = self::getUserFirstLoginListeners();
        foreach($arrListener as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating userFirstLoginEvent to ".get_class($objOneListener)." userid: ".$strUserid, class_logger::$levelInfo);
            $bitReturn = $bitReturn && $objOneListener->handleUserFirstLoginEvent($strUserid);
        }

        return $bitReturn;
    }


    /**
     * Loads all objects registered to ne notified in case of status-changes
     * @static
     * @return interface_userfirstlogin_listener[]
     */
    private static function getUserFirstLoginListeners() {
        if(self::$arrFirstLoginListener == null) {
            self::$arrFirstLoginListener = self::loadInterfaceImplementers("interface_userfirstlogin_listener");
        }

        return self::$arrFirstLoginListener;
    }

    /**
     * Loads all objects registered to ne notified in case of status-changes
     * @static
     * @return interface_recorddeleted_listener[]
     */
    private static function getRecordDeletedListeners() {
        if(self::$arrRecordDeletedListener == null) {
            self::$arrRecordDeletedListener = self::loadInterfaceImplementers("interface_recorddeleted_listener");
        }

        return self::$arrRecordDeletedListener;
    }

    /**
     * Loads all objects registered to ne notified in case of status-changes
     * @static
     * @return interface_statuschanged_listener[]
     */
    private static function getStatusChangedListeners() {
        if(self::$arrStatusChangedListener == null) {
            self::$arrStatusChangedListener = self::loadInterfaceImplementers("interface_statuschanged_listener");
        }

        return self::$arrStatusChangedListener;
    }


    /**
     * Loads all objects registered to ne notified in case of previd-changes
     * @static
     * @return interface_previdchanged_listener[]
     */
    private static function getPrevidChangedListeners() {
        if(self::$arrPrevidChangedListener == null) {
            self::$arrPrevidChangedListener = self::loadInterfaceImplementers("interface_previdchanged_listener");
        }

        return self::$arrPrevidChangedListener;
    }


    /**
     * Loads all objects registered to ne notified in case of previd-changes
     * @static
     * @return interface_recordcopied_listener[]
     */
    private static function getRecordCopiedListeners() {
        if(self::$arrRecordCopiedListener == null) {
            self::$arrRecordCopiedListener = self::loadInterfaceImplementers("interface_recordcopied_listener");
        }

        return self::$arrRecordCopiedListener;
    }

    /**
     * Loads all business-objects implementing the passed interface
     * @static
     * @param $strTargetInterface
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

