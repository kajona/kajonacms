<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Admin\Elements;

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\Admin\AdminFormgenerator;


/**
 * Class representing the admin-part of the guestbook element
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 * @targetTable element_guestbook.content_id
 */
class ElementGuestbookAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_guestbook.guestbook_id
     * @tableColumnDatatype char20
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel guestbook_id
     */
    private $strGuestbook;

    /**
     * @var string
     * @tableColumn element_guestbook.guestbook_template
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_guestbook
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_guestbook.guestbook_amount
     * @tableColumnDatatype int
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel guestbook_amount
     */
    private $intAmount;

    /**
     * @return AdminFormgenerator|null
     */
    public function getAdminForm()
    {
        $objForm = parent::getAdminForm();

        $objGuestbooks = GuestbookGuestbook::getObjectListFiltered();
        $arrGuestbooks = array();
        foreach ($objGuestbooks as $objOneGuestbook) {
            $arrGuestbooks[$objOneGuestbook->getSystemid()] = $objOneGuestbook->getStrDisplayName();
        }

        $objForm->getField("guestbook")->setArrKeyValues($arrGuestbooks);
        return $objForm;
    }

    /**
     * @param string $strTemplate
     *
     * @return void
     */
    public function setStrTemplate($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate()
    {
        return $this->strTemplate;
    }

    /**
     * @param string $strGuestbook
     *
     * @return void
     */
    public function setStrGuestbook($strGuestbook)
    {
        $this->strGuestbook = $strGuestbook;
    }

    /**
     * @return string
     */
    public function getStrGuestbook()
    {
        return $this->strGuestbook;
    }

    /**
     * @param int $intAmount
     *
     * @return void
     */
    public function setIntAmount($intAmount)
    {
        $this->intAmount = $intAmount;
    }

    /**
     * @return int
     */
    public function getIntAmount()
    {
        return $this->intAmount;
    }


}
