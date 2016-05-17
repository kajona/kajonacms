<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\Votings\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\Votings\System\VotingsVoting;

/**
 * Class representing the admin-part of the votings element
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementVotingsAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_universal.char1
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel votings_voting
     *
     */
    private $strChar1;

    /**
     * @var string
     * @tableColumn element_universal.char2
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_votings
     */
    private $strChar2;

    /**
     * @var string
     * @tableColumn element_universal.int1
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel votings_mode
     * @fieldDDValues [0 => votings_mode_voting],[1 => votings_mode_result]
     */
    private $intInt1;


    public function getAdminForm()
    {
        $objForm = parent::getAdminForm();

        $arrRawVotings = VotingsVoting::getObjectListFiltered(null, "", null, null, true);
        $arrVotings = array();

        foreach ($arrRawVotings as $objOneVoting) {
            $arrVotings[$objOneVoting->getSystemid()] = $objOneVoting->getStrTitle();
        }

        $objForm->getField("char1")->setArrKeyValues($arrVotings);

        return $objForm;
    }

    /**
     * @param string $strChar2
     */
    public function setStrChar2($strChar2)
    {
        $this->strChar2 = $strChar2;
    }

    /**
     * @return string
     */
    public function getStrChar2()
    {
        return $this->strChar2;
    }

    /**
     * @param string $strChar1
     */
    public function setStrChar1($strChar1)
    {
        $this->strChar1 = $strChar1;
    }

    /**
     * @return string
     */
    public function getStrChar1()
    {
        return $this->strChar1;
    }

    /**
     * @param string $intInt1
     */
    public function setIntInt1($intInt1)
    {
        $this->intInt1 = $intInt1;
    }

    /**
     * @return string
     */
    public function getIntInt1()
    {
        return $this->intInt1;
    }


}
