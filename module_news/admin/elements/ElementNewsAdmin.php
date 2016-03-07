<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

namespace Kajona\News\Admin\Elements;

use Kajona\News\System\NewsCategory;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;

/**
 * Class representing the admin-part of the news element
 *
 * @package module_news
 * @author sidler@mulchprod.de
 *
 * @targetTable element_news.content_id
 */
class ElementNewsAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * @var string
     * @tableColumn element_news.news_category
     * @tableColumnDatatype char20
     *
     * @fieldType dropdown
     * @fieldLabel commons_category
     */
    private $strCategory;

    /**
     * @var string
     * @tableColumn element_news.news_detailspage
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel news_detailspage
     */
    private $strDetailspage;

    /**
     * @var string
     * @tableColumn element_news.news_view
     * @tableColumnDatatype int
     *
     * @fieldType dropdown
     * @fieldLabel news_view
     * @fieldDDValues [0 => news_view_list],[1 => news_view_detail]
     */
    private $intView;

    /**
     * @var string
     * @tableColumn element_news.news_mode
     * @tableColumnDatatype int
     *
     * @fieldType dropdown
     * @fieldLabel news_mode
     * @fieldDDValues [0 => news_mode_normal],[1 => news_mode_archive]
     * @fieldMandatory
     */
    private $intListMode;

    /**
     * @var string
     * @tableColumn element_news.news_order
     * @tableColumnDatatype int
     *
     * @fieldType dropdown
     * @fieldLabel news_order
     * @fieldDDValues [0 => news_order_desc],[1 => news_order_asc]
     */
    private $intOrder;

    /**
     * @var string
     * @tableColumn element_news.news_template
     * @tableColumnDatatype char254
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_news
     * @fieldMandatory
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_news.news_amount
     * @tableColumnDatatype int
     *
     * @fieldType text
     * @fieldLabel news_amount
     * @fieldMandatory
     */
    private $intAmount;


    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrRawCats = NewsCategory::getObjectList();
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
     * @param string $intListMode
     */
    public function setIntListMode($intListMode) {
        $this->intListMode = $intListMode;
    }

    /**
     * @return string
     */
    public function getIntListMode() {
        return $this->intListMode;
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
