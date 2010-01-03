<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Class representing the admin-part of the downloads element
 *
 * @package modul_downloads
 */
class class_element_downloads extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_downloads";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_downloads";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "download_id|char,download_template|char,download_amount|number";

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
		//Load all archives
        $objDownloads = new class_modul_downloads_admin();
        $arrObjArchs = class_modul_downloads_archive::getAllArchives();
        $arrArchives = array();
        foreach ($arrObjArchs as $objOneArchive)
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("download_id", $arrArchives, $this->getText("download_id"), (isset($arrElementData["download_id"]) ? $arrElementData["download_id"] : "" ));
		//Load the available templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_downloads", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("download_template", $arrTemplatesDD, $this->getText("download_template"), (isset($arrElementData["download_template"]) ? $arrElementData["download_template"] : "" ));

        $strReturn .= $this->objToolkit->formInputText("download_amount", $this->getText("download_amount"), (isset($arrElementData["download_amount"]) ? $arrElementData["download_amount"] : ""));

		$strReturn .= $this->objToolkit->setBrowserFocus("download_id");

		return $strReturn;
	}


} //class_element_downloads.php
?>