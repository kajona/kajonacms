<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

namespace Kajona\System\System;


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
class CoreEventdispatcher
{

    /**
     * @var  CoreEventdispatcher
     */
    private static $objInstance = null;

    /**
     * @var GenericeventListenerInterface[][]
     */
    private $arrRegisteredListeners = array();

    /**
     * Private for the sake of a singleton
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of system wide event-dispatcher
     *
     * @return CoreEventdispatcher
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new CoreEventdispatcher();
        }

        return self::$objInstance;
    }

    /**
     * Adds a listener to the list of registered listeners.
     *
     * @param string $strEventIdentifier
     * @param GenericeventListenerInterface $objListener
     *
     * @return void
     */
    public function addListener($strEventIdentifier, GenericeventListenerInterface $objListener)
    {
        if (!isset($this->arrRegisteredListeners[$strEventIdentifier])) {
            $this->arrRegisteredListeners[$strEventIdentifier] = array();
        }

        Logger::getInstance(Logger::EVENTS)->info("registering listener for type ".$strEventIdentifier.", instance of ".get_class($objListener));
        $this->arrRegisteredListeners[$strEventIdentifier][] = $objListener;
    }

    /**
     * Removes all listeners of the same class registered for the given event-identifier
     * and adds the passed listener afterwards.
     *
     * @param string $strEventIdentifier
     * @param GenericeventListenerInterface $objListener
     *
     * @return void
     */
    public function removeAndAddListener($strEventIdentifier, GenericeventListenerInterface $objListener)
    {
        if (!isset($this->arrRegisteredListeners[$strEventIdentifier])) {
            $this->arrRegisteredListeners[$strEventIdentifier] = array();
        }

        foreach ($this->arrRegisteredListeners[$strEventIdentifier] as $objOneRegistered) {
            if (get_class($objListener) == get_class($objOneRegistered)) {
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
    public function removeAllListeners($strEventIdentifier)
    {
        $this->arrRegisteredListeners[$strEventIdentifier] = array();
    }

    /**
     * Removes a registered listener from a list of given event-listeners.
     * The listener is identified by a reference-comparison, so only the same instance will be removed.
     *
     * @param string $strEventIdentifier
     * @param GenericeventListenerInterface $objListener
     *
     * @return bool
     */
    public function removeListener($strEventIdentifier, GenericeventListenerInterface $objListener)
    {
        if (!isset($this->arrRegisteredListeners[$strEventIdentifier])) {
            $this->arrRegisteredListeners[$strEventIdentifier] = array();
        }

        foreach ($this->arrRegisteredListeners[$strEventIdentifier] as $intKey => $objOneListener) {
            if ($objListener === $objOneListener) {
                Logger::getInstance(Logger::EVENTS)->info("removing listener for type ".$strEventIdentifier.", instance of ".get_class($objOneListener));
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
     * @return GenericeventListenerInterface[]
     */
    public function getRegisteredListeners($strEventIdentifier)
    {
        if (!isset($this->arrRegisteredListeners[$strEventIdentifier])) {
            $this->arrRegisteredListeners[$strEventIdentifier] = array();
        }

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
     * @see GenericeventListenerInterface
     */
    public function notifyGenericListeners($strEventIdentifier, $arrArguments)
    {
        $bitReturn = true;

        if (!isset($this->arrRegisteredListeners[$strEventIdentifier])) {
            $this->arrRegisteredListeners[$strEventIdentifier] = array();
        }

        /** @var $objOneListener GenericeventListenerInterface */
        foreach ($this->arrRegisteredListeners[$strEventIdentifier] as $objOneListener) {
            Logger::getInstance(Logger::EVENTS)->info("propagating event of type ".$strEventIdentifier." to instance of ".get_class($objOneListener));
            $bitReturn = $objOneListener->handleEvent($strEventIdentifier, $arrArguments) && $bitReturn;
        }

        return $bitReturn;
    }


}

