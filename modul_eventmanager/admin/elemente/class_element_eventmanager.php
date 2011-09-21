<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

/**
 * Class representing the admin-part of the eventmanager element
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 */
class class_element_eventmanager extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_eventmanager";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_universal";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "char1|char,int1|number,int2|number";

		parent::__construct($arrModule);
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
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_eventmanager", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));

        $arrModeDD = array(
            "0" => $this->getText("eventmanager_mode_calendar"),
            "1" => $this->getText("eventmanager_mode_list")
        );
        $strReturn .= $this->objToolkit->formInputDropdown("int2", $arrModeDD, $this->getText("eventmanager_mode"), (isset($arrElementData["int2"]) ? $arrElementData["int2"] : "" ));
        
        $arrOrderDD = array(
            "0" => $this->getText("eventmanager_order_desc"),
            "1" => $this->getText("eventmanager_order_asc")
        );
        
        $strReturn .= $this->objToolkit->formTextRow($this->getText("eventmanager_order_hint"));
        $strReturn .= $this->objToolkit->formInputDropdown("int1", $arrOrderDD, $this->getText("eventmanager_order"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "" ));

       
		$strReturn .= $this->objToolkit->setBrowserFocus("int1");

		return $strReturn;
	}


}
?>