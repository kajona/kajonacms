<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The default implementation of the sortmanager, used by most modules and records.
 */
class class_common_sortmanager implements interface_sortmanager {

    /**
     * @var class_db
     */
    protected $objDB;

    protected $objSource;

    public function __construct(class_root $objSource) {
        $this->objDB = class_carrier::getInstance()->getObjDB();
        $this->objSource = $objSource;
    }

    /**
     * Fixes the sort-ids when a record is assigned to a new prev-id.
     * The old siblings have to be shifted, the records new sort-id
     * is set up by the new number of siblings.
     *
     * @param $strOldPrevid
     * @param $strNewPrevid
     * @param bool|array $arrRestrictionModules
     *
     * @return void
     */
    public function fixSortOnPrevIdChange($strOldPrevid, $strNewPrevid, $arrRestrictionModules = false) {
        $this->objDB->flushQueryCache();

        $arrParams = array($strOldPrevid);

        $strWhere = "";
        if($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }

        $strQuery = "SELECT system_id, system_sort
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                     AND system_id != '0'
                     ".$strWhere."
                     ORDER BY system_sort ASC";
        $arrSiblings = $this->objDB->getPArray($strQuery, $arrParams);

        $intI = 1;
        foreach($arrSiblings as $arrOneSibling) {
            if($arrOneSibling["system_sort"] != $intI) {
                $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? where system_id = ?";
                $this->objDB->_pQuery($strQuery, array($intI, $arrOneSibling["system_id"]));
            }
            $intI++;
        }

        //the new sort-id should fetch the number of siblings on the new prev-id
        $arrParams[0] = $strNewPrevid;
        $strQuery = "SELECT system_id, system_sort
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                     AND system_id != '0'
                     ".$strWhere."
                     ORDER BY system_sort ASC";
        $arrSiblings = $this->objDB->getPArray($strQuery, $arrParams);

        $intNewCount = count($arrSiblings);//$this->objSource->getNumberOfSiblings($this->objSource->getSystemid(), false);
        $this->objSource->setIntSort($intNewCount);
        $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? where system_id = ?";
        $this->objDB->_pQuery($strQuery, array($intNewCount, $this->objSource->getSystemid()));
    }

    /**
     * Fixes the sort-id of siblings when deleting a record
     *
     * @param bool|array $arrRestrictionModules
     *
     * @return mixed
     */
    public function fixSortOnDelete($arrRestrictionModules = false) {


        $arrParams = array();
        $arrParams[] = $this->objSource->getPrevId();

        $strWhere = "";
        if($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }


        $strQuery = "SELECT system_id, system_sort
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                     ".$strWhere."
                     ORDER BY system_sort ASC";
        $arrSiblings = $this->objDB->getPArray($strQuery, $arrParams);

        $arrIds = array();

        $bitHit = false;
        foreach($arrSiblings as $arrOneSibling) {

            if($bitHit) {
                $arrIds[] = $arrOneSibling["system_id"];
            }

            if($arrOneSibling["system_id"] == $this->objSource->getSystemid())
                $bitHit = true;
        }

        if(count($arrIds) > 0) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_sort = system_sort-1 where system_id IN (".implode(",", array_map(function($strVal) {return "?";}, $arrIds)).")";
            $this->objDB->_pQuery($strQuery, $arrIds);
        }
    }

    /**
     * Sets the Position of a SystemRecord in the current level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     * @return void
     * @deprecated
     */
    public function setPosition($strDirection = "upwards") {
        //get the old pos
        $intPos = $this->objSource->getIntSort();
        if($strDirection == "upwards")
            $intPos--;
        else
            $intPos++;

        $this->setAbsolutePosition($intPos);
    }


    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @throws class_exception
     * @return void
     */
    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false) {
        class_logger::getInstance()->addLogRow("move ".$this->objSource->getSystemid()." to new pos ".$intNewPosition, class_logger::$levelInfo);
        $this->objDB->flushQueryCache();

        //validate if object is sortable
        if(!$this->objSource->getLockManager()->isAccessibleForCurrentUser())
            throw new class_exception("Object is locked", class_exception::$level_ERROR);

        $arrParams = array();
        $arrParams[] = $this->objSource->getPrevId();

        $strWhere = "";
        if($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }

        //Load all elements on the same level, so at first get the prev id
        $strQuery = "SELECT *
                         FROM "._dbprefix_."system
                         WHERE system_prev_id=? AND system_id != '0'
                         ".$strWhere."
                         ORDER BY system_sort ASC, system_comment ASC";

        //No caching here to allow multiple shiftings per request
        $arrElements = $this->objDB->getPArray($strQuery, $arrParams, null, null, false);

        //more than one record to set?
        if(count($arrElements) <= 1)
            return;

        //senseless new pos?
        if($intNewPosition <= 0 || $intNewPosition > count($arrElements))
            return;

        $intCurPos = $this->objSource->getIntSort();

        if($intNewPosition == $intCurPos)
            return;


        //searching the current element to get to know if element should be sorted up- or downwards
        $bitSortDown = false;
        $bitSortUp = false;
        if($intNewPosition < $intCurPos)
            $bitSortUp = true;
        else
            $bitSortDown = true;


        //sort up?
        if($bitSortUp) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
                                SET system_sort=?
                                WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->objSource->getSystemid()));

            //start at the pos to be reached and move all one down
            for($intI = $intNewPosition; $intI < $intCurPos; $intI++) {

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort=?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI+1, $arrElements[$intI-1]["system_id"]));
            }
        }

        if($bitSortDown) {
            //move the record to be shifted to the wanted pos
            $strQuery = "UPDATE "._dbprefix_."system
                                SET system_sort=?
                                WHERE system_id=?";
            $this->objDB->_pQuery($strQuery, array(((int)$intNewPosition), $this->objSource->getSystemid()));

            //start at the pos to be reached and move all one up
            for($intI = $intCurPos+1; $intI <= $intNewPosition; $intI++) {

                $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort= ?
                            WHERE system_id=?";
                $this->objDB->_pQuery($strQuery, array($intI-1, $arrElements[$intI-1]["system_id"]));
            }
        }

        //flush the cache
        $this->objSource->flushCompletePagesCache();
        class_carrier::getInstance()->flushCache(class_carrier::INT_CACHE_TYPE_DBQUERIES | class_carrier::INT_CACHE_TYPE_ORMCACHE);
        $this->objSource->setIntSort($intNewPosition);
    }

}
