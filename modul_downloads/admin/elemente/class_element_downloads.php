<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/


//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");
//needed classes
include_once(_systempath_."/class_modul_downloads_archive.php");
include_once(_systempath_."/class_modul_downloads_file.php");

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

		$arrModule["tableColumns"]  = "download_id|char,download_template|char";

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
        include_once(_adminpath_."/class_modul_downloads_admin.php");
        $objDownloads = new class_modul_downloads_admin();
        $arrObjArchs = class_modul_downloads_archive::getAllArchives();
        $arrArchives = array();
        foreach ($arrObjArchs as $objOneArchive)
            $arrArchives[$objOneArchive->getSystemid()] = $objOneArchive->getTitle();

		//Build the form
		$strReturn .= $this->objToolkit->formInputDropdown("download_id", $arrArchives, $this->getText("download_id"), (isset($arrElementData["download_id"]) ? $arrElementData["download_id"] : "" ));
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/modul_downloads", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		$strReturn .= $this->objToolkit->formInputDropdown("download_template", $arrTemplatesDD, $this->getText("download_template"), (isset($arrElementData["download_template"]) ? $arrElementData["download_template"] : "" ));

		return $strReturn;
	}


} //class_element_downloads.php
?>