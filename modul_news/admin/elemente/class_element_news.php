<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the news element
 *
 * @package modul_news
 *
 */
class class_element_news extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_news";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_news";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]     = "news_category|char,news_view|number,news_mode|number,news_order|number,news_detailspage|char,news_template|char,news_amount|number";

		parent::__construct($arrModule);
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
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";
		//Load all newscats available
		$arrRawCats = class_modul_news_category::getCategories();
        $arrCats = array();
		$arrCats[0] = $this->getText("commons_all_cats");
        foreach ($arrRawCats as $objOneCat)
            $arrCats[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("news_category", $arrCats, $this->getText("commons_category"), (isset($arrElementData["news_category"]) ? $arrElementData["news_category"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("news_detailspage", $this->getText("news_detailspage"), (isset($arrElementData["news_detailspage"]) ? $arrElementData["news_detailspage"] : ""));

		$arrView = array( 0 => $this->getText("news_view_list"),
					      1 => $this->getText("news_view_detail"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_view", $arrView, $this->getText("news_view"), (isset($arrElementData["news_view"]) ? $arrElementData["news_view"] : "" ));

        $arrMode = array( 0 => $this->getText("news_mode_normal"),
        				  1 => $this->getText("news_mode_archive"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_mode", $arrMode, $this->getText("news_mode"), (isset($arrElementData["news_mode"]) ? $arrElementData["news_mode"] : "" ));

        $arrOrder = array( 0 => $this->getText("news_order_desc"),
        				   1 => $this->getText("news_order_asc"));
        $strReturn .= $this->objToolkit->formInputDropdown("news_order", $arrOrder, $this->getText("news_order"), (isset($arrElementData["news_order"]) ? $arrElementData["news_order"] : "" ));

        //Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_news", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("news_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["news_template"]) ? $arrElementData["news_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("news_template", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["news_template"]) ? $arrElementData["news_template"] : "" ));

        $strReturn .= $this->objToolkit->formInputText("news_amount", $this->getText("news_amount"), (isset($arrElementData["news_amount"]) ? $arrElementData["news_amount"] : ""));

        $strReturn .= $this->objToolkit->setBrowserFocus("news_category");

        return $strReturn;
	}


}
?>