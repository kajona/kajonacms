<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\SortableRatingInterface;
use Kajona\System\System\SystemModule;

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
class SearchSearch extends Model implements ModelInterface, SortableRatingInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn search_search.search_search_query
     * @tableColumnDatatype char254
     * @listOrder
     * @fieldMandatory
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     */
    private $strQuery;

    /**
     * @var string
     * @tableColumn search_search.search_search_filter_modules
     * @tableColumnDatatype char254
     */
    private $strInternalFilterModules = "-1";

    /**
     * @var string
     * @tableColumn search_search.search_search_private
     * @tableColumnDatatype int
     */
    private $intPrivate = 0;

    /**
     * For form-generation only
     *
     * @var array
     * @fieldType toggle_buttonbar
     * @fieldLabel search_modules
     */
    private $arrFormFilterModules = array();

    /**
     * For form-generation only
     *
     * @var string
     * @fieldType user
     * @fieldLabel search_users
     */
    private $strFormFilterUser = null;

    /**
     * @var null
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     */
    private $objChangeStartdate = null;

    /**
     * @var null
     * @fieldType Kajona\System\Admin\Formentries\FormentryDate
     */
    private $objChangeEnddate = null;

    /**
     * @var null
     */
    private $bitPortalObjectFilter = null;

    /**
     * @var null
     */
    private $strPortalLangFilter = null;


    public function getStrDisplayName()
    {
        return $this->getStrQuery();
    }


    /**
     * Returns the filter modules to edit the filter modules
     *
     * @return array
     */
    public function getFilterModules()
    {
        if (uniStrlen($this->strInternalFilterModules) > 0 && $this->strInternalFilterModules != "-1") {
            return explode(",", $this->strInternalFilterModules);
        }
        return array();
    }

    /**
     * Returns the user id of the record owner
     *
     * @return string
     */
    public function getFilterUser()
    {
        if (!empty($this->strFormFilterUser)) {
            return $this->strFormFilterUser;
        }
        return null;
    }

    /**
     * Returns all modules available in the module-table.
     * Limited to those with a proper title, so
     * a subset of getModuleIds() / all module-entries
     *
     * @return array
     */
    public function getPossibleModulesForFilter()
    {

        $arrFilterModules = array();

        $arrModules = SystemModule::getAllModules();
        $arrNrs = $this->getModuleNumbers();
        foreach ($arrModules as $objOneModule) {
            if (in_array($objOneModule->getIntNr(), $arrNrs) && $objOneModule->rightView()) {
                $strName = $this->getLang("modul_titel", $objOneModule->getStrName());
                if ($strName != "!modul_titel!") {
                    $arrFilterModules[$objOneModule->getIntNr()] = $strName;
                }
            }
        }

        return $arrFilterModules;
    }

    /**
     * Fetches the list of module-ids currently available in the system-table
     *
     * @return array
     */
    private function getModuleNumbers()
    {
        $strQuery = "SELECT DISTINCT system_module_nr FROM "._dbprefix_."system WHERE system_prev_id != '0' AND system_id != '0' AND system_deleted = 0";

        $arrRows = $this->objDB->getPArray($strQuery, array());

        $arrReturn = array();
        foreach ($arrRows as $arrOneRow) {
            $arrReturn[] = $arrOneRow["system_module_nr"];
        }

        return $arrReturn;
    }

    /**
     * Sets the filter modules
     *
     * @param $arrFilterModules
     */
    public function setFilterModules($arrFilterModules)
    {
        $this->strInternalFilterModules = implode(",", $arrFilterModules);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_lens";
    }


    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        return "";
    }


    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "";
    }

    /**
     * @return string
     */
    public function getStrQuery()
    {
        return $this->strQuery;
    }

    public function setStrQuery($strQuery)
    {
        $this->strQuery = trim($strQuery);
    }


    /**
     * @return array
     */
    public function getArrFilterModules()
    {
        if ($this->strInternalFilterModules != "" && $this->strInternalFilterModules != "-1") {
            return explode(",", $this->strInternalFilterModules);
        }
        else {
            return $this->getModuleNumbers();
        }
    }

    /**
     * @param array $arrFormFilterModules
     */
    public function setArrFormFilterModules($arrFormFilterModules)
    {
        if (is_array($arrFormFilterModules)) {
            $arrFormFilterModules = implode(",", $arrFormFilterModules);
        }
        $this->strInternalFilterModules = $arrFormFilterModules;
    }

    /**
     * @return array
     */
    public function getArrFormFilterModules()
    {
        if ($this->strInternalFilterModules != "" && $this->strInternalFilterModules != "-1") {
            return explode(",", $this->strInternalFilterModules);
        }
        else {
            return array();
        }
    }

    /**
     * @param string $arrFormFilterModules
     */
    public function setStrFormFilterUser($strFormFilterUser)
    {
        $this->strFormFilterUser = $strFormFilterUser;
    }

    /**
     * @return string
     */
    public function getStrFormFilterUser()
    {
        return $this->strFormFilterUser;
    }


    /**
     * @param string $strFilterModules
     */
    public function setStrInternalFilterModules($strFilterModules)
    {
        $this->strInternalFilterModules = $strFilterModules;
    }

    /**
     * @return string
     */
    public function getStrInternalFilterModules()
    {
        return $this->strInternalFilterModules;
    }

    /**
     * @param \Kajona\System\System\Date $objChangeEnddate
     */
    public function setObjChangeEnddate($objChangeEnddate)
    {
        $this->setObjEndDate($objChangeEnddate);
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjChangeEnddate()
    {
        return $this->getObjEndDate();
    }

    /**
     * @param \Kajona\System\System\Date $objChangeStartdate
     */
    public function setObjChangeStartdate($objChangeStartdate)
    {
        $this->setObjStartDate($objChangeStartdate);
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjChangeStartdate()
    {
        return $this->getObjStartDate();
    }

    /**
     * @param null $bitPortalObjectFilter
     */
    public function setBitPortalObjectFilter($bitPortalObjectFilter)
    {
        $this->bitPortalObjectFilter = $bitPortalObjectFilter;
    }

    /**
     * @return null
     */
    public function getBitPortalObjectFilter()
    {
        return $this->bitPortalObjectFilter;
    }

    /**
     * @param null $strPortalLangFilter
     */
    public function setStrPortalLangFilter($strPortalLangFilter)
    {
        $this->strPortalLangFilter = $strPortalLangFilter;
    }

    /**
     * @return null
     */
    public function getStrPortalLangFilter()
    {
        return $this->strPortalLangFilter;
    }

    /**
     * @param string $intPrivate
     */
    public function setIntPrivate($intPrivate)
    {
        $this->intPrivate = $intPrivate;
    }

    /**
     * @return string
     */
    public function getIntPrivate()
    {
        return $this->intPrivate;
    }


}
