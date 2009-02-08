<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_modul_pages_admin_xml.php 2353 2008-12-31 15:22:01Z sidler $                             *
********************************************************************************************************/


//Include der Mutter-Klasse
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");
//model
include_once(_systempath_."/class_modul_downloads_file.php");
include_once(_systempath_."/class_modul_downloads_archive.php");

/**
 * admin-class of the downloads-module
 * Serves xml-requests, e.g. syncing an archive
 *
 * @package modul_downloads
 */
class class_modul_downloads_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 			= "modul_downloads";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _downloads_modul_id_;
		$arrModul["modul"]			= "downloads";

		//base class
		parent::__construct($arrModul);
	}

	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "syncArchive")
            $strReturn .= $this->actionSyncArchive();
        else if($strAction == "massSyncArchive")
            $strReturn .= $this->actionMassSyncArchive();
            

        return $strReturn;
	}




	/**
	 * Syncs the archive and creates a small report
	 *
	 * @return string
	 */
	private function actionSyncArchive() {
		$strReturn = "";
		$strResult = "";
		
		$objArchive = new class_modul_downloads_archive($this->getSystemid());
        if($objArchive->rightRight1()) {
            $arrSyncs = class_modul_downloads_file::syncRecursive($objArchive->getSystemid(), $objArchive->getPath());
            $strResult .= $this->getText("syncro_ende");
            $strResult .= $this->getText("sync_add").$arrSyncs["insert"].$this->getText("sync_del").$arrSyncs["delete"].$this->getText("sync_upd").$arrSyncs["update"];
            
            $strReturn .= "<archive>".xmlSafeString(strip_tags($strResult))."</archive>";
        }
        else
            $strReturn .=  "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";    

        class_logger::getInstance()->addLogRow("synced archive ".$this->getSystemid().": ".$strResult, class_logger::$levelInfo);
            
		return $strReturn;
	}
	
/**
     * Syncs the gallery and creates a small report
     *
     * @return string
     */
    private function actionMassSyncArchive() {
        $strReturn = "";
        $strResult = "";
        
        //load all galleries
        $arrArchives = class_modul_downloads_archive::getAllArchives();
        $arrSyncs = array( "insert" => 0, "delete" => 0, "update" => 0);
        foreach($arrArchives as $objOneArchive) {
            if($objOneArchive->rightRight1()) {
                $arrTemp = class_modul_downloads_file::syncRecursive($objOneArchive->getSystemid(), $objOneArchive->getPath());
                $arrSyncs["insert"] += $arrTemp["insert"];
                $arrSyncs["delete"] += $arrTemp["delete"];
                $arrSyncs["update"] += $arrTemp["update"];
            }
        }
        $strResult = $this->getText("syncro_ende");
        $strResult .= $this->getText("sync_add").$arrSyncs["insert"].$this->getText("sync_del").$arrSyncs["delete"].$this->getText("sync_upd").$arrSyncs["update"];
            
        $strReturn .= "<archive>".xmlSafeString(strip_tags($strResult))."</archive>";
        
            
        class_logger::getInstance()->addLogRow("mass synced archives : ".$strResult, class_logger::$levelInfo);
        return $strReturn;
    }


} 

?>