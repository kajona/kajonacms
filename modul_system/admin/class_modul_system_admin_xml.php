<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_system_admin_xml.php  																	*
* 	adminclass of the system, xml stuff																	*
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");
//model
include_once(_systempath_."/class_modul_system_common.php");

/**
 * admin-class of the system-module
 * Serves xml-requests, mostly general requests e.g. changing a records status or position in a list
 *
 * @package modul_system
 */
class class_modul_system_admin_xml extends class_admin implements interface_xml_admin {
    
    
	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
		$arrModule["name"] 				= "modul_system";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _system_modul_id_;
		$arrModule["modul"]				= "system";

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
        if($strAction == "setAbsolutePosition")
            $strReturn .= $this->actionSetAbsolutePosition();
        if($strAction == "setStatus")
            $strReturn .= $this->actionSetStatus();    

        return $strReturn;
	}


	/**
	 * saves a post in the database an returns the post as html.
	 * In case of missing fields, the form is returned again
	 *
	 * @return string
	 */
	private function actionSetAbsolutePosition() {
	    $strReturn = "";

		//check permissions
		if($this->objRights->rightEdit($this->getSystemid())) {
		    $intNewPos = $this->getParam("listPos");
		    if($intNewPos != "")
		        $this->setAbsolutePosition($this->getSystemid(), $intNewPos);
		        
		    $this->setEditDate($this->getSystemid());    
		        
		    $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setAbsolutePosOk")."</message>";    
		}
		else
		    $strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";

        return $strReturn;
	}
	
	/**
	 * Changes the status of the current systemid
	 * 
	 * @return string
	 */
	private function actionSetStatus() {
	    $strReturn = "";
	    if($this->objRights->rightEdit($this->getSystemid())) {
	    if(parent::setStatus())
	        $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setStatusOk")."</message>";
	    else
            $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setStatusError")."</message>";
	    }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";
	        
	    return $strReturn;    
	}


}
?>