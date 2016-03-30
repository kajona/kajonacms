<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\Faqs\Admin\Elements;

use Kajona\Faqs\System\FaqsCategory;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;


/**
 * Class representing the admin-part of the faqs element
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 * @targetTable element_faqs.content_id
 */
class ElementFaqsAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_faqs.faqs_category
     *
     * @fieldType dropdown
     * @fieldLabel commons_category
     */
    private $strCategory;

    /**
     * @var string
     * @tableColumn element_faqs.faqs_template
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_faqs
     */
    private $strTemplate;

    public function getAdminForm()
    {
        $objForm = parent::getAdminForm();

        $arrRawCats = FaqsCategory::getObjectList();
        $arrCats = array();
        //addd an "i want all" cat ;)
        $arrCats["0"] = $this->getLang("commons_all_categories");

        foreach ($arrRawCats as $objOneCat) {
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
        }

        $objForm->getField("category")->setArrKeyValues($arrCats);
        return $objForm;
    }

    /**
     * @param string $strTemplate
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
     * @param string $strCategory
     */
    public function setStrCategory($strCategory)
    {
        $this->strCategory = $strCategory;
    }

    /**
     * @return string
     */
    public function getStrCategory()
    {
        return $this->strCategory;
    }


}
