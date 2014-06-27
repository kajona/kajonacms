<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * The objectlist class is used to load a list of objects or to count a list of objects
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectlist extends class_orm_base {

    /**
     * @var class_orm_objectlist_restriction[]
     */
    private $arrWhereRestrictions = array();


    /**
     * Internal helper, adds the where restrictions
     *
     * @param string &$strQuery
     * @param array &$arrParams
     * @return void
     */
    private function processWhereRestrictions(&$strQuery, &$arrParams) {
        foreach($this->arrWhereRestrictions as $objOneRestriction) {
            $strQuery .= $objOneRestriction->getStrWhere();
            foreach($objOneRestriction->getArrParams() as $strOneParam) {
                $arrParams[] = $strOneParam;
            }
        }
    }


    /**
     * A generic approach to count the number of object currently available.
     * This method is only a simple approach to determine the number of instances in the
     * database, if you need more specific counts, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     *
     * @return int
     */
    public function getObjectCount($strTargetClass, $strPrevid = "") {

        //build the query
        $strQuery = "SELECT COUNT(*)
                       ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" ? " AND system_prev_id = ? " : "")."";

        $arrParams = array();
        if($strPrevid != "")
            $arrParams[] = $strPrevid;

        $this->processWhereRestrictions($strQuery, $arrParams);

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];

    }


    /**
     * A generic approach to load a list of objects currently available.
     * This method is only a simple approach to determine the instances in the
     * database, if you need more specific loaders, overwrite this method or add your own
     * implementation to the derived class.
     *
     * @param string $strTargetClass
     * @param string $strPrevid
     * @param null|int $intStart
     * @param null|int $intEnd
     *
     * @return self[]
     */
    public function getObjectList($strTargetClass, $strPrevid = "", $intStart = null, $intEnd = null) {
        $objAnnotations = new class_reflection($strTargetClass);

        //try to load the sort criteria
        $arrPropertiesOrder = $objAnnotations->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_LISTORDER);

        $arrOrderByCriteria = array();
        $arrOrderByCriteria[] = " system_sort ASC ";
        if(count($arrPropertiesOrder) > 0) {
            $arrPropertiesORM = $objAnnotations->getPropertiesWithAnnotation(class_orm_base::STR_ANNOTATION_TABLECOLUMN);

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
            $strOrderBy = "ORDER BY ".implode(", ", $arrOrderByCriteria);


        $strQuery = "SELECT *
                           ".$this->getQueryBase($strTargetClass)."
                       ".($strPrevid != "" ? " AND system_prev_id = ? " : "");

        $arrParams = array();
        if($strPrevid != "")
            $arrParams[] = $strPrevid;

        $this->processWhereRestrictions($strQuery, $arrParams);

        $strQuery .= $strOrderBy;

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            class_orm_rowcache::addSingleInitRow($arrOneRow);
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);
        }

        return $arrReturn;
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



}
