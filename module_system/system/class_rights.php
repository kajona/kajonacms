<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

/**
 * Class to handle all the right-stuff concerning system-records
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_rights {

	/**
	 * class_db
	 *
	 * @var class_db
	 */
	private $objDb = null;

	/**
	 * Session instance
	 *
	 * @var class_session
	 */
	private $objSession = null;				//Session Object
	private $arrRightRowsCache = array();		//Array, caching database-rows with permission-entries

	private static $objRights = null;

    private $bitTestMode = false;

	/**
	 * Constructor doing the usual setup things
	 *
	 */
	private function __construct() 	{
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
     * Helper, shouldn't be called in regular cases.
     * Rebuilds the complete rights-structure, so saves the rights downwards.
     *
     * @param string $strStartId
     * @return bool
     */
    public function rebuildRightsStructure($strStartId = "0") {
        //load rights from root-node
        $arrRootRights = $this->getPlainRightRow($strStartId);
        return $this->setRights($arrRootRights, $strStartId);
    }


    /**
     * Writes a single rights record to the database.
     *
     * @param string $strSystemid
     * @param array $arrRights
     * @return bool
     */
    private function writeSingleRecord($strSystemid, $arrRights) {
        //Splitting up the rights
        $arrParams   = array();
        $arrParams[] = (int)$arrRights["inherit"];
        $arrParams[] = $arrRights["view"];
        $arrParams[] = $arrRights["edit"];
        $arrParams[] = $arrRights["delete"];
        $arrParams[] = $arrRights["right"];
        $arrParams[] = $arrRights["right1"];
        $arrParams[] = $arrRights["right2"];
        $arrParams[] = $arrRights["right3"];
        $arrParams[] = $arrRights["right4"];
        $arrParams[] = $arrRights["right5"];
        $arrParams[] = $strSystemid;

        $strQuery = "UPDATE "._dbprefix_."system_right
                        SET right_inherit=?,
                            right_view=?,
                            right_edit=?,
                            right_delete=?,
                            right_right=?,
                            right_right1=?,
                            right_right2=?,
                            right_right3=?,
                            right_right4=?,
                            right_right5=?
                      WHERE right_id=?";

        if($this->objDb->_pQuery($strQuery, $arrParams) ) {
            //Flush the cache so later lookups will match the new rights
            $this->objDb->flushQueryCache();
            $this->arrRightRowsCache = array();
            return true;
        }
        else
            return false;
    }

    /**
	 * Writes rights to the database.
     * Wrapper to the recursive function class_rights::setRightsRecursive($arrRights, $strSystemid)
	 *
     * @see setRightsRecursive($arrRights, $strSystemid)
	 * @param mixed $arrRights
	 * @param string $strSystemid
     * @throws class_exception
	 * @return bool
	 */
	public function setRights($arrRights, $strSystemid) 	{
	    //start a new tx
        $this->objDb->transactionBegin();

        $bitSave = $this->setRightsRecursive($arrRights, $strSystemid);

        if($bitSave) {
            $this->objDb->transactionCommit();
            class_logger::getInstance()->addLogRow("saving rights of record ".$strSystemid." succeeded", class_logger::$levelInfo);
        }
        else {
            $this->objDb->transactionRollback();
            class_logger::getInstance()->addLogRow("saving rights of record ".$strSystemid." failed", class_logger::$levelError);
            throw new class_exception("saving rights of record ".$strSystemid." failed", class_exception::$level_ERROR);
        }

        return $bitSave;

	}


    /**
     * Set the rights of the passed systemrecord.
     * Writes the rights down to all records inheriting from the current one.
     *
     * @param array $arrRights
     * @param string $strSystemid
     * @return bool
     */
    private function setRightsRecursive($arrRights, $strSystemid) 	{
        $bitReturn = true;

	    //check against root-record: here no inheritance
	    if($strSystemid == "" || $strSystemid == "0")
	        $arrRights["inherit"] = 0;


        $objCommon = new class_module_system_common($strSystemid);
        $strPrevSystemid = $objCommon->getPrevId();


        //separate the two possible modes: inheritance or no inheritance
        //if set to inheritance, set the flag, load the rights from one level above and write the rights down.
        if(isset($arrRights["inherit"]) && $arrRights["inherit"] == 1) {
            $arrRights = $this->getPlainRightRow($strPrevSystemid);
            $arrRights["inherit"] = 1;
        }

        $bitReturn &= $this->writeSingleRecord($strSystemid, $arrRights);

        //load all child records in order to update them, too.
        $arrChilds = $objCommon->getChildNodesAsIdArray($strSystemid);
        foreach($arrChilds as $strOneChildId) {
            //this check is needed for strange tree-behaviours!!! DO NOT REMOVE!
            if($strOneChildId != $strSystemid) {

                $arrChildRights = $this->getPlainRightRow($strOneChildId);

                if($arrChildRights["inherit"] == 1) {
                    $arrChildRights = $arrRights;
                    $arrChildRights["inherit"] = 1;
                }
                $bitReturn &= $this->setRightsRecursive($arrChildRights, $strOneChildId);
            }
        }

        return $bitReturn;

	}

    /**
     * Looks up, whether a record intherits its' rights or not.
     * If not, false is being returned, if the record inherits the rights from another
     * record, true is returned instead.
     *
     * @param $strSystemid
     * @return bool
     */
	public function isInherited($strSystemid) {
        $arrRights = $this->getPlainRightRow($strSystemid);
        return $arrRights["inherit"] == 1;
	}

    /**
     * Sets the inheritance-status for a single record
     *
     * @param bool $bitIsInherited
     * @param string $strSystemid
     * @return bool
     */
	public function setInherited($bitIsInherited, $strSystemid) {
        $arrRights = $this->getPlainRightRow($strSystemid);
        $arrRights["inherit"] = ($bitIsInherited ? 1 : 0);
        return $this->setRights($arrRights, $strSystemid);
	}

    /**
     * Looks up the rights for a given SystemID and going up the tree if needed (inheritance!)
     *
     * @param string $strSystemid
     * @return array
     */
	private function getPlainRightRow($strSystemid) {

        if(isset($this->arrRightRowsCache[$strSystemid]))
            $arrRow = $this->arrRightRowsCache[$strSystemid];
        else {
            $strQuery = "SELECT *
                            FROM "._dbprefix_."system,
                                 "._dbprefix_."system_right
                            WHERE system_id = ?
                                AND right_id = system_id ";

            $arrRow = $this->objDb->getPRow($strQuery, array($strSystemid));
            $this->arrRightRowsCache[$strSystemid] = $arrRow;
        }

        $arrRights = array();
        if(isset($arrRow["right_id"]) && validateSystemid($arrRow["right_id"])) {
            $arrRights["view"]   = $arrRow["right_view"];
            $arrRights["edit"]   = $arrRow["right_edit"];
            $arrRights["delete"] = $arrRow["right_delete"];
            $arrRights["right"]  = $arrRow["right_right"];
            $arrRights["right1"] = $arrRow["right_right1"];
            $arrRights["right2"] = $arrRow["right_right2"];
            $arrRights["right3"] = $arrRow["right_right3"];
            $arrRights["right4"] = $arrRow["right_right4"];
            $arrRights["right5"] = $arrRow["right_right5"];
            $arrRights["inherit"]= (int)$arrRow["right_inherit"];
        }
        else {
            $arrRights["view"]   = "";
            $arrRights["edit"]   = "";
            $arrRights["delete"] = "";
            $arrRights["right"]  = "";
            $arrRights["right1"] = "";
            $arrRights["right2"] = "";
            $arrRights["right3"] = "";
            $arrRights["right4"] = "";
            $arrRights["right5"] = "";
            $arrRights["inherit"]= 1;
        }


        return $arrRights;
	}


    /**
     * Returns a 2-dimensional Array containg the groups and the assigned rights.
     *
     * @param string $strSystemid
     * @return mixed
     */
	public function getArrayRights($strSystemid) {
		$arrReturn = array();

		$arrRow = $this->getPlainRightRow($strSystemid);

		//Exploding the array
		$arrReturn["view"]   = explode(",",$arrRow["view"]);
		$arrReturn["edit"]   = explode(",",$arrRow["edit"]);
		$arrReturn["delete"] = explode(",",$arrRow["delete"]);
		$arrReturn["right"]  = explode(",",$arrRow["right"]);
		$arrReturn["right1"] = explode(",",$arrRow["right1"]);
		$arrReturn["right2"] = explode(",",$arrRow["right2"]);
		$arrReturn["right3"] = explode(",",$arrRow["right3"]);
		$arrReturn["right4"] = explode(",",$arrRow["right4"]);
		$arrReturn["right5"] = explode(",",$arrRow["right5"]);

		$arrReturn["inherit"] = (int)$arrRow["inherit"];

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["view"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["edit"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["delete"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right1"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;


		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right2"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right3"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;

		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right4"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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

        if($strSystemid == "")
            return false;

        if($this->bitTestMode)
            return true;


		$bitReturn = false;
        $arrGroups = array();
		if($strUserid == "") {
			$strUserid = $this->objSession->getUserID();
            if($strUserid != "") {
                $arrGroups = $this->objSession->getGroupIdsAsArray();
            }
        }
        else {
            $objUser = new class_module_user_user($strUserid);
            $arrGroups = $objUser->getArrGroupIds();
        }

		$arrRights = $this->getArrayRights($strSystemid);

		if($strUserid != "") {
			foreach($arrGroups as $strGroup) {
				if(in_array($strGroup, $arrRights["right5"]))
					$bitReturn = true;
			}
		}
		else {
			//Guest
			$strGuestId = _guests_group_id_;

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
	    return class_module_system_module::getModuleIdByNr($intModuleNr);
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
							AND member.group_member_group_id = '"._admins_group_id_."'
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

	    $this->objDb->flushQueryCache();
        $this->arrRightRowsCache = array();

	    //Load the current rights
	    $arrRights = $this->getArrayRights($strSystemid, false);

	    //rights not given, add now, disabling inheritance
	    $arrRights["inherit"] = 0;

	    //add the group to the row
	    if(!in_array($strGroupId, $arrRights[$strRight]))
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

    /**
	 * Removes a group from a right at a given systemid
	 * <b>NOTE: By setting rights using this method, inheritance is set to false!!!</b>
	 *
	 * @param string $strGroupId
	 * @param string $strSystemid
	 * @param string $strRight one of view, edit, delete, right, right1, right2, right3, right4, right5
	 * @return bool
	 */
	public function removeGroupFromRight($strGroupId, $strSystemid, $strRight) {

	    $this->objDb->flushQueryCache();
        $this->arrRightRowsCache = array();

	    //Load the current rights
	    $arrRights = $this->getArrayRights($strSystemid, false);

	    //rights not given, add now, disabling inheritance
	    $arrRights["inherit"] = 0;

	    //remove the group
        if(in_array($strGroupId, $arrRights[$strRight])) {
            foreach($arrRights[$strRight] as $intKey => $strSingleGroup) {
                if($strSingleGroup == $strGroupId)
                    unset($arrRights[$strRight][$intKey]);
            }
        }

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

    /**
     * Flushes the internal rights cache
     *
     * @return void
     */
    public function flushRightsCache() {
        $this->arrRightRowsCache = array();
    }

    public function setBitTestMode($bitTestMode) {
        $this->bitTestMode = $bitTestMode &&  _autotesting_;
    }


    /**
     * Validates a set of permissions for a single object.
     * The string of permissions is a comma-separated list, whereas the entries may be one of
     * view, edit, delete, right, right1, right2, right3, right4, right5
     * If at least a single permission is given, true is returned, otherwise false.
     *
     * @param $strPermissions
     * @param class_model $objObject
     * @return bool
     * @throws class_exception
     * @since 4.0
     */
    public function validatePermissionString($strPermissions, class_model $objObject) {

        if(!$objObject instanceof class_model) {
            throw new class_exception("automated permission-check only for instances of class_model", class_exception::$level_ERROR);
            return false;
        }

        if(trim($strPermissions) == "")
            return false;

        $arrPermissions = explode(",", $strPermissions);

        foreach($arrPermissions as $strOnePermissions) {
            $strOnePermissions = trim($strOnePermissions);

            switch (trim($strOnePermissions)) {
                case "view":
                    if($objObject->rightView())
                        return true;
                    break;
                case "edit":
                    if($objObject->rightEdit())
                        return true;
                    break;
                case "delete":
                    if($objObject->rightDelete())
                        return true;
                    break;
                case "right":
                    if($objObject->rightRight())
                        return true;
                    break;
                case "right1":
                    if($objObject->rightRight1())
                        return true;
                    break;
                case "right2":
                    if($objObject->rightRight2())
                        return true;
                    break;
                case "right3":
                    if($objObject->rightRight3())
                        return true;
                    break;
                case "right4":
                    if($objObject->rightRight4())
                        return true;
                    break;
                case "right5":
                    if($objObject->rightRight5())
                        return true;
                    break;
                default:
                    break;
            }
        }

        return false;
    }

    /**
     * Adds a row to the internal cache.
     * Only to be used in combination with class_roots setArrInitRow.
     *
     * @param $arrRow
     */
    public function addRowToCache($arrRow) {
        if(isset($arrRow["right_id"]))
            $this->arrRightRowsCache[$arrRow["right_id"]] = $arrRow;
    }
}


