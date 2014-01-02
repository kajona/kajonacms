<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the news element
 *
 * @package module_news
 * @author sidler@mulchprod.de
 *
 * @targetTable element_news.content_id
 */
class class_element_news_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_news.news_category
     *
     * @fieldType dropdown
     * @fieldLabel commons_category
     */
    private $strCategory;

    /**
     * @var string
     * @tableColumn element_news.news_detailspage
     *
     * @fieldType page
     * @fieldLabel news_detailspage
     */
    private $strDetailspage;

    /**
     * @var string
     * @tableColumn element_news.news_view
     *
     * @fieldType dropdown
     * @fieldLabel news_view
     * @fieldDDValues [0 => news_view_list],[1 => news_view_detail]
     */
    private $intView;

    /**
     * @var string
     * @tableColumn element_news.news_mode
     *
     * @fieldType dropdown
     * @fieldLabel news_mode
     * @fieldDDValues [0 => news_mode_normal],[1 => news_mode_archive]
     */
    private $intMode;

    /**
     * @var string
     * @tableColumn element_news.news_order
     *
     * @fieldType dropdown
     * @fieldLabel news_order
     * @fieldDDValues [0 => news_order_desc],[1 => news_order_asc]
     */
    private $intOrder;


    /**
     * @var string
     * @tableColumn element_news.news_template
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_news
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_news.news_amount
     *
     * @fieldType text
     * @fieldLabel news_amount
     * @fieldMandatory
     */
    private $intAmount;


    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrRawCats = class_module_news_category::getObjectList();
        $arrCats = array();
        $arrCats[0] = $this->getLang("commons_all_categories");
        foreach($arrRawCats as $objOneCat) {
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
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
     * @param string $strDetailspage
     */
    public function setStrDetailspage($strDetailspage) {
        $this->strDetailspage = $strDetailspage;
    }

    /**
     * @return string
     */
    public function getStrDetailspage() {
        return $this->strDetailspage;
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

    /**
     * @param string $intView
     */
    public function setIntView($intView) {
        $this->intView = $intView;
    }

    /**
     * @return string
     */
    public function getIntView() {
        return $this->intView;
    }

    /**
     * @param string $intOrder
     */
    public function setIntOrder($intOrder) {
        $this->intOrder = $intOrder;
    }

    /**
     * @return string
     */
    public function getIntOrder() {
        return $this->intOrder;
    }

    /**
     * @param string $intMode
     */
    public function setIntMode($intMode) {
        $this->intMode = $intMode;
    }

    /**
     * @return string
     */
    public function getIntMode() {
        return $this->intMode;
    }

    /**
     * @param string $intAmount
     */
    public function setIntAmount($intAmount) {
        $this->intAmount = $intAmount;
    }

    /**
     * @return string
     */
    public function getIntAmount() {
        return $this->intAmount;
    }






}
