<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


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
        $arrModul = array();
		$arrModul["name"] 			= "modul_gallery";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _gallery_modul_id_;
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
	public function action($strAction = "") {
        $strReturn = "";
        if($strAction == "syncGallery")
            $strReturn .= $this->actionSyncGallery();
        else if($strAction == "massSyncGallery")
            $strReturn .= $this->actionMassSyncGallery();
        else if($strAction == "partialSyncGallery")
            $strReturn .= $this->actionPartialSyncGallery();


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
            $strResult .= $this->getText("syncro_ende")."<br />";
            $strResult .= $this->getText("sync_add").$arrSyncs["insert"]."<br />".$this->getText("sync_del").$arrSyncs["delete"]."<br />".$this->getText("sync_upd").$arrSyncs["update"];

            $strReturn .= "<gallery>".$strResult."</gallery>";
        }
        else
            $strReturn .=  "<error>".xmlSafeString($this->getText("commons_error_permissions"))."</error>";

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
        $strResult .= $this->getText("syncro_ende")."<br />";
        $strResult .= $this->getText("sync_add").$arrSyncs["insert"]."<br />".$this->getText("sync_del").$arrSyncs["delete"]."<br />".$this->getText("sync_upd").$arrSyncs["update"];

        $strReturn .= "<gallery>".xmlSafeString(strip_tags($strResult))."</gallery>";

        class_logger::getInstance()->addLogRow("mass synced galleries: ".$strResult, class_logger::$levelInfo);
        return $strReturn;
    }

    /**
     * Syncs the gallery partially, so only a single level, and creates a small report
     *
     * @return string
     */
    private function actionPartialSyncGallery() {
        $strReturn = "";
		$strResult = "";

		$objPic = new class_modul_gallery_pic($this->getSystemid());
        $strFilename = $objPic->getStrFilename();

        if($strFilename == "") {
            $objPic = new class_modul_gallery_gallery($this->getSystemid());
            $strFilename = $objPic->getStrPath();
        }


        if($objPic->rightRight1()) {
            $arrSyncs = class_modul_gallery_pic::syncRecursive($objPic->getSystemid(), $strFilename, false);
            $strResult .= $this->getText("syncro_ende")."<br />";
            $strResult .= $this->getText("sync_add").$arrSyncs["insert"]."<br />".$this->getText("sync_del").$arrSyncs["delete"]."<br />".$this->getText("sync_upd").$arrSyncs["update"];

            $strReturn .= "<gallery>".$strResult."</gallery>";
        }
        else
            $strReturn .=  "<error>".xmlSafeString($this->getText("commons_error_permissions"))."</error>";

        class_logger::getInstance()->addLogRow("synced gallery partially >".$objPic->getStrFilename()."< ".$this->getSystemid().": ".$strResult, class_logger::$levelInfo);

		return $strReturn;
    }


}

?>