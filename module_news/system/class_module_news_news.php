<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a news itself
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable news.news_id
 *
 * @module news
 * @moduleId _news_module_id_
 * @objectValidator class_news_news_objectvalidator
 *
 * @formGenerator class_module_news_news_formgenerator
 */
class class_module_news_news extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, interface_admin_listable, interface_versionable, interface_search_portalobject {

    /**
     * @var string
     * @tableColumn news.news_title
     * @tableColumnDatatype char254
     * @addSearchIndex
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @versionable
     * @templateExport
     * @jsonExport
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn news.news_image
     * @tableColumnDatatype char254
     * @fieldType image
     *
     * @addSearchIndex
     * @versionable
     * @templateExport
     * @templateMapper urlencode
     * @jsonExport
     */
    private $strImage = "";

    /**
     * @var int
     * @tableColumn news.news_hits
     * @tableColumnDatatype int
     * @templateExport
     * @jsonExport
     */
    private $intHits = 0;

    /**
     * @var string
     * @tableColumn news.news_intro
     * @tableColumnDatatype text
     * @fieldType textarea
     * @addSearchIndex
     *
     * @versionable
     * @templateExport
     * @jsonExport
     */
    private $strIntro = "";

    /**
     * @var string
     * @tableColumn news.news_text
     * @tableColumnDatatype text
     * @blockEscaping
     * @addSearchIndex
     *
     * @fieldType wysiwygsmall
     *
     * @versionable
     * @templateExport
     * @jsonExport
     */
    private $strText = "";

    /**
     * @var int
     * @fieldType toggleonoff
     * @tableColumn news.news_redirect_enabled
     * @tableColumnDatatype int
     *
     * @versionable
     */
    private $intRedirectEnabled = 0;

    /**
     * @var string
     * @fieldType page
     * @tableColumn news.news_redirect_page
     * @tableColumnDatatype char254
     *
     * @versionable
     */
    private $strRedirectPage = "";


    /**
     * For form-rendering and versioning only
     *
     * @var int
     * @fieldType date
     * @fieldLabel form_news_datestart
     * @fieldMandatory
     *
     * @versionable
     * @templateExport
     * @templateMapper date
     * @jsonExport
     */
    private $objDateStart = 0;

    /**
     * For form-rendering and versioning only
     *
     * @var int
     * @fieldType date
     * @fieldLabel form_news_dateend
     *
     * @versionable
     * @templateExport
     * @templateMapper date
     * @jsonExport
     */
    private $objDateEnd = 0;

    /**
     * For form-rendering and versioning only
     *
     * @var int
     * @fieldType date
     * @fieldLabel form_news_datespecial
     *
     * @versionable
     * @templateExport
     * @templateMapper date
     */
    private $objDateSpecial = 0;


    /**
     * For rendering only
     * @var int
     * @templateExport
     * @templateMapper datetime
     */
    private $objDateTimeStart = 0;

    /**
     * For rendering only
     * @var int
     * @templateExport
     * @templateMapper datetime
     */
    private $objDateTimeEnd = 0;

    /**
     * For rendering only
     * @var int
     * @templateExport
     * @templateMapper datetime
     */
    private $objDateTimeSpecial = 0;


    /**
     * @var class_module_news_category[]
     * @objectList news_member (source="newsmem_news", target="newsmem_category")
     * @fieldType checkboxarray
     * @versionable
     */
    private $arrCats = null;

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_news";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getIntHits() . " " . $this->getLang("commons_hits_header", "news");
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        $strConfigValue = class_module_system_setting::getConfigValue("_news_news_datetime_");
        return "S: " . dateToString($this->getObjStartDate(), $strConfigValue == "true")
            . ($this->getObjEndDate() != null ? " E: " . dateToString($this->getObjEndDate(), $strConfigValue == "true") : "")
            . ($this->getObjSpecialDate() != null ? " A: " . dateToString($this->getObjSpecialDate(), $strConfigValue == "true") : "");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    /**
     * Loads all news from the database
     * if passed, the filter is used to load the news of the given category
     * If a start and end value is given, just a section of the list is being loaded
     *
     * @param string $strFilter
     * @param int $intStart
     * @param int $intEnd
     * @param \Kajona\System\System\Date $objStartDate
     * @param \Kajona\System\System\Date $objEndDate
     *
     * @return class_module_news_news[]
     * @static
     */
    public static function getObjectList($strFilter = "", $intStart = null, $intEnd = null, \Kajona\System\System\Date $objStartDate = null, \Kajona\System\System\Date $objEndDate = null) {
        $arrParams = array();

        $strWhere = "";
        if($objStartDate != null && $objEndDate != null) {
            $strWhere = "AND (system_date_start >= ? and system_date_start < ?) ";
            $arrParams[] = $objStartDate->getLongTimestamp();
            $arrParams[] = $objEndDate->getLongTimestamp();
        }

        $objOrm = new class_orm_objectlist();
        $strWhere .= $objOrm->getDeletedWhereRestriction();

        if($strFilter != "") {
            $strQuery = "SELECT *
							FROM " . _dbprefix_ . "news,
							      " ._dbprefix_."system_right,
							      " . _dbprefix_ . "news_member,
							      " . _dbprefix_ . "system
						LEFT JOIN " . _dbprefix_ . "system_date
						       ON system_id = system_date_id
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND system_id = right_id
							  " . $strWhere . "
							  AND newsmem_category = ?
							ORDER BY system_date_start DESC";
            $arrParams[] = $strFilter;
        }
        else {
            $strQuery = "SELECT *
							FROM " . _dbprefix_ . "news,
							      " ._dbprefix_."system_right,
							      " . _dbprefix_ . "system
					    LEFT JOIN " . _dbprefix_ . "system_date
						       ON system_id = system_date_id
							WHERE system_id = news_id
							  AND system_id = right_id
							  " . $strWhere . "
							ORDER BY system_date_start DESC";
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);
        class_orm_rowcache::addArrayOfInitRows($arrIds);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneId["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Calculates the number of news available for the
     * given cat or in total.
     *
     * @param string $strFilter
     *
     * @return int
     */
    public static function getObjectCount($strFilter = "") {
        $arrParams = array();

        $objOrm = new class_orm_objectlist();
        $strWhere = $objOrm->getDeletedWhereRestriction();

        if($strFilter != "") {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "news,
							      " ._dbprefix_."system_right,
							      " . _dbprefix_ . "news_member,
							      " . _dbprefix_ . "system
						LEFT JOIN " . _dbprefix_ . "system_date
						       ON system_id = system_date_id
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND system_id = right_id
							  " . $strWhere . "
							  AND newsmem_category = ?";
            $arrParams[] = $strFilter;
        }
        else {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "news,
							      " ._dbprefix_."system_right,
							      " . _dbprefix_ . "system
					    LEFT JOIN " . _dbprefix_ . "system_date
						       ON system_id = system_date_id
							WHERE system_id = news_id
							  AND system_id = right_id
							  " . $strWhere;
        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Counts the number of news displayed for the passed portal-setup
     *
     * @param int $intMode 0 = regular, 1 = archive
     * @param int|string $strCat
     *
     * @return int
     * @static
     */
    public static function getNewsCountPortal($intMode, $strCat = 0) {
        return count(self::loadListNewsPortal($intMode, $strCat));
    }

    /**
     * Loads all news from the db assigned to the passed cat
     *
     * @param int $intMode 0 = regular, 1 = archive
     * @param int|string $strCat
     * @param int $intOrder 0 = descending, 1 = ascending
     * @param int $intStart
     * @param int $intEnd
     *
     * @return class_module_news_news[]
     * @static
     */
    public static function loadListNewsPortal($intMode, $strCat = 0, $intOrder = 0, $intStart = null, $intEnd = null) {
        $arrParams = array();
        $longNow = \Kajona\System\System\Date::getCurrentTimestamp();
        //Get Timeintervall
        if($intMode == "0") {
            //Regular news
            $strTime = "AND (system_date_special IS NULL OR (system_date_special > ? OR system_date_special = 0))";
        }
        elseif($intMode == "1") {
            //Archivnews
            $strTime = "AND (system_date_special < ? AND system_date_special IS NOT NULL AND system_date_special != 0)";
        }
        else {
            $strTime = "";
        }

        $objOrm = new class_orm_objectlist();
        $strWhere = $objOrm->getDeletedWhereRestriction();

        //check if news should be ordered de- or ascending
        if($intOrder == 0) {
            $strOrder = "DESC";
        }
        else {
            $strOrder = "ASC";
        }

        if($strCat != "0") {
            $strQuery = "SELECT *
                            FROM " . _dbprefix_ . "news,
                                 " . _dbprefix_ . "news_member,
                                 " . _dbprefix_ . "system_right,
                                 " . _dbprefix_ . "system
                       LEFT JOIN " . _dbprefix_ . "system_date
                              ON system_id = system_date_id
                            WHERE system_id = news_id
                              AND news_id = newsmem_news
                              AND system_id = right_id
                              AND newsmem_category = ?
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                " . $strTime.$strWhere . "
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start " . $strOrder.", system_create_date DESC";
            $arrParams[] = $strCat;
            $arrParams[] = $longNow;
            if($strTime != "") {
                $arrParams[] = $longNow;
            }
            $arrParams[] = $longNow;

        }
        else {
            $strQuery = "SELECT *
                            FROM " . _dbprefix_ . "news,
                                 " . _dbprefix_ . "system_right,
                                 " . _dbprefix_ . "system
                        LEFT JOIN " . _dbprefix_ . "system_date
                               ON system_id = system_date_id
                            WHERE system_id = news_id
                              AND system_id = right_id
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                " . $strTime.$strWhere . "
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start " . $strOrder.", system_create_date DESC";

            $arrParams[] = $longNow;
            if($strTime != "") {
                $arrParams[] = $longNow;
            }
            $arrParams[] = $longNow;
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);
        class_orm_rowcache::addArrayOfInitRows($arrIds);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Increments the hits counter of the current object
     *
     * @return bool
     */
    public function increaseHits() {
        $strQuery = "UPDATE " . _dbprefix_ . "news SET news_hits = ? WHERE news_id= ? ";
        return $this->objDB->_pQuery($strQuery, array($this->getIntHits() + 1, $this->getSystemid()));
    }

    /**
     * Returns a human readable name of the action stored with the changeset.
     *
     * @param string $strAction the technical actionname
     *
     * @return string the human readable name
     */
    public function getVersionActionName($strAction) {
        return $strAction;
    }

    /**
     * Returns a human readable name of the record / object stored with the changeset.
     *
     * @return string the human readable name
     */
    public function getVersionRecordName() {
        return "news";
    }

    /**
     * Returns a human readable name of the property-name stored with the changeset.
     *
     * @param string $strProperty the technical property-name
     *
     * @return string the human readable name
     */
    public function getVersionPropertyName($strProperty) {
        return $strProperty;
    }

    /**
     * Renders a stored value. Allows the class to modify the value to display, e.g. to
     * replace a timestamp by a readable string.
     *
     * @param string $strProperty
     * @param string $strValue
     *
     * @return string
     */
    public function renderVersionValue($strProperty, $strValue) {
        if(($strProperty == "objDateStart" || $strProperty == "objDateEnd" || $strProperty == "objDateSpecial") && $strValue > 0)
            return dateToString(new \Kajona\System\System\Date($strValue), false);

        else if($strProperty == "assignedCategories" && validateSystemid($strValue)) {
            $objCategory = new class_module_news_category($strValue);
            return $objCategory->getStrTitle();
        }

        return $strValue;
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     * If you want to add additional entries to the result set, clone the result and modify
     * the new instance to your needs. Pack them in an array and they'll be merged
     * into the result set afterwards.
     * Make sure to return the passed result-object in this array, too.
     *
     * @param class_search_result $objResult
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function updateSearchResult(class_search_result $objResult) {
        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT news_detailspage
                       FROM "._dbprefix_."element_news,
                            "._dbprefix_."news_member,
                            "._dbprefix_."news,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE news_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND ( news_category = '0' OR (
                                news_category = newsmem_category
                                AND newsmem_news = news_id
                           )
                        )
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND news_view = 0
                        ".$objORM->getDeletedWhereRestriction()."
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getSystemid(), $objResult->getObjSearch()->getStrPortalLangFilter()));

        $arrReturn = array();
        foreach($arrRows as $arrOnePage) {

            //check, if the post is available on a page using the current language
            if(!isset($arrOnePage["news_detailspage"]) || $arrOnePage["news_detailspage"] == "")
                continue;

            $objDetails = class_module_pages_page::getPageByName($arrOnePage["news_detailspage"]);

            if($objDetails == null)
                continue;

            //TODO: PV position
            $objOneResult = clone $objResult;
            $objOneResult->setStrPagelink(class_link::getLinkPortal($arrOnePage["news_detailspage"], "", "_self", $this->getStrTitle(), "newsDetail", "&highlight=".urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8")), $this->getSystemid()));
            $objOneResult->setStrPagename($arrOnePage["news_detailspage"]);
            $objOneResult->setStrDescription($this->getStrIntro());

            $arrReturn[] = $objOneResult;
        }
        return $arrReturn;
    }

    /**
     * Since the portal may be split in different languages,
     * return the content lang of the current record using the common
     * abbreviation such as "de" or "en".
     * If the content is not assigned to any language, return "" instead (e.g. a single image).
     *
     * @return mixed
     */
    public function getContentLang() {
        //see if the entry is assigned to a language
        $objSet = class_module_languages_languageset::getLanguagesetForSystemid($this->getSystemid());
        if($objSet != null && $objSet->getLanguageidForSystemid($this->getSystemid()) !== null) {
            $objLang = new class_module_languages_language($objSet->getLanguageidForSystemid($this->getSystemid()));
            return $objLang->getStrName();
        }

        return "";
    }

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return "";
    }


    /**
     * @return string
     */
    public function getStrTitle() {
        return $this->strTitle;
    }

    /**
     * @return string
     */
    public function getStrIntro() {
        return $this->strIntro;
    }

    /**
     * @return string
     */
    public function getStrText() {
        return $this->strText;
    }

    /**
     * @return string
     */
    public function getStrImage() {
        return $this->strImage;
    }

    /**
     * @return int
     */
    public function getIntHits() {
        return $this->intHits;
    }


    /**
     * @param \Kajona\System\System\Date $objEndDate
     * @return void
     */
    public function setObjDateEnd($objEndDate) {
        if($objEndDate == "")
            $objEndDate = null;
        $this->setObjEndDate($objEndDate);
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateEnd() {
        return $this->getObjEndDate();
    }
    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateTimeEnd() {
        return $this->getObjEndDate();
    }

    /**
     * @param \Kajona\System\System\Date $objDateSpecial
     * @return void
     */
    public function setObjDateSpecial($objDateSpecial) {
        if($objDateSpecial == "")
            $objDateSpecial = null;
        $this->setObjSpecialDate($objDateSpecial);
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateSpecial() {
        return $this->getObjSpecialDate();
    }
    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateTimeSpecial() {
        return $this->getObjSpecialDate();
    }

    /**
     * @param \Kajona\System\System\Date $objStartDate
     * @return void
     */
    public function setObjDateStart($objStartDate) {
        if($objStartDate == "")
            $objStartDate = null;
        $this->setObjStartDate($objStartDate);
    }

    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateStart() {
        return $this->getObjStartDate();
    }
    /**
     * @return \Kajona\System\System\Date
     */
    public function getObjDateTimeStart() {
        return $this->getObjStartDate();
    }

    /**
     * @return string[]|null
     */
    public function getArrCats() {
        return $this->arrCats;
    }

    /**
     * @param string $strTitle
     * @return void
     */
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @param string $strIntro
     * @return void
     */
    public function setStrIntro($strIntro) {
        $this->strIntro = $strIntro;
    }

    /**
     * @param string $strText
     * @return void
     */
    public function setStrText($strText) {
        $this->strText = $strText;
    }

    /**
     * @param string $strImage
     * @return void
     */
    public function setStrImage($strImage) {
        $this->strImage = $strImage;
    }

    /**
     * @param int $intHits
     * @return void
     */
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    /**
     * @param string[] $arrCats
     * @return void
     */
    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }

    /**
     * @param int $intRedirectEnabled
     * @return void
     */
    public function setIntRedirectEnabled($intRedirectEnabled) {
        $this->intRedirectEnabled = $intRedirectEnabled;
    }

    /**
     * @return int
     */
    public function getIntRedirectEnabled() {
        return $this->intRedirectEnabled;
    }

    /**
     * @param string $strRedirectPage
     * @return void
     */
    public function setStrRedirectPage($strRedirectPage) {
        $this->strRedirectPage = $strRedirectPage;
    }

    /**
     * @return string
     */
    public function getStrRedirectPage() {
        return $this->strRedirectPage;
    }

}
