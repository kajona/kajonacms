<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Metadata allows to filter the result based on characteristics of the current record.
 * By querying the system-table, various additional information becomes available.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 4.4
 */
class class_module_search_metadata_filter {


    /**
     * @var class_date
     */
    private $objFilterChangeStartDate;
    /**
     * @var class_date
     */
    private $objFilterChangeEndDate;

    /**
     * @var int[]
     */
    private $arrFilterModules = array();

    /**
     * @var int[]
     */
    private $arrFilterUsers = array();

    /**
     * @var string[]
     */
    private $arrFilterClasses = array();

    /**
     * @var bool
     */
    private $bitPortalSearch = false;

    /**
     * @var string
     */
    private $strPortalLang = null;

    /**
     * Adds metadata-query parts to the statement to be generated
     *
     * @param string &$strQuery
     * @param string[] &$arrParams
     * @return void
     */
    public function getQuery(&$strQuery, &$arrParams) {

        //add the module filter
        if(count($this->arrFilterModules) > 0) {
            $strIn = "";
            foreach($this->arrFilterModules as $intModuleId) {
                $arrParams[] = $intModuleId;
                $strIn .= "?,";
            }
            $strIn = substr($strIn, 0, -1);

            $strQuery .= " AND system_module_nr in (" . $strIn . ") ";
        }

        //add the user filter
        if(count($this->arrFilterUsers) > 0) {
            $strIn = "";
            foreach($this->arrFilterUsers as $strUserId) {
                $arrParams[] = $strUserId;
                $strIn .= "?,";
            }
            $strIn = substr($strIn, 0, -1);

            $strQuery .= " AND system_owner in (" . $strIn . ") ";
        }

        //add the class filter
        if(count($this->arrFilterClasses) > 0) {
            $strIn = "";
            foreach($this->arrFilterClasses as $strOneClass) {
                $arrParams[] = $strOneClass;
                $strIn .= "?,";
            }
            $strIn = substr($strIn, 0, -1);

            $strQuery .= " AND system_class in (" . $strIn . ") ";
        }

        //add the start-date filter
        if($this->objFilterChangeStartDate !== null) {
            $strQuery .= "AND system_lm_time >= ? ";
            $arrParams[] = $this->objFilterChangeStartDate->getTimeInOldStyle();
        }

        //add the end-date filter
        if($this->objFilterChangeEndDate !== null) {
            $strQuery .= "AND system_lm_time <= ? ";
            $arrParams[] = $this->objFilterChangeEndDate->getTimeInOldStyle();
        }

        if($this->getBitPortalSearch()) {
            $strQuery .= " AND D.search_ix_portal_object = 1 ";
            $strQuery .= " AND system_status = 1 ";
        }

        if($this->getStrPortalLang() != "") {
            $strQuery .= " AND ( D.search_ix_content_lang IS NULL OR D.search_ix_content_lang ='' OR D.search_ix_content_lang = ? )";
            $arrParams[] = $this->getStrPortalLang();
        }
    }


    /**
     * @param int[] $arrFilterModules
     * @return void
     */
    public function setFilterModules($arrFilterModules) {
        $this->arrFilterModules = $arrFilterModules;
    }

    /**
     * @param int[] $arrFilterUsers
     * @return void
     */
    public function setFilterUsers($arrFilterUsers) {
        $this->arrFilterUsers = $arrFilterUsers;
    }

    /**
     * @return int[]
     * @return void
     */
    public function getFilterModules() {
        return $this->arrFilterModules;
    }

    /**
     * @return void
     */
    public function resetFilterModules() {
        $this->arrFilterModules = null;
    }

    /**
     * @param \string[] $arrFilterClasses
     * @return void
     */
    public function setArrFilterClasses($arrFilterClasses) {
        $this->arrFilterClasses = $arrFilterClasses;
    }

    /**
     * @return \string[]
     */
    public function getArrFilterClasses() {
        return $this->arrFilterClasses;
    }



    /**
     * @param class_date $objChangeStartDate
     * @return void
     */
    public function setFilterChangeStartDate($objChangeStartDate) {
        $this->objFilterChangeStartDate = $objChangeStartDate;
    }

    /**
     * @return void
     */
    public function resetFilterChangeStartDate() {
        $this->objFilterChangeStartDate = null;
    }

    /**
     * @param class_date $objChangeEndDate
     * @return void
     */
    public function setFilterChangeEndDate($objChangeEndDate) {
        $this->objFilterChangeEndDate = $objChangeEndDate;
    }

    /**
     * @return void
     */
    public function resetFilterChangeEndDate() {
        $this->objFilterChangeEndDate = null;
    }

    /**
     * @param string $strPortalLang
     */
    public function setStrPortalLang($strPortalLang) {
        $this->strPortalLang = $strPortalLang;
    }

    /**
     * @return string
     */
    public function getStrPortalLang() {
        return $this->strPortalLang;
    }

    /**
     * @param boolean $bitPortalSearch
     */
    public function setBitPortalSearch($bitPortalSearch) {
        $this->bitPortalSearch = $bitPortalSearch;
    }

    /**
     * @return boolean
     */
    public function getBitPortalSearch() {
        return $this->bitPortalSearch;
    }

}