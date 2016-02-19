<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Votings\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;

/**
 * Model for a voting answer itself
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 *
 * @targetTable votings_answer.votings_answer_id
 *
 * @module votings
 * @moduleId _votings_module_id_
 */
class VotingsAnswer extends Model implements ModelInterface, AdminListableInterface  {

    /**
     * @var string
     * @tableColumn votings_answer.votings_answer_text
     * @tableColumnDatatype text
     * @addSearchIndex
     *
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel form_answer_text
     */
    private $strText = "";

    /**
     * @var int
     * @tableColumn votings_answer.votings_answer_hits
     * @tableColumnDatatype int
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
        return "icon_question";
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
