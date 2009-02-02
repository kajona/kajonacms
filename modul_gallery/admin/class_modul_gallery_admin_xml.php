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
include_once(_systempath_."/class_modul_gallery_pic.php");
include_once(_systempath_."/class_modul_gallery_gallery.php");

/**
 * admin-class of the gallery-module
 * Serves xml-requests, e.g. syncing a gallery
 *
 * @package modul_gallery
 */
class class_modul_gallery_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "modul_gallery";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _bildergalerie_modul_id_;
		$arrModul["modul"]			= "gallery";

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
        if($strAction == "syncGallery")
            $strReturn .= $this->actionSyncGallery();
        else if($strAction == "massSyncGallery")
            $strReturn .= $this->actionMassSyncGallery();
            

        return $strReturn;
	}




	/**
	 * Syncs the gallery and creates a small report
	 *
	 * @return string
	 */
	private function actionSyncGallery() {
		$strReturn = "";
		$strResult = "";
		
		$objGallery = new class_modul_gallery_gallery($this->getSystemid());
        if($objGallery->rightRight1()) {
            $arrSyncs = class_modul_gallery_pic::syncRecursive($objGallery->getSystemid(), $objGallery->getStrPath());
            $strResult .= $this->getText("syncro_ende");
            $strResult .= $this->getText("sync_add").$arrSyncs["insert"].$this->getText("sync_del").$arrSyncs["delete"].$this->getText("sync_upd").$arrSyncs["update"];
            
            $strReturn .= "<gallery>".xmlSafeString(strip_tags($strResult))."</gallery>";
        }
        else
            $strReturn .=  "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";    

        class_logger::getInstance()->addLogRow("synced gallery ".$this->getSystemid().": ".$strResult, class_logger::$levelInfo);    
            
		return $strReturn;
	}
	
/**
     * Syncs the gallery and creates a small report
     *
     * @return string
     */
    private function actionMassSyncGallery() {
        $strReturn = "";
        $strResult = "";
        
        //load all galleries
        $arrGalleries = class_modul_gallery_gallery::getGalleries();
        $arrSyncs = array( "insert" => 0, "delete" => 0, "update" => 0);
        foreach($arrGalleries as $objOneGallery) {
            if($objOneGallery->rightRight1()) {
                $arrTemp = class_modul_gallery_pic::syncRecursive($objOneGallery->getSystemid(), $objOneGallery->getStrPath());
                $arrSyncs["insert"] += $arrTemp["insert"];
                $arrSyncs["delete"] += $arrTemp["delete"];
                $arrSyncs["update"] += $arrTemp["update"];
            }
        }
        $strResult = $this->getText("syncro_ende");
        $strResult .= $this->getText("sync_add").$arrSyncs["insert"].$this->getText("sync_del").$arrSyncs["delete"].$this->getText("sync_upd").$arrSyncs["update"];
            
        $strReturn .= "<gallery>".xmlSafeString(strip_tags($strResult))."</gallery>";
        
            
        class_logger::getInstance()->addLogRow("mass synced galleries : ".$strResult, class_logger::$levelInfo);
        return $strReturn;
    }


} 

?>