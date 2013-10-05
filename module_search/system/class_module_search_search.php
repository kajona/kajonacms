<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model-Class for search queries.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 3.4
 * @targetTable search_search.search_search_id
 *
 * @module search
 * @moduleId _search_module_id_
 */
class class_module_search_search extends class_model implements interface_model, interface_sortable_rating, interface_admin_listable {


    /**
     * @var string
     * @tableColumn search_search_query
     * @listOrder
     * @fieldMandatory
     * @fieldType text
     */
    private $strQuery;

    /**
     * @var string
     * @tableColumn search_search_filter_modules
     * @listOrder
     */
    private $strInternalFilterModules = "-1";

    /**
     * For form-generation only
     *
     * @var array
     * @fieldType multiselect
     * @fieldLabel search_modules
     */
    private $arrFormFilterModules = array();

    /**
     * @var null
     * @fieldType date
     */
    private $objChangeStartdate = null;

    /**
     * @var null
     * @fieldType date
     */
    private $objChangeEnddate= null;


    public function getStrDisplayName() {
        return $this->getStrQuery();
    }


    /**
     * Returns the filter modules to edit the filter modules
     *
     * @return array
     */
    public function getFilterModules() {
        if(uniStrlen($this->strInternalFilterModules) > 0) {
            return explode(",", $this->strInternalFilterModules);
        }
        return array();
    }

    /**
     * Returns all modules available in the module-table.
     * Limited to those with a proper title, so
     * a subset of getModuleIds() / all module-entries
     * @return array
     */
    public function getPossibleModulesForFilter() {

        $arrFilterModules = array();

        $arrModules = class_module_system_module::getAllModules();
        $arrNrs = $this->getModuleNumbers();
        foreach($arrModules as $objOneModule) {
            if(in_array($objOneModule->getIntNr(), $arrNrs) && $objOneModule->rightView()) {
                $strName = $this->getLang("modul_titel", $objOneModule->getStrName());
                if($strName != "!modul_titel!")
                    $arrFilterModules[$objOneModule->getIntNr()] = $strName;
            }
        }

        return $arrFilterModules;
    }

    /**
     * Fetches the list of module-ids currently available in the system-table
     * @return array
     */
    private function getModuleNumbers() {
        $strQuery = "SELECT DISTINCT system_module_nr FROM "._dbprefix_."system";

        $arrRows = $this->objDB->getPArray($strQuery, array());

        $arrReturn = array();
        foreach($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["system_module_nr"];
        }

        return $arrReturn;
    }

    /**
     * Sets the filter modules
     *
     * @param $arrFilterModules
     */
    public function setFilterModules($arrFilterModules) {
        $this->strInternalFilterModules = implode(",", $arrFilterModules);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_lens";
    }


    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * @return string
     */
    public function getStrQuery() {
        return $this->strQuery;
    }

    public function setStrQuery($strQuery) {
        $this->strQuery = trim($strQuery);
    }


    /**
     * @return array
     */
    public function getArrFilterModules() {
        if($this->strInternalFilterModules != "" && $this->strInternalFilterModules != "-1") {
            return explode(",", $this->strInternalFilterModules);
        }
        else {
            return $this->getModuleNumbers();
        }
    }

    /**
     * @param array $arrFormFilterModules
     */
    public function setArrFormFilterModules($arrFormFilterModules) {
        $this->strInternalFilterModules = implode(",", $arrFormFilterModules);
    }

    /**
     * @return array
     */
    public function getArrFormFilterModules() {
        if($this->strInternalFilterModules != "" && $this->strInternalFilterModules != "-1") {
            return explode(",", $this->strInternalFilterModules);
        }
        else {
            return array();
        }
    }



    /**
     * @param string $strFilterModules
     */
    public function setStrInternalFilterModules($strFilterModules) {
        $this->strInternalFilterModules = $strFilterModules;
    }

    /**
     * @return string
     */
    public function getStrInternalFilterModules() {
        return $this->strInternalFilterModules;
    }

    /**
     * @param class_date $objChangeEnddate
     */
    public function setObjChangeEnddate($objChangeEnddate) {
        $this->setObjEndDate($objChangeEnddate);
    }

    /**
     * @return class_date
     */
    public function getObjChangeEnddate() {
        return $this->getObjEndDate();
    }

    /**
     * @param class_date $objChangeStartdate
     */
    public function setObjChangeStartdate($objChangeStartdate) {
        $this->setObjStartDate($objChangeStartdate);
    }

    /**
     * @return class_date
     */
    public function getObjChangeStartdate() {
        return $this->getObjStartDate();
    }



}
