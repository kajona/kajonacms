<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The default implementation of the sortmanager, used by most modules and records.
 */
class CommonSortmanager implements SortmanagerInterface
{

    /**
     * @var Database
     */
    protected $objDB;

    protected $objSource;

    public function __construct(Root $objSource)
    {
        $this->objDB = Carrier::getInstance()->getObjDB();
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
    public function fixSortOnPrevIdChange($strOldPrevid, $strNewPrevid, $arrRestrictionModules = false)
    {
        $this->objDB->flushQueryCache();

        $arrParams = array($strOldPrevid);

        $strWhere = "";
        if ($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach ($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }

        $strQuery = "SELECT system_id, system_sort
                     FROM "._dbprefix_."system
                     WHERE system_prev_id=?
                     AND system_id != '0'
                     AND system_deleted = 0
                     AND system_sort > -1
                     ".$strWhere."
                     ORDER BY system_sort ASC";
        $arrSiblings = $this->objDB->getPArray($strQuery, $arrParams);

        $intI = 1;
        foreach ($arrSiblings as $arrOneSibling) {
            if ($arrOneSibling["system_sort"] != $intI) {
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
                     AND system_deleted = 0
                     AND system_sort > -1
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
    public function fixSortOnDelete($intOldSort, $arrRestrictionModules = false)
    {

        if ($intOldSort == -1) {
            return;
        }

        $arrParams = array();
        $arrParams[] = $this->objSource->getPrevId();

        $strWhere = "";
        if ($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach ($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }


        $strQuery = "SELECT system_id, system_sort, system_deleted
                     FROM "._dbprefix_."system
                     WHERE system_prev_id =?
                     AND system_deleted = 0
                     AND system_sort > -1
                     ".$strWhere."
                     ORDER BY system_sort ASC";
        $arrSiblings = $this->objDB->getPArray($strQuery, $arrParams);

        $arrIds = array();

        foreach ($arrSiblings as $arrOneSibling) {
            //see if the current record is below the old one and shift each one
            if ($arrOneSibling["system_sort"] > $intOldSort) {
                $arrIds[] = $arrOneSibling["system_id"];
            }
        }

        if (count($arrIds) > 0) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_sort = system_sort-1 where system_deleted = 0 AND system_sort > -1 AND system_id IN (".implode(",", array_map(function ($strVal) {
                    return "?";
                }, $arrIds)).")";
            $this->objDB->_pQuery($strQuery, $arrIds);
        }
    }

    /**
     * Sets the Position of a SystemRecord in the current level one position upwards or downwards
     *
     * @param string $strDirection upwards || downwards
     *
     * @return void
     * @deprecated
     */
    public function setPosition($strDirection = "upwards")
    {
        //get the old pos
        $intPos = $this->objSource->getIntSort();
        if ($strDirection == "upwards") {
            $intPos--;
        } else {
            $intPos++;
        }

        $this->setAbsolutePosition($intPos);
    }


    /**
     * Sets the position of systemid using a given value.
     *
     * @param int $intNewPosition
     * @param array|bool $arrRestrictionModules If an array of module-ids is passed, the determination of siblings will be limited to the module-records matching one of the module-ids
     *
     * @throws Exception
     * @return void
     */
    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false)
    {
        Logger::getInstance()->addLogRow("move ".$this->objSource->getSystemid()." to new pos ".$intNewPosition, Logger::$levelInfo);
        $this->objDB->flushQueryCache();

        //validate if object is sortable
        if (!$this->objSource->getLockManager()->isAccessibleForCurrentUser()) {
            throw new Exception("Object is locked", Exception::$level_ERROR);
        }

        $arrParams = array();
        $arrParams[] = $this->objSource->getPrevId();

        $strWhere = "";
        if ($arrRestrictionModules && is_array($arrRestrictionModules)) {
            $arrMarks = array();
            foreach ($arrRestrictionModules as $strOneId) {
                $arrMarks[] = "?";
                $arrParams[] = $strOneId;
            }
            $strWhere = "AND system_module_nr IN ( ".implode(", ", $arrMarks)." )";

        }

        //Load all elements on the same level, so at first get the prev id
        $strQuery = "SELECT system_id, system_sort
                         FROM "._dbprefix_."system
                         WHERE system_prev_id=? AND system_id != '0'
                           AND system_deleted = 0
                           AND system_sort > -1
                         ".$strWhere."
                         ORDER BY system_sort ASC";

        //No caching here to allow multiple shiftings per request
        $arrElements = $this->objDB->getPArray($strQuery, $arrParams, null, null, false);

        //more than one record to set?
        if (count($arrElements) <= 1) {
            return;
        }

        //senseless new pos?
        if ($intNewPosition <= 0 || $intNewPosition > count($arrElements)) {
            return;
        }

        $arrCurObjectRow = null;
        foreach ($arrElements as $arrOneRow) {
            if ($arrOneRow["system_id"] == $this->objSource->getSystemid()) {
                $arrCurObjectRow = $arrOneRow;
            }
        }
        //fetch the current sort id rather from the database then from the object
        $intCurPos = $arrCurObjectRow["system_sort"];

        if ($intNewPosition == $intCurPos) {
            return;
        }


        //searching the current element to get to know if element should be sorted up- or downwards
        $bitSortDown = false;
        $bitSortUp = false;
        if ($intNewPosition < $intCurPos) {
            $bitSortUp = true;
        } else {
            $bitSortDown = true;
        }


        //sort up?
        if ($bitSortUp) {
            //move the record to be shifted to the wanted pos
            $this->updateRecordSort($this->objSource->getSystemid(), (int)$intNewPosition);

            //start at the pos to be reached and move all one down
            for ($intI = $intNewPosition; $intI < $intCurPos; $intI++) {
                $this->updateRecordSort($arrElements[$intI - 1]["system_id"], $intI + 1);
            }
        }

        if ($bitSortDown) {
            //move the record to be shifted to the wanted pos
            $this->updateRecordSort($this->objSource->getSystemid(), (int)$intNewPosition);

            //start at the pos to be reached and move all one up
            for ($intI = $intCurPos + 1; $intI <= $intNewPosition; $intI++) {
                $this->updateRecordSort($arrElements[$intI - 1]["system_id"], $intI - 1);
            }
        }

        //flush the cache
        Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->flushCache();
        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE);
        $this->objSource->setIntSort($intNewPosition);
    }


    /**
     * Internal helper to update a single record
     *
     * @param $strSystemid
     * @param $intPos
     */
    private function updateRecordSort($strSystemid, $intPos)
    {
        $strQuery = "UPDATE "._dbprefix_."system
                            SET system_sort= ?
                            WHERE system_id=?";
        $this->objDB->_pQuery($strQuery, array($intPos, $strSystemid));

        //update the source object if currently loaded by the objectfactory
        $objObject = Objectfactory::getInstance()->getObjectFromCache($strSystemid);
        if ($objObject !== null) {
            $objObject->setIntSort($intPos);
        }
    }
}
