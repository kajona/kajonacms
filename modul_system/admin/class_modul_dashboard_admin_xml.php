<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");
//model
include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_modul_dashboard_widget.php");

/**
 * admin-class of the dashboard-module
 * Serves xml-requests, mostly general requests e.g. changing a widgets position
 *
 * @package modul_dashboard
 */
class class_modul_dashboard_admin_xml extends class_admin implements interface_xml_admin {
    
    
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
		$arrModule["name"] 				= "modul_dashboard";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _dashboard_modul_id_;
		$arrModule["modul"]				= "dashboard";

		parent::__construct($arrModule);
	}


	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "setDashboardPosition")
            $strReturn .= $this->actionSetDashboardPosition();

        return $strReturn;
	}


	/**
	 * saves the new position of a widget on the dashboard.
	 * updates the sorting AND the assigned colum
	 *
	 * @return string
	 */
	private function actionSetDashboardPosition() {
	    $strReturn = "";

		//check permissions
		if($this->objRights->rightEdit($this->getSystemid())) {
		    $intNewPos = $this->getParam("listPos");
		    $strNewColumn = $this->getParam("listId");
		    if($intNewPos != "")
		        $this->setAbsolutePosition($this->getSystemid(), $intNewPos);
		        
		    $objWidget = new class_modul_dashboard_widget($this->getSystemid());
		    $objWidget->setStrColumn($strNewColumn);
		    $objWidget->updateObjectToDb();
		        
		    $this->setEditDate($this->getSystemid());    
		        
		    $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setDashboardPosition")."</message>";    
		}
		else
		    $strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";

        return $strReturn;
	}


}
?>