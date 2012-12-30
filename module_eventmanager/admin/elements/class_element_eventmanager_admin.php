<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the eventmanager element
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 */
class class_element_eventmanager_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("name", "element_eventmanager");
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,int1,int2");
        parent::__construct();
	}

    /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";

		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_eventmanager", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

        $arrModeDD = array(
            "0" => $this->getLang("eventmanager_mode_calendar"),
            "1" => $this->getLang("eventmanager_mode_list")
        );
        $strReturn .= $this->objToolkit->formInputDropdown("int2", $arrModeDD, $this->getLang("eventmanager_mode"), (isset($arrElementData["int2"]) ? $arrElementData["int2"] : "" ));
        
        $arrOrderDD = array(
            "0" => $this->getLang("eventmanager_order_desc"),
            "1" => $this->getLang("eventmanager_order_asc")
        );
        
        $strReturn .= $this->objToolkit->formTextRow($this->getLang("eventmanager_order_hint"));
        $strReturn .= $this->objToolkit->formInputDropdown("int1", $arrOrderDD, $this->getLang("eventmanager_order"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "" ));

       
		$strReturn .= $this->objToolkit->setBrowserFocus("int1");

		return $strReturn;
	}


}
