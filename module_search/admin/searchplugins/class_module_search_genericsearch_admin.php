<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Backend search plugin, generic approach.
 * Scans all model-classes for possible db-mapping information and builds queries to
 * find matches.
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
class class_module_search_genericsearch_admin implements interface_search_plugin  {

    /* @var class_module_search_search */
    private $objSearch;

    /**
     * @var class_search_result
     */
    private $arrHits = array();
    private $objDB;

    public function  __construct(class_module_search_search $objSearch) {
        $this->objSearch = $objSearch;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {

        //get all model-classes to scan
        $arrClasses = $this->getModelReflectionClasses();

        $arrClassesFiltered = array();
        foreach($arrClasses as $strOneClass){
            /** @var $objOneClass interface_admin_listable|class_model */
            $objOneClass = new $strOneClass();
            if ( in_array($objOneClass->getArrModule("moduleId"), $this->objSearch->getArrFilterModules()))
                $arrClassesFiltered[] = $strOneClass;
        }


        foreach($arrClassesFiltered as $strOneClass)
            $this->processSingleClass($strOneClass);

        return $this->arrHits;
    }


    private function processSingleClass($strClassname) {
        $objReflection = new class_reflection($strClassname);

        $objPhpRef = new ReflectionClass($strClassname);
        if(!$objPhpRef->implementsInterface("interface_admin_listable"))
            return;

        $arrTargetTables = $objReflection->getAnnotationValuesFromClass("@targetTable");

        if(count($arrTargetTables) > 0 ) {

            $strWhere = "";
            $arrTables = array();
            foreach($arrTargetTables as $strOneTable) {
                $arrOneTable = explode(".", $strOneTable);
                $strWhere .= "AND system_id=".$arrOneTable[1]." ";
                $arrTables[] = _dbprefix_.$arrOneTable[0];
            }

            //build the like-statements
            $arrWhere = array();
            $arrParams = array();

            $arrProperties = $objReflection->getPropertiesWithAnnotation("@tableColumn");
            foreach($arrProperties as $strProperty => $strColumn) {

                //is it a string-based field?
                $strTypeAnnotation = $objReflection->getAnnotationValueForProperty($strProperty, "@var");

                if($strTypeAnnotation == "int" || $strTypeAnnotation == "long" || $strTypeAnnotation == "bool")
                    continue;

                $arrColumn = explode(".", $strColumn);
                if(count($arrColumn) == 2)
                    $strColumn = _dbprefix_.$strColumn;

                $arrWhere[] = $strColumn ." LIKE ? ";
                $arrParams[] = "%".$this->objSearch->getStrQuery()."%";
            }

            if($this->objSearch->getObjChangeEnddate() != null) {
                $arrParams[] = $this->objSearch->getObjChangeEnddate()->getTimeInOldStyle();
                $strWhere .= "AND system_lm_time <= ? ";
            }

            if($this->objSearch->getObjChangeStartdate() != null) {
                $arrParams[] = $this->objSearch->getObjChangeStartdate()->getTimeInOldStyle();
                $strWhere .= "AND system_lm_time >= ? ";
            }

            $strQuery = "SELECT system_id
                          FROM ".implode(", ", $arrTables)." ,
                               ".$this->objDB->encloseTableName(_dbprefix_."system")."
                         WHERE ( ".implode(" OR ", $arrWhere)." )
                            ".$strWhere."";

            $arrRows = $this->objDB->getPArray($strQuery, $arrParams);

            foreach($arrRows as $arrOneRow) {
                $objInstance = class_objectfactory::getInstance()->getObject($arrOneRow["system_id"]);

                if($objInstance != null && $objInstance->rightView()) {
                    $objResult = new class_search_result();
                    $objResult->setObjObject($objInstance);
                    $this->arrHits[] = $objResult;
                }
            }

        }
    }


    /**
     * @return string[]
     */
    private function getModelReflectionClasses() {
        $arrReturn = array();
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system", array(".php"));

        foreach($arrFiles as $strOneFile) {
            if(uniStripos($strOneFile, "class_module_") !== false) {
                $objClass = new ReflectionClass(uniSubstr($strOneFile, 0, -4));
                if(!$objClass->isAbstract() && $objClass->implementsInterface("interface_admin_listable") && $objClass->isSubclassOf("class_root")) {
                    $arrReturn[] = uniSubstr($strOneFile, 0, -4);
                }
            }
        }
        return $arrReturn;
    }

}

