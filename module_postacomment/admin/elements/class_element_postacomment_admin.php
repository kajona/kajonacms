<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Class representing the admin-part of the postacomment element
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 */
class class_element_postacomment_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->setArrModuleEntry("name", "element_postacomment");
		$this->setArrModuleEntry("table", _dbprefix_."element_universal");
		$this->setArrModuleEntry("tableColumns", "char1,char2,int1");
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

        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_postacomment");
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

        $strReturn .= $this->objToolkit->formTextRow($this->getLang("postacomment_actionfilter_hint"));
        $strReturn .= $this->objToolkit->formInputText("char2", $this->getLang("postacomment_actionfilter"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));
        $strReturn .= $this->objToolkit->formInputText("int1", $this->getLang("postacomment_numberofposts"), (isset($arrElementData["int1"]) ? $arrElementData["int1"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}


}
