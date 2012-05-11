<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Class representing the admin-part of the downloads element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_element_downloads_admin extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->setArrModuleEntry("name", "element_downloads");
		$this->setArrModuleEntry("table", _dbprefix_."element_downloads");
		$this->setArrModuleEntry("tableColumns", "download_id,download_template,download_amount");

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
		//Load all archives
        $arrObjArchs = class_module_mediamanager_repo::getAllRepos();
        $arrArchives = array();
        foreach ($arrObjArchs as $objOneArchive)
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("download_id", $arrArchives, $this->getLang("download_id"), (isset($arrElementData["download_id"]) ? $arrElementData["download_id"] : "" ));
		//Load the available templates
		$arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_mediamanager");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}

		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("download_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["download_template"]) ? $arrElementData["download_template"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("download_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["download_template"]) ? $arrElementData["download_template"] : "" ));

        $strReturn .= $this->objToolkit->formInputText("download_amount", $this->getLang("download_amount"), (isset($arrElementData["download_amount"]) ? $arrElementData["download_amount"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("download_id");

		return $strReturn;
	}


}
