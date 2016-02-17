<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
class class_module_votings_voting extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, interface_admin_listable, interface_search_resultobject {

    /**
     * @var string
     * @tableColumn votings_voting.votings_voting_title
     * @tableColumnDatatype char254
     * @addSearchIndex
     *
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel commons_title
     *
     * @listOrder
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
        $objOrm = new class_orm_objectlist();
        if($bitOnlyActive) {
            $objOrm->addWhereRestriction(new class_orm_objectlist_systemstatus_restriction(class_orm_comparator_enum::NotEqual(), 0));
        }
        return $objOrm->getObjectList(__CLASS__, "", $intStart, $intEnd);
    }

    /**
     * Counts the answers related to the current question
     *
     * @param bool $bitOnlyActive
     *
     * @return int
     */
    public function getAllAnswersCount($bitOnlyActive = false) {

        $objOrm = new class_orm_objectlist();
        if($bitOnlyActive) {
            $objOrm->addWhereRestriction(new class_orm_objectlist_systemstatus_restriction(class_orm_comparator_enum::NotEqual(), 0));
        }
        return $objOrm->getObjectCount(__CLASS__);
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }


}
