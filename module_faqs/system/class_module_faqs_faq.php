<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a faq itself
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @targetTable faqs.faqs_id
 */
class class_module_faqs_faq extends class_model implements interface_model, interface_sortable_rating, interface_admin_listable, interface_versionable {

    /**
     * @var string
     * @tableColumn faqs.faqs_question
     * @versionable
     *
     * @fieldType text
     * @fieldMandatory
     */
    private $strQuestion = "";

    /**
     * @var string
     * @tableColumn faqs.faqs_answer
     * @blockEscaping
     * @versionable
     *
     * @fieldType wysiwygsmall
     * @fieldMandatory
     */
    private $strAnswer = "";

    private $arrCats = array();

    private $updateBitMemberships = false;

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
        return "faq";
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
        return $strValue;
    }


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _faqs_module_id_);
        $this->setArrModuleEntry("modul", "faqs");

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
        return "icon_question.png";
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
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return uniSubstr($this->getStrQuestion(), 0, 200);
    }


    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        //delete all relations
        if($this->updateBitMemberships) {
            class_module_faqs_category::deleteFaqsMemberships($this->getSystemid());
            //insert all memberships
            foreach(array_keys($this->arrCats) as $strCatID) {
                $strQuery = "INSERT INTO " . _dbprefix_ . "faqs_member
                            (faqsmem_id, faqsmem_faq, faqsmem_category) VALUES
                            (?, ?, ?)";

                $this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getSystemid(), $strCatID));
            }
        }
        return parent::updateStateToDb();

    }

    public function copyObject($strNewPrevid = "") {
        $arrMemberCats = class_module_faqs_category::getFaqsMember($this->getSystemid());
        $this->arrCats = array();
        foreach($arrMemberCats as $objOneCat) {
            $this->arrCats[$objOneCat->getSystemid()] = "1";
        }
        $this->updateBitMemberships = true;
        return parent::copyObject($strNewPrevid);
    }


    /**
     * Loads all faqs from the database
     * if passed, the filter is used to load the faqs of the given category
     *
     * @param string $strFilter
     * @param null $intStart
     * @param null $intEnd
     *
     * @return mixed
     * @static
     */
    public static function getObjectList($strFilter = "", $intStart = null, $intEnd = null) {
        $arrParams = array();
        if($strFilter != "") {
            $strQuery = "SELECT system_id
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system,
							     " . _dbprefix_ . "faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = ?
							ORDER BY faqs_question ASC";
            $arrParams[] = $strFilter;
        }
        else {
            $strQuery = "SELECT system_id
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system
							WHERE system_id = faqs_id
							ORDER BY faqs_question ASC";
        }

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams, $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_faqs_faq($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all faqs from the database
     * if passed, the filter is used to load the faqs of the given category
     *
     * @param string $strFilter
     *
     * @return mixed
     * @static
     */
    public static function getObjectCount($strFilter = "") {
        $arrParams = array();
        if($strFilter != "") {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system,
							     " . _dbprefix_ . "faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = ?";
            $arrParams[] = $strFilter;
        }
        else {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system
							WHERE system_id = faqs_id";
        }

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }


    public function deleteObject() {
        //Delete memberships
        if(class_module_faqs_category::deleteFaqsMemberships($this->getSystemid())) {
            return parent::deleteObject();
        }
        return false;
    }


    /**
     * Loads all faqs from the db assigned to the passed cat
     *
     * @param string $strCat
     *
     * @return class_module_faqs_faq[]
     * @static
     */
    public static function loadListFaqsPortal($strCat) {
        $arrParams = array();
        if($strCat == 1) {
            $strQuery = "SELECT system_id
    						FROM " . _dbprefix_ . "faqs,
    		                     " . _dbprefix_ . "system
    		                WHERE system_id = faqs_id
    		                  AND system_status = 1
    						ORDER BY faqs_question ASC";
        }
        else {
            $strQuery = "SELECT system_id
    						FROM " . _dbprefix_ . "faqs,
    						     " . _dbprefix_ . "faqs_member,
    		                     " . _dbprefix_ . "system
    		                WHERE system_id = faqs_id
    		                  AND faqs_id = faqsmem_faq
    		                  AND faqsmem_category = ?
    		                  AND system_status = 1
    						ORDER BY faqs_question ASC";
            $arrParams[] = $strCat;
        }
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_faqs_faq($arrOneId["system_id"]);
        }

        return $arrReturn;
    }

    public function getStrQuestion() {
        return $this->strQuestion;
    }

    public function getStrAnswer() {
        return $this->strAnswer;
    }

    public function getArrCats() {
        return $this->arrCats;
    }

    public function setStrAnswer($strAnswer) {
        $this->strAnswer = $strAnswer;
    }

    public function setStrQuestion($strQuestion) {
        $this->strQuestion = $strQuestion;
    }

    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }

    public function setUpdateBitMemberships($updateBitMemberships) {
        $this->updateBitMemberships = $updateBitMemberships;
    }

}
