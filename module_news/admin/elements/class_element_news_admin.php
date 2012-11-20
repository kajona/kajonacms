<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the news element
 *
 * @package module_news
 * @author sidler@mulchprod.de
 */
class class_element_news_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("name", "element_news");
        $this->setArrModuleEntry("table", _dbprefix_ . "element_news");
        $this->setArrModuleEntry("tableColumns", "news_category,news_view,news_mode,news_order,news_detailspage,news_template,news_amount");
        parent::__construct();
    }

    public function getRequiredFields() {
        return array(
            "news_amount" => "number"
        );
    }

    /**
     * Returns a form to edit the element-data
     *
     * @param mixed $arrElementData
     *
     * @return string
     */
    public function getEditForm($arrElementData) {
        $strReturn = "";
        //Load all newscats available
        $arrRawCats = class_module_news_category::getObjectList();
        $arrCats = array();
        $arrCats[0] = $this->getLang("commons_all_categories");
        foreach($arrRawCats as $objOneCat) {
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
        }

        //Build the form
        $strReturn .= $this->objToolkit->formInputDropdown("news_category", $arrCats, $this->getLang("commons_category"), (isset($arrElementData["news_category"]) ? $arrElementData["news_category"] : ""));
        $strReturn .= $this->objToolkit->formInputPageSelector("news_detailspage", $this->getLang("news_detailspage"), (isset($arrElementData["news_detailspage"]) ? $arrElementData["news_detailspage"] : ""));

        $arrView = array(0 => $this->getLang("news_view_list"),
                         1 => $this->getLang("news_view_detail"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_view", $arrView, $this->getLang("news_view"), (isset($arrElementData["news_view"]) ? $arrElementData["news_view"] : ""));

        $arrMode = array(0 => $this->getLang("news_mode_normal"),
                         1 => $this->getLang("news_mode_archive"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_mode", $arrMode, $this->getLang("news_mode"), (isset($arrElementData["news_mode"]) ? $arrElementData["news_mode"] : ""));

        $arrOrder = array(0 => $this->getLang("news_order_desc"),
                          1 => $this->getLang("news_order_asc"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_order", $arrOrder, $this->getLang("news_order"), (isset($arrElementData["news_order"]) ? $arrElementData["news_order"] : ""));

        //Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_news", ".tpl");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        if(count($arrTemplates) == 1) {
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("news_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["news_template"]) ? $arrElementData["news_template"] : "")));
        }
        else {
            $strReturn .= $this->objToolkit->formInputDropdown("news_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["news_template"]) ? $arrElementData["news_template"] : ""));
        }

        $strReturn .= $this->objToolkit->formInputText("news_amount", $this->getLang("news_amount"), (isset($arrElementData["news_amount"]) ? $arrElementData["news_amount"] : ""));

        $strReturn .= $this->objToolkit->setBrowserFocus("news_category");

        return $strReturn;
    }

}
