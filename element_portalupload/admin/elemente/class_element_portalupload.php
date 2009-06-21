<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

include_once(_systempath_."/class_modul_downloads_archive.php");
include_once(_systempath_."/class_modul_filemanager_repo.php");

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

		$arrModule["tableColumns"]   = "char1|char,char2|char,char3|char";

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
		$arrFmRepos = array();
		$arrDlArchives = array();
		if(!$this->getPossibleValues($arrDlArchives, $arrFmRepos)) {
		    $strReturn .= $this->objToolkit->warningBox($this->getText("portalupload_matchwarning"));
		}

		//Build the form
		//Load the available templates
		include_once(_systempath_."/class_filesystem.php");
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
		
		$arrFmDD = array();
		if(count($arrFmRepos) > 0) {
			foreach($arrFmRepos as $objOneRepo) {
				$arrFmDD[$objOneRepo->getSystemid()] = $objOneRepo->getStrName();
			}
		}
		
		
		$strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getText("portalupload_template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ));
		$strReturn .= $this->objToolkit->formInputDropdown("char2", $arrDlDD, $this->getText("portalupload_download"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : "" ));
		$strReturn .= $this->objToolkit->formInputDropdown("char3", $arrFmDD, $this->getText("portalupload_filemanager"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" ));

		return $strReturn;
	}
	
	private function getPossibleValues(&$arrDlArchives, &$arrFmRepos) {
	    $bitReturn = false;
	    
	    $arrDlArchivesAvailable = class_modul_downloads_archive::getAllArchives();
	    $arrFmReposAvailable = class_modul_filemanager_repo::getAllRepos();
	    
	    foreach($arrDlArchivesAvailable as $objOneArchive) {
	        foreach($arrFmReposAvailable as $objOneRepo) {
	            if($objOneArchive->getPath() == $objOneRepo->getStrPath()) {
	                if(!in_array($objOneArchive, $arrDlArchives))
	                   $arrDlArchives[] = $objOneArchive;
	                if(!in_array($objOneRepo, $arrFmRepos))   
	                   $arrFmRepos[] = $objOneRepo;
	                   
	                $bitReturn = true;   
	            }
	        }
	    }
	    return $bitReturn;
	}

    protected function getRequiredFields() {
        return array("char1" => "string", "char2" => "string", "char3" => "string");
    }
} 
?>