<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_directorybrowser.php 3577 2011-01-17 20:07:32Z sidler $                                   *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the directorybrowser-element
 *
 * @package modul_pages
 * @author sidler@mulchprod.de
 *
 */
class class_element_directorybrowser extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_directorybrowser";
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

		//Build the form
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_directorybrowser", ".tpl");
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
        
        $strReturn .= $this->objToolkit->formInputText("char2", $this->getText("directory"), isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" , "inputText", getLinkAdminDialog("filemanager", "folderListFolderview", "&form_element=char2&folder=/portal/downloads/public", $this->getText("commons_open_browser"), $this->getText("commons_open_browser"), "icon_externalBrowser.gif", $this->getText("commons_open_browser")));

		$strReturn .= $this->objToolkit->setBrowserFocus("char2");

		return $strReturn;
	}
    
    /**
     * Required is: the path
     * 
     * @return type 
     */
    public function getRequiredFields() {
        return array("char2" => "string");
    }



}
?>