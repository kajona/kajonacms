<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Wrapper to access the last urls the current user / session called.
 * In prior versions, this was handled by class_admin and class_portal.
 *
 * The history itself is filled automatically by the request dispatcher, so there's no need to do this on your own.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.4
 */
class class_history {

    /**
     * @var class_session
     */
    private $objSession = null;

    const STR_ADMIN_SESSION_KEY = "CLASS_HISTORY::STR_ADMIN_SESSION_KEY";
    const STR_PORTAL_SESSION_KEY = "CLASS_HISTORY::STR_PORTAL_SESSION_KEY";

    /**
     * Default constructor
     */
    function __construct() {
        $this->objSession = class_carrier::getInstance()->getObjSession();
    }

    /**
     * Add the current call to the stack of backend-urls
     * @return void
     */
    public function setAdminHistory() {
        $this->writeToHistoryArray(self::STR_ADMIN_SESSION_KEY);
    }

    /**
     * Adds the current call to the list of portal urls
     *
     * @return void
     */
    public function setPortalHistory() {
        $this->writeToHistoryArray(self::STR_PORTAL_SESSION_KEY);
    }

    private function writeToHistoryArray($strSessionKey) {
        $strQueryString = getServer("QUERY_STRING");

        //Clean querystring of empty actions
        if(uniSubstr($strQueryString, -8) == "&action=") {
            $strQueryString = substr_replace($strQueryString, "", -8);
        }

        //Just do s.th., if not in the rights-mgmt
        if(uniStrpos($strQueryString, "module=right") !== false) {
            return;
        }

        $arrHistory = $this->objSession->getSession($strSessionKey);

        //And insert just, if different to last entry
        if($strQueryString == $this->getHistoryEntry(0, $strSessionKey)) {
            return;
        }
        //If we reach up here, we can enter the current query
        if($arrHistory !== false) {
            array_unshift($arrHistory, $strQueryString);
            while(count($arrHistory) > 10) {
                array_pop($arrHistory);
            }
        }
        else {
            $arrHistory = array($strQueryString);
        }
        //saving the new array to session
        $this->objSession->setSession($strSessionKey, $arrHistory);
    }


    /**
     * Internal helper to access the session based arrays
     * @param int $intPosition
     * @param string $strSessionKey
     *
     * @return null
     */
    private function getHistoryEntry($intPosition, $strSessionKey) {
        $arrHistory = $this->objSession->getSession($strSessionKey);
        if(isset($arrHistory[$intPosition])) {
            return $arrHistory[$intPosition];
        }
        else {
            return null;
        }
    }

    /**
     * Fetches an admin-url the current user loaded before
     *
     * @param int $intPosition
     *
     * @return string
     */
    public function getAdminHistory($intPosition = 0) {
        return $this->getHistoryEntry($intPosition, self::STR_ADMIN_SESSION_KEY);
    }


    /**
     * returns the full admin history array
     *
     * @return string[]
     */
    public function getArrAdminHistory() {
        return $this->objSession->getSession(self::STR_ADMIN_SESSION_KEY);
    }

    /**
     * returns the full portal history array
     *
     * @return string[]
     */
    public function getArrPortalHistory() {
        return $this->objSession->getSession(self::STR_PORTAL_SESSION_KEY);
    }

    /**
     * Fetches an admin-url the current user loaded before
     *
     * @param int $intPosition
     * @return string
     */
    public function getPortalHistory($intPosition = 0) {
        return $this->getHistoryEntry($intPosition, self::STR_PORTAL_SESSION_KEY);
    }

}

