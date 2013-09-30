<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the faqs element
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 *
 * @targetTable element_faqs.content_id
 */
class class_element_faqs_admin extends class_element_admin implements interface_admin_element {

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
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_faqs
     */
    private $strTemplate;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrRawCats = class_module_faqs_category::getObjectList();
        $arrCats = array();
        //addd an "i want all" cat ;)
        $arrCats["0"] = $this->getLang("commons_all_categories");

        foreach($arrRawCats as $objOneCat) {
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
        }

        $objForm->getField("category")->setArrKeyValues($arrCats);
        return $objForm;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strCategory
     */
    public function setStrCategory($strCategory) {
        $this->strCategory = $strCategory;
    }

    /**
     * @return string
     */
    public function getStrCategory() {
        return $this->strCategory;
    }






}
