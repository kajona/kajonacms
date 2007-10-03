<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_dashboard_admin.php																		*
* 	The dashboard is the start-page when loading the admin. Holds a few widgets.						*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_dashboard_admin.php 1565 2007-06-14 09:54:52Z sidler $	                            *
********************************************************************************************************/

include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");
include_once(_systempath_."/class_modul_system_adminwidget.php");


class class_modul_dashboard_admin extends class_admin implements interface_admin {
    
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 				= "modul_dashboard";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _dashboard_modul_id_;
		$arrModul["modul"]				= "dashboard";

		//Base class
		parent::__construct($arrModul);

	}
	
	public function action($strAction = "") {
	    
	}
	
	public function getOutputContent() {
	    
	}
}


?>
