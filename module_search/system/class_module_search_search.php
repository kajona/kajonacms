<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_search_search.php 5071 2012-09-25 13:13:54Z sidler $                              *
********************************************************************************************************/

/**
 * Model-Class for search queries.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 3.4
 *
 * @targetTable search_search.search_search_id
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
    private $strFilterModules;

    /**
     * @param string $strFilterModules
     */
    public function setStrFilterModules($strFilterModules)
    {
        $this->strFilterModules = $strFilterModules;
    }

    /**
     * @return string
     */
    public function getStrFilterModules()
    {
        return "";
        //return $this->strFilterModules;
    }


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "search");
        $this->setArrModuleEntry("moduleId", _search_module_id_);
        $this->strFilterModules = "";

        parent::__construct($strSystemid);

    }

    public function getStrDisplayName() {
        return $this->getStrName();
    }

    public function getStrName() {
        return $this->getStrQuery();
    }

    /**
     * Returns the filter modules to edit the filter modules
     *
     * @return array
     */
    public function getFilterModules(){
        if (uniStrlen($this->strFilterModules)>0)
           return explode(",", $this->strFilterModules);
        return array();
    }

    public function getFilterModulesFilter(){
        $arrFilterModules = $this->getFilterModules();

        if (count($arrFilterModules) == 0)
        {
            $arrReturn = $this->objDB->getPArray("SELECT DISTINCT system_module_nr as module_nr
                FROM kajona_system", array());

            foreach ($arrReturn as $arrEntry){
                $arrFilterModules[] = $arrEntry["module_nr"];
            }
        }

        return $arrFilterModules;
    }

    /**
     * Sets the filter modules
     *
     * @param $arrFilterModules
     */
    public function setFilterModules($arrFilterModules){
        $this->strFilterModules = implode(",", $arrFilterModules);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_lens.png";
    }


    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * @return string
     *
     */
    public function getStrQuery() {
        return $this->strQuery;
    }

    public function setStrQuery($strQuery) {
        $this->strQuery = trim($strQuery);
    }

}
