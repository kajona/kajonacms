<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
 *
 * @module faqs
 * @moduleId _faqs_module_id_
 */
class class_module_faqs_faq extends class_model implements interface_model, interface_sortable_rating, interface_admin_listable, interface_versionable, interface_search_portalobject {

    /**
     * @var string
     * @tableColumn faqs.faqs_question
     * @versionable
     * @addSearchIndex
     * @listOrder
     *
     * @fieldType text
     * @fieldMandatory
     * @templateExport
     */
    private $strQuestion = "";

    /**
     * @var string
     * @tableColumn faqs.faqs_answer
     * @blockEscaping
     * @versionable
     * @addSearchIndex
     *
     * @fieldType wysiwygsmall
     * @fieldMandatory
     * @templateExport
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
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_question";
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
            $arrValues = array();
            foreach(array_keys($this->arrCats) as $strCatID) {
                $arrValues[] = array(generateSystemid(), $this->getSystemid(), $strCatID);
            }

            $this->objDB->multiInsert(
                "faqs_member",
                array("faqsmem_id", "faqsmem_faq", "faqsmem_category"),
                $arrValues
            );
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
        if($strFilter != "") {
            $strQuery = "SELECT system_id
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system,
							     " . _dbprefix_ . "faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = ?
							ORDER BY faqs_question ASC";

            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFilter), $intStart, $intEnd);

            $arrReturn = array();
            foreach($arrIds as $arrOneId) {
                $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneId["system_id"]);
            }

            return $arrReturn;
        }
        else {
            return parent::getObjectList("", $intStart, $intEnd);
        }

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
        if($strFilter != "") {
            $strQuery = "SELECT COUNT(*)
							FROM " . _dbprefix_ . "faqs,
							     " . _dbprefix_ . "system,
							     " . _dbprefix_ . "faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = ?";
            $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strFilter));
            return $arrRow["COUNT(*)"];
        }
        else {
            return parent::getObjectCount();
        }

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

    /**
     * Return an on-lick link for the passed object.
     * This link is rendered by the portal search result generator, so
     * make sure the link is a valid portal page.
     * If you want to suppress the entry from the result, return an empty string instead.
     *
     * @param class_search_result $objResult
     *
     * @see getLinkPortalHref()
     * @return mixed
     */
    public function updateSearchResult(class_search_result $objResult) {
        //search for matching pages
        $strQuery = "SELECT page_name,  page_id
                       FROM " . _dbprefix_ . "element_faqs,
                            " . _dbprefix_ . "faqs
                  LEFT JOIN " . _dbprefix_ . "faqs_member
                         ON (faqsmem_faq = faqs_id),
                            " . _dbprefix_ . "page_element,
                            " . _dbprefix_ . "page,
                            " . _dbprefix_ . "system
                      WHERE faqs_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND (
                            faqs_category IS NULL OR (
                                faqs_category = '0' OR faqs_category = faqsmem_category
                            )
                        )
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND page_element_ph_language = ? ";

        $arrRows = $this->objDB->getPArray($strQuery, array($this->getSystemid(), $objResult->getObjSearch()->getStrPortalLangFilter()));

        $arrReturn = array();

        foreach($arrRows as $arrOnePage) {

            //check, if the post is available on a page using the current language
            if(!isset($arrOnePage["page_name"]) || $arrOnePage["page_name"] == "") {
                continue;
            }

            $objCurResult = clone($objResult);
            $objCurResult->setStrPagelink(class_link::getLinkPortal($arrOnePage["page_name"], "", "_self", $arrOnePage["page_name"], "", "&highlight=" . urlencode(html_entity_decode($objResult->getObjSearch()->getStrQuery(), ENT_QUOTES, "UTF-8"))));
            $objCurResult->setStrPagename($arrOnePage["page_name"]);
            $objCurResult->setStrDescription($this->getStrQuestion());
            $arrReturn[] = $objCurResult;
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
        //the default, plz
        return "";
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
