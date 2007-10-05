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
*	$Id$	                            *
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
	    if($strAction == "")
	        $strAction = "list";
	        
	    if($strAction == "list") {
	        $this->strOutput = $this->actionList();
	    }    
	    else if($strAction == "addWidgetToDashboard") {
	        $this->strOutput = $this->actionAddWidgetToDashboard();    
	    }
	}
	
    
	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("moduleRights"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "addWidgetToDashboard", "", $this->getText("addWidget"), "", "", true, "adminnavi"));
		return $arrReturn;
	}
	
	public function getOutputContent() {
	    return $this->strOutput;
	}
	
	/**
	 * Generates the dashboard itself. 
	 * Loads all widgets placed on the dashboard
	 *
	 * @return string
	 */
	private function actionList() {
	    return "The widgets will go here...";
	}
	
	/**
	 * Generates the forms to add a widget to the dashboard
	 *
	 * @return string, "" in case of success
	 */
	private function actionAddWidgetToDashboard() {
	    $strReturn = "";
	    //check permissions
	    if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

	        //step 1: select a widget, plz 
	        if($this->getParam("step") == "") {
	            $objSystemWidget = new class_modul_system_adminwidget();
	            $arrWidgetsAvailable = $objSystemWidget->getListOfWidgetsAvailable();
	            
	            $arrDD = array();
	            foreach ($arrWidgetsAvailable as $strOneWidget) {
	                $objWidget = new $strOneWidget();
	            	$arrDD[$strOneWidget] = $objWidget->getWidgetName();
	            	
	            }
	            
	            $arrColumnsAvailable = array();
	            $arrColumnsAvailable[1] = $this->getText("column1");
	            $arrColumnsAvailable[2] = $this->getText("column2");
	            $arrColumnsAvailable[3] = $this->getText("column3");
	            
	            
	            $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=dashboard&amp;action=addWidgetToDashboard");
	            $strReturn .= $this->objToolkit->formInputDropdown("widget", $arrDD, $this->getText("widget") );
	            $strReturn .= $this->objToolkit->formInputDropdown("column", $arrColumnsAvailable, $this->getText("column") );
	            
	            $strReturn .= $this->objToolkit->formInputHidden("step", "2");
	            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("addWidgetNextStep"));
	            $strReturn .= $this->objToolkit->formClose();
	        }
	        //step 2: loading the widget and allow it to show a view fields
	        else if($this->getParam("step") == "2") {
	            $strWidgetClass = $this->getParam("widget");
	            include_once(_adminpath_."/widgets/".$strWidgetClass.".php");
	            $objWidget = new $strWidgetClass();
	            
	            //ask the widget to generate its form-parts and wrap our elements around
	            $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=dashboard&amp;action=addWidgetToDashboard");
	            $strReturn .= $objWidget->getEditForm();
	            $strReturn .= $this->objToolkit->formInputHidden("step", "3");
	            $strReturn .= $this->objToolkit->formInputHidden("widget", $strWidgetClass);
	            $strReturn .= $this->objToolkit->formInputHidden("column", $this->getParam("column"));
	            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("addWidgetNextStep"));
	            $strReturn .= $this->objToolkit->formClose();
	        }
	        
	    }
	    else
	        $strReturn = $this->getText("fehler_recht");
	    
	    return $strReturn;    
	}
}


?>
