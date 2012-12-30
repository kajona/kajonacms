<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Model for a voting answer itself
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 *
 * @targetTable votings_answer.votings_answer_id
 */
class class_module_votings_answer extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn votings_answer.votings_answer_text
     *
     * @fieldType textarea
     * @fieldMandatory
     */
    private $strText = "";

    /**
     * @var int
     * @tableColumn votings_answer.votings_answer_hits
     */
    private $intHits = 0;

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
        return $this->intHits." ".$this->getLang("commons_hits_header");
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
        return $this->getStrText();
    }


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("modul", "votings");
        $this->setArrModuleEntry("moduleId", _votings_module_id_);
        parent::__construct($strSystemid);
    }

    public function getStrText() {
        return $this->strText;
    }

    public function setStrText($strText) {
        $this->strText = $strText;
    }

    public function getIntHits() {
        return $this->intHits;
    }

    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }

    
}
