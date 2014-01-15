<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: $                                  *
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
    private $arrFilterModules = null;


    /**
     * Adds metadata-query parts to the statement to be generated
     * @param $strQuery
     * @param $arrParams
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
    }


    /**
     * @param $arrFilterModules
     * @return void
     */
    public function setFilterModules($arrFilterModules) {
        $this->arrFilterModules = $arrFilterModules;
    }

    /**
     * @return array
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

}