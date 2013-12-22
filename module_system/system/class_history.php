<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
        //Loading the current history from session
        $arrHistory = $this->objSession->getSession(self::STR_ADMIN_SESSION_KEY);

        $strQueryString = getServer("QUERY_STRING");
        //Clean querystring of empty actions
        if(uniSubstr($strQueryString, -8) == "&action=") {
            $strQueryString = substr_replace($strQueryString, "", -8);
        }

        //Just do s.th., if not in the rights-mgmt
        if(uniStrpos($strQueryString, "module=right") !== false) {
            return;
        }

        //And insert just, if different to last entry
        if($strQueryString == $this->getAdminHistory()) {
            return;
        }
        //If we reach up here, we can enter the current query
        if($arrHistory !== false) {
            array_unshift($arrHistory, $strQueryString);
            while(count($arrHistory) > 5) {
                array_pop($arrHistory);
            }
        }
        else {
            $arrHistory[] = $strQueryString;
        }
        //saving the new array to session
        $this->objSession->setSession(self::STR_ADMIN_SESSION_KEY, $arrHistory);
    }


    /**
     * Fetches an admin-url the current user loaded before
     *
     * @param int $intPosition
     *
     * @return string
     */
    public function getAdminHistory($intPosition = 0) {
        $arrHistory = $this->objSession->getSession(self::STR_ADMIN_SESSION_KEY);
        if(isset($arrHistory[$intPosition])) {
            return $arrHistory[$intPosition];
        }
        else {
            return null;
        }
    }


    /**
     * Adds the current call to the list of portal urls
     *
     * @return void
     */
    public function setPortalHistory() {
        //Loading the current history from session
        $arrHistory = $this->objSession->getSession(self::STR_PORTAL_SESSION_KEY);

        $strQueryString = getServer("QUERY_STRING");
        //Clean Querystring of empty actions
        if(uniSubstr($strQueryString, -8) == "&action=") {
            $strQueryString = substr_replace($strQueryString, "", -8);
        }
        //And insert just, if different to last entry
        if($strQueryString == $this->getPortalHistory()) {
            return;
        }
        //If we reach up here, we can enter the current query
        if($arrHistory !== false) {
            array_unshift($arrHistory, $strQueryString);
            while(count($arrHistory) > 5) {
                array_pop($arrHistory);
            }
        }
        else {
            $arrHistory[] = $strQueryString;
        }
        //saving the new array to session
        $this->objSession->setSession(self::STR_PORTAL_SESSION_KEY, $arrHistory);

        return;
    }

    /**
     * Fetches an admin-url the current user loaded before
     *
     * @param int $intPosition
     * @return string
     */
    public function getPortalHistory($intPosition = 0) {
        $arrHistory = $this->objSession->getSession(self::STR_PORTAL_SESSION_KEY);
        if(isset($arrHistory[$intPosition])) {
            return $arrHistory[$intPosition];
        }
        else {
            return null;
        }
    }

}

