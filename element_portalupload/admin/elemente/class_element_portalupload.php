<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portalupload-element
 *
 * @package modul_pages
 *
 */
class class_element_portalupload extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_portalupload";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_universal";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "char1|char,char2|char";

		parent::__construct($arrModule);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		//load the arrays of download-archives and filemanager repos available
		$arrDlArchives = class_modul_downloads_archive::getAllArchives();
		

		//Build the form
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_portalupload", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}


		$arrDlDD = array();
		if(count($arrDlArchives) > 0) {
			foreach($arrDlArchives as $objOneArchive) {
				$arrDlDD[$objOneArchive->getSystemid()] = $objOneArchive->getTitle();
			}
		}



		if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
        
		$strReturn .= $this->objToolkit->formInputDropdown("char2", $arrDlDD, $this->getText("portalupload_download"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));

		$strReturn .= $this->objToolkit->setBrowserFocus("char1");

		return $strReturn;
	}

	

    public function getRequiredFields() {
        return array("char1" => "string", "char2" => "string");
    }
}
?>