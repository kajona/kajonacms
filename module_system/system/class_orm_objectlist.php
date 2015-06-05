<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The objectlist class is used to load a list of objects or to count a list of objects.
 * Therefore it's not necessary to pass an object instance when creating an instance of class_orm_objectlist.
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
 * @see class_orm_objectlist_restriction
 * @see class_orm_objectlist_orderby
 */
class class_orm_objectlist extends class_orm_base {

    /**
     * @var class_orm_objectlist_restriction[]
     */
    private $arrWhereRestrictions = array();

    /**
     * @var class_orm_objectlist_orderby[]
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
     * @see class_orm_objectlist_restriction
     * @see class_orm_objectlist_orderby
     */
    public function getObjectCount($strTargetClass, $strPrevid = "") {

        //build the query
        $strQuery = "SELECT COUNT(*)
                       ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "")."";

        $arrParams = array();
        if($strPrevid != "")
            $arrParams[] = $strPrevid;

        $this->addLogicalDeleteRestriction();
        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];

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
     * @return class_model[]|interface_model[]
     *
     * @see class_orm_objectlist_restriction
     * @see class_orm_objectlist_orderby
     */
    public function getObjectList($strTargetClass, $strPrevid = "", $intStart = null, $intEnd = null) {

        $strQuery = "SELECT *
                           ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "");

        $arrParams = array();
        if($strPrevid != "")
            $arrParams[] = $strPrevid;

        $this->addLogicalDeleteRestriction();
        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);
        $strQuery .= $this->getOrderBy(new class_reflection($strTargetClass));
        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            //Caching is only allowed if the fetched and required classes match. Otherwise there could be missing queried tables.
            if($arrOneRow["system_class"] == $strTargetClass) {
                class_orm_rowcache::addSingleInitRow($arrOneRow);
                $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
            }
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
     * @return class_model|interface_model|null
     *
     * @see class_orm_objectlist_restriction
     * @see class_orm_objectlist_orderby
     */
    public function getSingleObject($strTargetClass, $strPrevid = "") {

        $strQuery = "SELECT *
                           ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" && $strPrevid !== null ? " AND system_prev_id = ? " : "");

        $arrParams = array();
        if($strPrevid != "")
            $arrParams[] = $strPrevid;

        $this->processWhereRestrictions($strQuery, $arrParams, $strTargetClass);
        $strQuery .= $this->getOrderBy(new class_reflection($strTargetClass));
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);

        if(isset($arrRow["system_id"])) {
            class_orm_rowcache::addSingleInitRow($arrRow);
            return class_objectfactory::getInstance()->getObject($arrRow["system_id"]);
        }

        return null;
    }

    /**
     * Generates the order by statement
     *
     * @param class_reflection $objReflection
     *
     * @return string
     */
    private function getOrderBy(class_reflection $objReflection) {
        //try to load the sort criteria
        $arrPropertiesOrder = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_LISTORDER);

        $arrOrderByCriteria = array();
        foreach($this->arrOrderBy as $objOneOrder)
            $arrOrderByCriteria[] = $objOneOrder->getStrOrderBy();

        $arrOrderByCriteria[] = " system_sort ASC ";
        if(count($arrPropertiesOrder) > 0) {
            $arrPropertiesORM = $objReflection->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

            foreach($arrPropertiesOrder as $strProperty => $strAnnotation) {
                if(isset($arrPropertiesORM[$strProperty])) {

                    $arrColumn = explode(".", $arrPropertiesORM[$strProperty]);
                    if(count($arrColumn) == 2)
                        $strColumn = $arrColumn[1];
                    else
                        $strColumn = $arrColumn[0];

                    //get order
                    $strOrder = (uniStrtoupper($strAnnotation) == "DESC" ? "DESC" : "ASC");

                    //get column
                    if($strColumn != "") {
                        $arrOrderByCriteria[] = " ".$strColumn." ".$strOrder." ";
                    }
                }
            }
        }

        $strOrderBy = "";
        if(count($arrOrderByCriteria) > 0)
            $strOrderBy = "ORDER BY ".implode(" , ", $arrOrderByCriteria)." ";

        return $strOrderBy;
    }



    protected function addLogicalDeleteRestriction() {

        if(!self::$bitLogcialDeleteAvailable)
            return;

        $this->addWhereRestriction(new class_orm_objectlist_restriction($this->getDeletedWhereRestriction(), array()));
    }


    /**
     * Internal helper, adds the where restrictions
     *
     * @param string &$strQuery
     * @param array &$arrParams
     * @return void
     */
    private function processWhereRestrictions(&$strQuery, &$arrParams, $strTargetClass) {
        foreach($this->arrWhereRestrictions as $objOneRestriction) {
            $objOneRestriction->setStrTargetClass($strTargetClass);
            $strQuery .= " ".$objOneRestriction->getStrWhere()." ";
            foreach($objOneRestriction->getArrParams() as $strOneParam) {
                $arrParams[] = $strOneParam;
            }
        }
    }


    /**
     * Add a where restriction to the current queries
     *
     * @param class_orm_objectlist_restriction $objRestriction
     * @return void
     */
    public function addWhereRestriction(class_orm_objectlist_restriction $objRestriction) {
        $this->arrWhereRestrictions[] = $objRestriction;
    }

    /**
     * Add a order by restriction to the current queries
     *
     * @param class_orm_objectlist_orderby $objOrder
     *
     * @return void
     */
    public function addOrderBy(class_orm_objectlist_orderby $objOrder) {
        $this->arrOrderBy[] = $objOrder;
    }


}
