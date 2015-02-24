<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The core eventmanager is used to trigger and fire internal events such as status-changed or record-deleted-events.
 * Therefore the corresponding interface-implementers are called and notified.
 *
 * Since version 4.5, the eventdispatcher provides a fully generic approach. Hardcoded-/package based dependencies are no longer required.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_core_eventdispatcher {

    /**
     * @var  class_core_eventdispatcher
     */
    private static $objInstance = null;

    /**
     * @var class_model[]
     */
    private static $arrListeners = array();

    /**
     * @var interface_genericevent_listener[][]
     */
    private $arrRegisteredListeners = array();

    /**
     * Private for the sake of a singleton
     */
    private function __construct() {
    }

    /**
     * Returns an instance of system wide event-dispatcher
     * @return class_core_eventdispatcher
     */
    public static function getInstance() {
        if(self::$objInstance == null)
            self::$objInstance = new class_core_eventdispatcher();

        return self::$objInstance;
    }

    /**
     * Adds a listener to the list of registered listeners.
     *
     * @param string $strEventIdentifier
     * @param interface_genericevent_listener $objListener
     *
     * @return void
     */
    public function addListener($strEventIdentifier, interface_genericevent_listener $objListener) {
        if(!isset($this->arrRegisteredListeners[$strEventIdentifier]))
            $this->arrRegisteredListeners[$strEventIdentifier] = array();

        class_logger::getInstance(class_logger::EVENTS)->addLogRow("registering listener for type ".$strEventIdentifier.", instance of ".get_class($objListener), class_logger::$levelInfo);
        $this->arrRegisteredListeners[$strEventIdentifier][] = $objListener;
    }

    /**
     * Removes all listeners of the same class registered for the given event-identifier
     * and adds the passed listener afterwards.
     *
     * @param string $strEventIdentifier
     * @param interface_genericevent_listener $objListener
     *
     * @return void
     */
    public function removeAndAddListener($strEventIdentifier, interface_genericevent_listener $objListener) {
        if(!isset($this->arrRegisteredListeners[$strEventIdentifier]))
            $this->arrRegisteredListeners[$strEventIdentifier] = array();

        foreach($this->arrRegisteredListeners[$strEventIdentifier] as $objOneRegistered) {
            if(get_class($objListener) == get_class($objOneRegistered)) {
                $this->removeListener($strEventIdentifier, $objOneRegistered);
            }
        }

        $this->addListener($strEventIdentifier, $objListener);
    }

    /**
     * Removes ALL registered listeners for the given extension point
     *
     * @param string $strEventIdentifier
     *
     * @return void
     */
    public function removeAllListeners($strEventIdentifier) {
        $this->arrRegisteredListeners[$strEventIdentifier] = array();
    }

    /**
     * Removes a registered listener from a list of given event-listeners.
     * The listener is identified by a reference-comparison, so only the same instance will be removed.
     *
     * @param string $strEventIdentifier
     * @param interface_genericevent_listener $objListener
     *
     * @return bool
     */
    public function removeListener($strEventIdentifier, interface_genericevent_listener $objListener) {
        if(!isset($this->arrRegisteredListeners[$strEventIdentifier]))
            $this->arrRegisteredListeners[$strEventIdentifier] = array();

        foreach($this->arrRegisteredListeners[$strEventIdentifier] as $intKey => $objOneListener) {
            if($objListener === $objOneListener) {
                class_logger::getInstance(class_logger::EVENTS)->addLogRow("removing listener for type ".$strEventIdentifier.", instance of ".get_class($objOneListener), class_logger::$levelInfo);
                unset($this->arrRegisteredListeners[$strEventIdentifier][$intKey]);
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the list of listeners currently registered for a given event
     *
     * @param string $strEventIdentifier
     *
     * @return interface_genericevent_listener[]
     */
    public function getRegisteredListeners($strEventIdentifier) {
        if(!isset($this->arrRegisteredListeners[$strEventIdentifier]))
            $this->arrRegisteredListeners[$strEventIdentifier] = array();

        return $this->arrRegisteredListeners[$strEventIdentifier];
    }

    /**
     * Notifies all listeners implementing the passed extension point.
     * The list of arguments is passed to all listeners.
     * Make sure to return a valid boolean value, otherwise the chain of event-handler may be broken.
     *
     * @param string $strEventIdentifier
     * @param array $arrArguments
     *
     * @return bool
     * @since 4.5
     * @see interface_genericevent_listener
     */
    public function notifyGenericListeners($strEventIdentifier, $arrArguments) {
        $bitReturn = true;

        if(!isset($this->arrRegisteredListeners[$strEventIdentifier]))
            $this->arrRegisteredListeners[$strEventIdentifier] = array();

        /** @var $objOneListener interface_genericevent_listener */
        foreach($this->arrRegisteredListeners[$strEventIdentifier] as $objOneListener) {
            class_logger::getInstance(class_logger::EVENTS)->addLogRow("propagating event of type ".$strEventIdentifier." to instance of ".get_class($objOneListener), class_logger::$levelInfo);
            $bitReturn = $objOneListener->handleEvent($strEventIdentifier, $arrArguments) && $bitReturn;
        }

        return $bitReturn;
    }

    /**
     * Returns all classes (here: instances of the class) implementing a given interface
     * @param string $strInterface
     *
     * @return class_model
     * @deprecated
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
     * @deprecated please migrate to generic, decoupled event-listeners
     * @see class_core_eventdispatcher::notifyGenericListeners
     *
     * @return bool
     */
    public static function notifyListeners($strInterface, $strMethodname,  $arrArguments) {
        if(!isset(self::$arrListeners[$strInterface]))
            self::$arrListeners[$strInterface] = self::loadInterfaceImplementers($strInterface);

        $bitReturn = true;
        foreach(self::$arrListeners[$strInterface] as $objOneListener) {
            $bitReturn = call_user_func_array(array($objOneListener, $strMethodname), $arrArguments) && $bitReturn;
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
     * Triggers all model-classes implementing the interface interface_recordupdated_listener and notifies them about an updated object
     *
     * @param class_model $objRecord
     *
     * @static
     * @return bool
     * @deprecated use class_core_eventdispatcher::notifyListeners() instead
     * @see interface_recordupdated_listener
     * @see class_core_eventdispatcher::notifyListeners()
     */
    public static function notifyRecordUpdatedListeners(class_model $objRecord) {
        return self::notifyListeners("interface_recordupdated_listener", "handleRecordUpdatedEvent", array($objRecord));
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

