<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_rights.php																					*
* 	Class to manage the rights of system-records														*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

/**
 * Class to handle all the right-stuff concerning system-records
 *
 * @package modul_system
 */
class class_rights {
	private $arrModul = null;				//Array mit den Moduldaten
	private $objDb = null;					//DatenbankObject
	private $objSession = null;				//Session Object
	private $arrRightsCache = array();		//Array, caching rights

	private static $objRights = null;

	/**
	 * Constructor doing the usual setup things
	 *
	 */
	private function __construct() 	{
		$this->modul["name"] 		= "class_rights";
		$this->modul["author"] 		= "sidler@mulchprod.de";
		$this->modul["moduleId"] 		= _rechte_modul_id_;

		$objCarrier = class_carrier::getInstance();
		$this->objDb = $objCarrier->getObjDb();
		$this->objSession = $objCarrier->getObjSession();
	}

	/**
	 * Returns one Instance of the Rights-Object, using a singleton pattern
	 *
	 * @return object The Rights-Object
	 */
	public static function getInstance() {
		if(self::$objRights == null) {
			self::$objRights = new class_rights();
		}

		return self:: $objRights;
	}


	/**
	 * Writes rights to the database
	 *
	 * @param mixed $arrRights
	 * @param string $strSystemid
	 * @return bool
	 */
	public function setRights($arrRights = array(), $strSystemid) 	{
	    //check against root-record: here no inheritance
	    if($strSystemid == "")
	        $arrRights["inherit"] = 0;

		if(isset($arrRights["inherit"]) && $arrRights["inherit"] == 1) 	{
			//Inheritance, nothing special
			$strQuery = "UPDATE "._dbprefix_."system_right SET right_inherit=1 WHERE right_id='".dbsafeString($strSystemid)."'";
			if($this->objDb->_query($strQuery))
				return true;
			else
				return false;
		}
		else {
			//Splitting up the rights
			$strView = $arrRights["view"];
			$strEdit = $arrRights["edit"];
			$strDelete = $arrRights["delete"];
			$strRights = $arrRights["right"];
			$strRight1 = $arrRights["right1"];
			$strRight2 = $arrRights["right2"];
			$strRight3 = $arrRights["right3"];
			$strRight4 = $arrRights["right4"];
			$strRight5 = $arrRights["right5"];

			$strQuery = "UPDATE "._dbprefix_."system_right
						SET right_inherit=0,
							right_view='".dbsafeString($strView)."',
							right_edit='".dbsafeString($strEdit)."',
							right_delete='".dbsafeString($strDelete)."',
							right_right='".dbsafeString($strRights)."',
							right_right1='".dbsafeString($strRight1)."',
							right_right2='".dbsafeString($strRight2)."',
							right_right3='".dbsafeString($strRight3)."',
							right_right4='".dbsafeString($strRight4)."',
							right_right5='".dbsafeString($strRight5)."'
							WHERE right_id='".dbsafeString($strSystemid)."'";
			if($this->objDb->_query($strQuery))
				return true;
			else
				return false;
		}
	}

    /**
     * Looks up, whether a record intherits its' rights or not.
     * If not, false is being returned, if the record inherits the rights from another 
     * record, true is returned instead.
     * 
     * @return bool
     */
	public function isInherited($strSystemid) {
		$bitReturn = false;
		
		$strQuery = "SELECT *
                        FROM "._dbprefix_."system,
                             "._dbprefix_."system_right
                        WHERE system_id = '".dbsafeString($strSystemid)."'
                            AND right_id = system_id ";

        $arrRow = $this->objDb->getRow($strQuery);

        if((isset($arrRow["right_inherit"]) && $arrRow["right_inherit"] == 1))
            $bitReturn = true;
		
		return $bitReturn;
	}

	/**
	 * Looks up the rights for a given SystemID and going up the tree if needed (inheritance!)
	 *
	 * @param string $strSystemid
	 * @param bool $bitLoadInherited behave as if loading inherited rights
	 * @return mixed
	 */
	public function getRightRow($strSystemid, $bitLoadInherited = false) {
		$arrReturn = array();
		$arrRow = array();
		//Row in Cache?
		if(isset($this->arrRightsCache[$strSystemid]) && !$bitLoadInherited)
			return $this->arrRightsCache[$strSystemid];

		$strQuery = "SELECT *
						FROM "._dbprefix_."system,
							 "._dbprefix_."system_right
						WHERE system_id = '".dbsafeString($strSystemid)."'
							AND right_id = system_id ";

		$arrRow = $this->objDb->getRow($strQuery);

		if((isset($arrRow["right_inherit"]) && $arrRow["right_inherit"] == 1) || $bitLoadInherited) {
			//Is there any prev_id?
			if($arrRow["system_prev_id"] != "0") 	{
				//Loading the previous datarecord using a recursion
				$arrRow = $this->getRightRow($arrRow["system_prev_id"]);
			}
			else {
				//Inheritance, but NO system_prev_id
				//So we use the module-node!
				//!!!!!!!BUT: There are special cases!!!!!!

				//Special case 1: Folders!
				if($arrRow["system_module_nr"] == _pages_ordner_id_) {
					//Pages Root
					$strQuery = "SELECT *
								FROM "._dbprefix_."system,
									 "._dbprefix_."system_right
								WHERE system_id = '".dbsafeString($this->getModuleSystemid(_pages_modul_id_))."'
									AND	right_id = system_id ";

					$arrRow = $this->objDb->getRow($strQuery);
					if($arrRow["right_inherit"] == 1) {
						//This Record inherits from the global root
						$strQuery= "SELECT *
										FROM "._dbprefix_."system,
											"._dbprefix_."system_right
										WHERE system_id = '0'
										  AND system_id = right_id";
						$arrRow = $this->objDb->getRow($strQuery);
					}
				}
				//Regular Case: Loading the module-node
				else {
					$strQuery = "SELECT *
								FROM "._dbprefix_."system,
									 "._dbprefix_."system_right
								WHERE system_id = '".dbsafeString($this->getModuleSystemid($arrRow["system_module_nr"]))."'
									AND right_id = system_id ";

					$arrRow = $this->objDb->getRow($strQuery);
					if($arrRow["right_inherit"] == 1) 	{
						//This Record inherits from the global root
						$strQuery= "SELECT *
										FROM "._dbprefix_."system,
											"._dbprefix_."system_right
										WHERE system_id = '0'
										  AND system_id = right_id";
						$arrRow = $this->objDb->getRow($strQuery);
					}
				}
			}
		}
		//Saving in the cache
		$this->arrRightsCache[$strSystemid] = $arrRow;
		return $arrRow;
	}


	/**
	 * Returns a 2-dimensional Array containg the groups and the assinged rights.
	 * If the record inherits, the tree is traversed upwards till the base-node is being found.
	 * In the last case, this is the system-root
	 *
	 * @param string $strSystemid
	 * @param bool $bitLoadInherited behave as if loading inherited rights
	 * @return mixed
	 */
	public function getArrayRights($strSystemid, $bitLoadInherited = false) {
		$arrReturn = array();

		//Inheritance?
		$strQuery = "SELECT *
					FROM "._dbprefix_."system,
						 "._dbprefix_."system_right
					WHERE system_id = '".dbsafeString($strSystemid)."'
						AND	right_id = system_id";

		$arrTemp = $this->objDb->getRow($strQuery);

		$arrRow = $this->getRightRow($strSystemid, $bitLoadInherited);

		//Exploding the array
		$arrReturn["view"] = explode(",",(isset($arrRow["right_view"]) ? $arrRow["right_view"] : ""));
		$arrReturn["edit"] = explode(",",(isset($arrRow["right_edit"]) ? $arrRow["right_edit"] : ""));
		$arrReturn["delete"] = explode(",",(isset($arrRow["right_delete"]) ? $arrRow["right_delete"] : ""));
		$arrReturn["right"] = explode(",",(isset($arrRow["right_right"]) ? $arrRow["right_right"] : ""));
		$arrReturn["right1"] = explode(",",(isset($arrRow["right_right1"]) ? $arrRow["right_right1"] : ""));
		$arrReturn["right2"] = explode(",",(isset($arrRow["right_right2"]) ? $arrRow["right_right2"] : ""));
		$arrReturn["right3"] = explode(",",(isset($arrRow["right_right3"]) ? $arrRow["right_right3"] : ""));
		$arrReturn["right4"] = explode(",",(isset($arrRow["right_right4"]) ? $arrRow["right_right4"] : ""));
		$arrReturn["right5"] = explode(",",(isset($arrRow["right_right5"]) ? $arrRow["right_right5"] : ""));

		$arrReturn["inherit"] = isset($arrTemp["right_inherit"]) ? $arrTemp["right_inherit"] : "";

		return $arrReturn;
	}

	/**
	 * Checks if the user has the right to view the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightView($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["view"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["view"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}

	/**
	 * Checks if the user has the right to edit the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightEdit($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["edit"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["edit"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}


	/**
	 * Checks if the user has the right to delete the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightDelete($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["delete"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["delete"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}



	/**
	 * Checks if the user has the right to edit the rights of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}



	/**
	 * Checks if the user has the right to edit the right1 of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight1($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right1"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right1"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}


	/**
	 * Checks if the user has the right to edit the right2 of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight2($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right2"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right2"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}


	/**
	 * Checks if the user has the right to edit the right3 of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight3($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right3"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right3"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}

	/**
	 * Checks if the user has the right to edit the right4 of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight4($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right4"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right4"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}


	/**
	 * Checks if the user has the right to edit the right5 of the record
	 *
	 * @param string $strSystemid
	 * @param string $strUserid
	 * @return bool
	 */
	public function rightRight5($strSystemid, $strUserid = "") {
		$bitReturn = false;
		//Given ID?
		if($strUserid == "")
			$strUserid = $this->objSession->getSession("userid");

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid !== false) {
			//Loading the groups the user belonges to
			$strQuery = "SELECT group_member_group_id
						FROM "._dbprefix_."user_group_members
						WHERE group_member_user_id='".dbsafeString($strUserid)."'";

			$arrGroups = $this->objDb->getArray($strQuery);

			foreach($arrGroups as $arrRow) 	{
				if(in_array($arrRow["group_member_group_id"], $arrRights["right5"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _gaeste_gruppe_id_;

			if(in_array($strGuestId, $arrRights["right5"]))
				$bitReturn = true;
		}
		return $bitReturn;
	}


	/**
	 * Looks up the systemid of a module
	 *
	 * @param int $intModuleNr
	 * @return string
	 */
	public function getModuleSystemid($intModuleNr) {
	    include_once(_systempath_."/class_modul_system_module.php");
	    return class_modul_system_module::getModuleIdByNr($intModuleNr);
	}


	/**
	 * Checks whether a user is in the admin-group or not
	 *
	 * @param string $strUserid
	 * @return bool
	 */
	public function userIsAdmin($strUserid) {
		$bitReturn = false;

		$strQuery = "SELECT COUNT(*)
						FROM "._dbprefix_."user_group_members AS member,
						     "._dbprefix_."user as users
						WHERE member.group_member_user_id = users.user_id
							AND member.group_member_group_id = '"._admin_gruppe_id_."'
							AND users.user_id='".dbsafeString($strUserid)."'";
		$arrRow = $this->objDb->getRow($strQuery);

		if($arrRow["COUNT(*)"] == 1)
			$bitReturn = true;

		return $bitReturn;
	}

	/**
	 * Adds a group for a right at a given systemid
	 * <b>NOTE: By setting rights using this method, inheritance is set to false!!!</b>
	 *
	 * @param string $strGroupId
	 * @param string $strSystemid
	 * @param string $strRight one of view, edit, delete, right, right1, right2, right3, right4, right5
	 * @return bool
	 */
	public function addGroupToRight($strGroupId, $strSystemid, $strRight) {
	    $bitReturn = true;

	    //Load the current rights
	    $arrRights = $this->getArrayRights($strSystemid);

	    if(in_array($strGroupId, $arrRights[$strRight])) {
	        //rights already given, return
	        return true;
	    }

	    //rights not given, add now
	    $arrRights["inherit"] = 0;

	    //add the group to the row
	    $arrRights[$strRight][] = $strGroupId;

	    //build a one-dim array
	    $arrRights["view"] = implode(",", $arrRights["view"]);
	    $arrRights["edit"] = implode(",", $arrRights["edit"]);
	    $arrRights["delete"] = implode(",", $arrRights["delete"]);
	    $arrRights["right"] = implode(",", $arrRights["right"]);
	    $arrRights["right1"] = implode(",", $arrRights["right1"]);
	    $arrRights["right2"] = implode(",", $arrRights["right2"]);
	    $arrRights["right3"] = implode(",", $arrRights["right3"]);
	    $arrRights["right4"] = implode(",", $arrRights["right4"]);
	    $arrRights["right5"] = implode(",", $arrRights["right5"]);

	    //and save the row
	    $bitReturn = $this->setRights($arrRights, $strSystemid);

	    return $bitReturn;
	}

} //class_rechte()


?>