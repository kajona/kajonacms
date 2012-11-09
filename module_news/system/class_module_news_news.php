<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_news_news.php 4051 2011-08-03 16:11:39Z sidler $                                    *
********************************************************************************************************/

/**
 * Model for a news itself
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable news.news_id
 */
class class_module_news_news extends class_model implements interface_model, interface_admin_listable, interface_versionable {

    /**
     * @var string
     * @tableColumn news.news_title
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @versionable
     */
    private $strTitle = "";

    /**
     * @var string
     * @tableColumn news.news_image
     *
     * @fieldType image
     *
     * @versionable
     */
    private $strImage = "";

    /**
     * @var int
     * @tableColumn news.news_hits
     */
    private $intHits = 0;

    /**
     * @var string
     * @tableColumn news.news_intro
     * @fieldType textarea
     *
     * @versionable
     */
    private $strIntro = "";

    /**
     * @var string
     * @tableColumn news.news_text
     * @blockEscaping
     *
     * @fieldType wysiwygsmall
     *
     * @versionable
     */
    private $strText = "";

    /**
     * @var int
     * @fieldType date
     * @fieldLabel form_news_datestart
     *
     * @versionable
     */
    private $longDateStart = 0;

    /**
     * @var int
     * @fieldType date
     * @fieldLabel form_news_dateend
     *
     * @versionable
     */
    private $longDateEnd = 0;

    /**
     * @var int
     * @fieldType date
     * @fieldLabel form_news_datespecial
     *
     * @versionable
     */
    private $longDateSpecial = 0;

    private $arrCats = null;

    private $bitTitleChanged = false;

    private $bitUpdateMemberships = false;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _news_module_id_);
        $this->setArrModuleEntry("modul", "news");

        //base class
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_news.png";
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
        return "S: " . dateToString(new class_date($this->getIntDateStart()), false)
            . ($this->getIntDateEnd() != 0 ? " E: " . dateToString(new class_date($this->getIntDateEnd()), false) : "")
            . ($this->getIntDateSpecial() != 0 ? " A: " . dateToString(new class_date($this->getIntDateSpecial()), false) : "");
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }

    protected function initObjectInternal() {
        parent::initObjectInternal();
        $arrRow = $this->getArrInitRow();
        $this->setIntDateEnd($arrRow["system_date_end"]);
        $this->setIntDateStart($arrRow["system_date_start"]);
        $this->setIntDateSpecial($arrRow["system_date_special"]);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    protected function onInsertToDb() {

        $objStartDate = null;
        $objEndDate = null;
        $objSpecialDate = null;

        if($this->getIntDateStart() != 0 && $this->getIntDateStart() != "") {
            $objStartDate = new class_date($this->getIntDateStart());
        }

        if($this->getIntDateEnd() != 0 && $this->getIntDateEnd() != "") {
            $objEndDate = new class_date($this->getIntDateEnd());
        }

        if($this->getIntDateSpecial() != 0 && $this->getIntDateSpecial() != "") {
            $objSpecialDate = new class_date($this->getIntDateSpecial());
        }

        $bitReturn = $this->createDateRecord($this->getSystemid(), $objStartDate, $objEndDate, $objSpecialDate);

        return $bitReturn;
    }


    protected function updateStateToDb() {

        $objStartDate = null;
        $objEndDate = null;
        $objSpecialDate = null;

        if($this->getIntDateStart() != 0 && $this->getIntDateStart() != "") {
            $objStartDate = new class_date($this->getIntDateStart());
        }

        if($this->getIntDateEnd() != 0 && $this->getIntDateEnd() != "") {
            $objEndDate = new class_date($this->getIntDateEnd());
        }

        if($this->getIntDateSpecial() != 0 && $this->getIntDateSpecial() != "") {
            $objSpecialDate = new class_date($this->getIntDateSpecial());
        }

        //dates
        $this->updateDateRecord($this->getSystemid(), $objStartDate, $objEndDate, $objSpecialDate);


        if($this->bitUpdateMemberships) {

            //add records to the change-history manually
            $arrOldAssignments = class_module_news_category::getNewsMember($this->getSystemid());

            $arrOldGroupIds = array();
            foreach($arrOldAssignments as $objOneAssignment)
                $arrOldGroupIds[] = $objOneAssignment->getSystemid();

            $arrNewGroupIds = array_keys($this->arrCats);

            $arrChanges = array(
                array("property" => "assignedCategories", "oldvalue" => $arrOldGroupIds, "newvalue" => $arrNewGroupIds)
            );
            $objChanges = new class_module_system_changelog();
            $objChanges->processChanges($this, "editCategoryAssignments", $arrChanges);

            class_module_news_category::deleteNewsMemberships($this->getSystemid());
            //insert all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO " . _dbprefix_ . "news_member
                            (newsmem_id, newsmem_news, newsmem_category) VALUES
                            (?, ?, ?)";
                if(!$this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getSystemid(), $strCatID))) {
                    return false;
                }
            }
        }

        $this->bitTitleChanged = false;

        return parent::updateStateToDb();
    }

    public function copyObject($strNewPrevid = "") {
        $arrMemberCats = class_module_news_category::getNewsMember($this->getSystemid());
        $this->arrCats = array();
        foreach($arrMemberCats as $objOneCat) {
            $this->arrCats[$objOneCat->getSystemid()] = "1";
        }
        $this->bitUpdateMemberships = true;
        return parent::copyObject($strNewPrevid);
    }

    /**
     * Loads all news from the database
     * if passed, the filter is used to load the news of the given category
     * If a start and end value is given, just a section of the list is being loaded
     *
     * @param string $strFilter
     * @param int $intStart
     * @param int $intEnd
     * @param class_date $objStartDate
     * @param class_date $objEndDate
     *
     * @return class_module_news_news[]
     * @static
     */
    public static function getObjectList($strFilter = "", $intStart = null, $intEnd = null, class_date $objStartDate = null, class_date $objEndDate = null) {
        $arrParams = array();

        $strDateWhere = "";
        if($objStartDate != null && $objEndDate != null) {
            $strDateWhere = "AND (system_date_start >= ? and system_date_start < ?) ";
            $arrParams[] = $objStartDate->getLongTimestamp();
            $arrParams[] = $objEndDate->getLongTimestamp();
        }

        if($strFilter != "") {
            $strQuery = "SELECT system_id
							FROM " . _dbprefix_ . "news,
							      " . _dbprefix_ . "system,
							      " . _dbprefix_ . "system_date,
							      " . _dbprefix_ . "news_member
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND news_id = system_date_id
							  " . $strDateWhere . "
							  AND newsmem_category = ?
							ORDER BY system_date_start DESC";
            $arrParams = array(dbsafeString($strFilter));
        }
        else {
            $strQuery = "SELECT system_id
							FROM " . _dbprefix_ . "news,
							      " . _dbprefix_ . "system,
							      " . _dbprefix_ . "system_date
							WHERE system_id = news_id
							  AND system_id = system_date_id
							  " . $strDateWhere . "
							ORDER BY system_date_start DESC";
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_news_news($arrOneId["system_id"]);
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
        if($strFilter != "") {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "news_member
							WHERE newsmem_category = ?";
            $arrParams[] = $strFilter;
        }
        else {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "news";
        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Deletes the given news and all relating memberships
     *
     * @return bool
     */
    public function deleteObject() {
        //Delete memberships
        if(class_module_news_category::deleteNewsMemberships($this->getSystemid())) {
            return parent::deleteObject();
        }
        return false;
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
        $longNow = class_date::getCurrentTimestamp();
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

        //check if news should be ordered de- or ascending
        if($intOrder == 0) {
            $strOrder = "DESC";
        }
        else {
            $strOrder = "ASC";
        }

        if($strCat != "0") {
            $strQuery = "SELECT system_id
                            FROM " . _dbprefix_ . "news,
                                 " . _dbprefix_ . "news_member,
                                 " . _dbprefix_ . "system,
                                 " . _dbprefix_ . "system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND news_id = newsmem_news
                              AND newsmem_category = ?
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                " . $strTime . "
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start " . $strOrder;
            $arrParams[] = $strCat;
            $arrParams[] = $longNow;
            if($strTime != "") {
                $arrParams[] = $longNow;
            }
            $arrParams[] = $longNow;

        }
        else {
            $strQuery = "SELECT system_id
                            FROM " . _dbprefix_ . "news,
                                 " . _dbprefix_ . "system,
                                 " . _dbprefix_ . "system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                " . $strTime . "
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start " . $strOrder;

            $arrParams[] = $longNow;
            if($strTime != "") {
                $arrParams[] = $longNow;
            }
            $arrParams[] = $longNow;
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_news_news($arrOneId["system_id"]);
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
        if(($strProperty == "longDateStart" || $strProperty == "longDateEnd" || $strProperty == "longDateSpecial") && $strValue > 0)
            return dateToString(new class_date($strValue), false);

        else if($strProperty == "assignedCategories" && validateSystemid($strValue)) {
            $objCategory = new class_module_news_category($strValue);
            return $objCategory->getStrTitle();
        }

        return $strValue;
    }


    public function getStrTitle() {
        return $this->strTitle;
    }

    public function getStrIntro() {
        return $this->strIntro;
    }

    public function getStrText() {
        return $this->strText;
    }

    public function getStrImage() {
        return $this->strImage;
    }

    public function getIntHits() {
        return $this->intHits;
    }

    public function getIntDateStart() {
        return $this->longDateStart;
    }

    public function getIntDateEnd() {
        return $this->longDateEnd;
    }

    public function getIntDateSpecial() {
        return $this->longDateSpecial;
    }

    public function setLongDateEnd($longDateEnd) {
        $this->longDateEnd = $longDateEnd;
    }

    public function getLongDateEnd() {
        return $this->longDateEnd;
    }

    public function setLongDateSpecial($longDateSpecial) {
        $this->longDateSpecial = $longDateSpecial;
    }

    public function getLongDateSpecial() {
        return $this->longDateSpecial;
    }

    public function setLongDateStart($longDateStart) {
        $this->longDateStart = $longDateStart;
    }

    public function getLongDateStart() {
        return $this->longDateStart;
    }

    public function getArrCats() {
        return $this->arrCats;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
        $this->bitTitleChanged = true;
    }

    public function setStrIntro($strIntro) {
        $this->strIntro = $strIntro;
    }

    public function setStrText($strText) {
        $this->strText = $strText;
    }

    public function setStrImage($strImage) {
        $this->strImage = $strImage;
    }

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    public function setIntDateStart($intDateStart) {
        $this->longDateStart = $intDateStart;
    }

    public function setIntDateEnd($intDateEnd) {
        $this->longDateEnd = $intDateEnd;
    }

    public function setIntDateSpecial($intDateSpecial) {
        $this->longDateSpecial = $intDateSpecial;
    }

    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }

    public function setBitUpdateMemberships($bitUpdateMemberships) {
        $this->bitUpdateMemberships = $bitUpdateMemberships;
    }

}
