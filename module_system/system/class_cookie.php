<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * A small class to provide acces to cookies, both ways, reading as writing.
 * Use this class ONLY to access / set cookies!
 *
 * @package module_system
 */
class class_cookie {

    private $arrModul;

	/**
	 * Contructor
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_cookie";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _system_modul_id_;
	}

	/**
	 * Sends a cookie to the browser
	 *
	 * @param string $strName
	 * @param string $strValue
	 * @param int $intTime
	 * @return bool
	 */
	public function setCookie($strName, $strValue, $intTime = 0) {
	    //cookie is 30 days valid
	    if($intTime == 0)
	       $intTime = time()+60*60*24*30;

        $strPath = _webpath_;

	    return setcookie($strName, $strValue, $intTime);
	    return setcookie($strName, $strValue, $intTime, $strPath);
	}

	/**
	 * Gets the value of a cookie
	 *
	 * @param string $strName
	 * @return mixed
	 */
	public function getCookie($strName) {
	    return getCookie($strName);
	}

}

?>