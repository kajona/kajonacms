<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;

use ReflectionClass;


/**
 * The objectlist class is used to load a list of objects or to count a list of objects.
 * Therefore it's not necessary to pass an object instance when creating an instance of OrmObjectlist.
 *
 * Pass the class-name of the queried object-type to either
 *   - getObjectCount()
 *   - getObjectList()
 *   - getSingleObject()
 *
 * By default the generated query has no additional where-restrictions and processes
 * the property marked with @listOrder to sort the result. Nevertheless, the api
 * provides methods to add additional restrictions and sort-orders before calling the
 * getter-methods:
 *   - addOrderBy
 *   - addWhereRestriction
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 * @see OrmObjectlist_restriction
 * @see OrmObjectlist_orderby
 */
class OrmObjectlist extends OrmBase
{

    /**
     * @var OrmCondition[]
     */
    private $arrWhereRestrictions = array();

    /**
     * @var OrmObjectlistOrderby[]
     */
    private $arrOrderBy = array();


    /**
     * Counts the objects found by the currently setup query.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     *
     * @return int
     *
     * @see OrmObjectlist_restriction
     * @see OrmObjectlist_orderby
     */
    public function getObjectCount($strTargetClass, $strPrevid = "")
    {

        //build the query
        $strQuery = "SELECT COUNT(*) AS cnt
                       ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "")."";

        $arrParams = array();
        if ($strPrevid != "") {
            $arrParams[] = $strPrevid;
        }

        $this->addLogicalDeleteRestriction();
        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["cnt"];

    }


    /**
     * Returns the list of object id's matching the current query. The target-tables
     * are set up by analyzing the classes' annotations, the initial sort-order, too.
     * You may influence the ordering and restrictions by adding the relevant restriction / order
     * objects before calling this method.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return array of system ids
     *
     * @see OrmObjectlist_restriction
     * @see OrmObjectlist_orderby
     */
    public function getObjectListIds($strTargetClass, $strPrevid = "", $intStart = null, $intEnd = null)
    {

        $strQuery = "SELECT *
                           ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "");

        $arrParams = array();
        if ($strPrevid != "") {
            $arrParams[] = $strPrevid;
        }

        $this->addLogicalDeleteRestriction();
        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);
        $strQuery .= $this->getOrderBy(new Reflection($strTargetClass));
        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            //Caching is only allowed if the fetched and required classes match. Otherwise there could be missing queried tables.
            if ($arrOneRow["system_class"] == $strTargetClass) {
                OrmRowcache::addSingleInitRow($arrOneRow);
                $arrReturn[] = $arrOneRow["system_id"];
            }
            else {
                $objReflectionClass = new ReflectionClass($arrOneRow["system_class"]);
                if ($objReflectionClass->isSubclassOf($strTargetClass)) {
                    //returns the instance, but enforces a fresh reload from the database.
                    //this is useful if extending classes need to query additional tables
                    $arrReturn[] = $arrOneRow["system_id"];
                }
            }

        }

        return $arrReturn;
    }

    /**
     * Returns the list of objects matching the current query. The target-tables
     * are set up by analyzing the classes' annotations, the initial sort-order, too.
     * You may influence the ordering and restrictions by adding the relevant restriction / order
     * objects before calling this method.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return Model[]|ModelInterface[]
     *
     * @see OrmObjectlist_restriction
     * @see OrmObjectlist_orderby
     */
    public function getObjectList($strTargetClass, $strPrevid = "", $intStart = null, $intEnd = null)
    {
        $arrIds = $this->getObjectListIds($strTargetClass, $strPrevid, $intStart, $intEnd);

        $arrReturn = array();

        foreach ($arrIds as $strId) {
            $arrReturn[] = Objectfactory::getInstance()->getObject($strId);
        }

        return $arrReturn;
    }

    /**
     * Returns a single object matching the current query. The matching object is either
     * limited by the where statements set up in advance or the first record of the matching
     * result-set is returned.
     * If the query results in an empty result set, null is returned instead.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     *
     * @return Model|ModelInterface|null
     *
     * @see OrmObjectlist_restriction
     * @see OrmObjectlist_orderby
     */
    public function getSingleObject($strTargetClass, $strPrevid = "")
    {

        $strQuery = "SELECT *
                           ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "");

        $arrParams = array();
        if ($strPrevid != "") {
            $arrParams[] = $strPrevid;
        }

        $this->addLogicalDeleteRestriction();
        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);
        $strQuery .= $this->getOrderBy(new Reflection($strTargetClass));
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);

        if (isset($arrRow["system_id"])) {
            OrmRowcache::addSingleInitRow($arrRow);
            return Objectfactory::getInstance()->getObject($arrRow["system_id"]);
        }

        return null;
    }

    /**
     * Generates the order by statement
     *
     * @param Reflection $objReflection
     *
     * @return string
     */
    private function getOrderBy(Reflection $objReflection)
    {
        //try to load the sort criteria
        $arrPropertiesOrder = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_LISTORDER);

        $arrOrderByCriteria = array();
        foreach ($this->arrOrderBy as $objOneOrder) {
            $arrOrderByCriteria[] = $objOneOrder->getStrOrderBy();
        }


        if (count($arrPropertiesOrder) > 0) {
            $arrPropertiesORM = $objReflection->getPropertiesWithAnnotation(OrmBase::STR_ANNOTATION_TABLECOLUMN);

            foreach ($arrPropertiesOrder as $strProperty => $strAnnotation) {
                if (isset($arrPropertiesORM[$strProperty])) {

                    $arrColumn = explode(".", $arrPropertiesORM[$strProperty]);
                    if (count($arrColumn) == 2) {
                        $strColumn = $arrColumn[1];
                    }
                    else {
                        $strColumn = $arrColumn[0];
                    }

                    //get order
                    $strOrder = (StringUtil::toUpperCase($strAnnotation) == "DESC" ? "DESC" : "ASC");

                    //get column
                    if ($strColumn != "") {
                        $arrOrderByCriteria[] = " ".$strColumn." ".$strOrder." ";
                    }
                }
            }
        }

        $arrOrderByCriteria[] = " CASE WHEN system_sort < 0 THEN 9999999 ELSE system_sort END ASC "; //TODO: add a better way of setting the max value
        $arrOrderByCriteria[] = " system_create_date DESC ";
        $strOrderBy = "";
        if (count($arrOrderByCriteria) > 0) {
            $strOrderBy = "ORDER BY ".implode(" , ", $arrOrderByCriteria)." ";
        }

        return $strOrderBy;
    }


    protected function addLogicalDeleteRestriction()
    {

        if (!self::$bitLogcialDeleteAvailable) {
            return;
        }

        $this->addWhereRestriction(new OrmCondition($this->getDeletedWhereRestriction("", ""), array()));
    }


    /**
     * Internal helper, adds the where restrictions
     *
     * @param string &$strQuery
     * @param array &$arrParams
     *
     * @return void
     */
    private function processWhereRestrictions(&$strQuery, &$arrParams, $strTargetClass)
    {
        foreach ($this->arrWhereRestrictions as $objOneRestriction) {
            $objOneRestriction->setStrTargetClass($strTargetClass);

            $strWhere = $objOneRestriction->getStrWhere();

            if($objOneRestriction instanceof OrmConditionInterface && $strWhere != "") {
                $strWhere = OrmCondition::STR_CONDITION_AND." (".$strWhere.")";
            }

            $strQuery .= " ".$strWhere." ";
            foreach ($objOneRestriction->getArrParams() as $strOneParam) {
                $arrParams[] = $strOneParam;
            }
        }
    }


    /**
     * Add a where restriction to the current queries
     *
     * @param OrmConditionInterface $objCondition
     *
     * @return void
     */
    public function addWhereRestriction(OrmConditionInterface $objCondition)
    {
        $this->arrWhereRestrictions[] = $objCondition;
    }

    /**
     * Add a order by restriction to the current queries
     *
     * @param OrmObjectlistOrderby $objOrder
     *
     * @return void
     */
    public function addOrderBy(OrmObjectlistOrderby $objOrder)
    {
        $this->arrOrderBy[] = $objOrder;
    }


}
