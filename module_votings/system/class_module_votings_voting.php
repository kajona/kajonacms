<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a single voting, so the entry to a voting.
 * Represents the title
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 * @targetTable votings_voting.votings_voting_id
 *
 * @module votings
 * @moduleId _votings_module_id_
 */
class class_module_votings_voting extends class_model implements interface_model, interface_admin_listable, interface_search_resultobject {

    /**
     * @var string
     * @tableColumn votings_voting.votings_voting_title
     *
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";

    /**
     * @var int
     * @fieldType date
     * @fieldLabel form_voting_datestart
     */
    private $objStartDate = null;

    /**
     * @var int
     * @fieldType date
     * @fieldLabel form_voting_dateend
     */
    private $objEndDate = null;

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return getLinkAdminHref("votings", "listAnswers", "&systemid=".$this->getSystemid());
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
        return $this->getStrTitle();
    }


    /**
     * Loads all available categories from the db,
     * so a kind of factory method for voting-object
     *
     * @param bool $bitOnlyActive
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return class_module_votings_voting[]
     * @static
     */
    public static function getObjectList($bitOnlyActive = false, $intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id FROM " . _dbprefix_ . "votings_voting,
						" . _dbprefix_ . "system
						WHERE system_id = votings_voting_id
						" . ($bitOnlyActive ? " AND system_status = 1 " : "") . "
						ORDER BY votings_voting_title";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_votings_voting($arrOneId["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Loads the answers related to the current question
     *
     * @param bool $bitOnlyActive
     * @param null $intStart
     * @param null $intEnd
     *
     * @return class_module_votings_answer[]
     */
    public function getAllAnswers($bitOnlyActive = false, $intStart = null, $intEnd = null) {

        $strQuery = "SELECT system_id
                       FROM " . _dbprefix_ . "votings_answer,  " . _dbprefix_ . "system
                      WHERE system_prev_id=?
                        AND system_id = votings_answer_id
                        " . ($bitOnlyActive ? " AND system_status = 1 " : "") . "
                      ORDER BY system_sort ASC, votings_answer_text ASC";
        $arrQuery = $this->objDB->getPArray($strQuery, array($this->getSystemid()), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrQuery as $arrSingleRow) {
            $arrReturn[] = new class_module_votings_answer($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }

    /**
     * Counts the answers related to the current question
     *
     * @param bool $bitOnlyActive
     *
     * @return int
     */
    public function getAllAnswersCount($bitOnlyActive = false) {
        $strQuery = "SELECT COUNT(*)
                       FROM " . _dbprefix_ . "votings_answer,  " . _dbprefix_ . "system
                      WHERE system_prev_id=?
                        AND system_id = votings_answer_id
                        " . ($bitOnlyActive ? " AND system_status = 1 " : "") . "";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        return $arrRow["COUNT(*)"];
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }


}
