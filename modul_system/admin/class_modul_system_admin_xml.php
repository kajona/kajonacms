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
        $arrModule = array();
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
        if($strAction == "executeSystemTask")
            $strReturn .= $this->actionExecuteSystemTask();

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
		    $this->flushCompletePagesCache();
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
    	    if(parent::setStatus()) {
    	        $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setStatusOk")."</message>";
    	        $this->flushCompletePagesCache();

                //try to flush the navigations-cache
                $objNavi = class_modul_system_module::getModuleByName("navigation");
                if($objNavi != null) {
                    include_once(_systempath_."/class_modul_navigation_cache.php");
                    class_modul_navigation_cache::flushCache();
                }
    	    }
    	    else
                $strReturn .= "<error>".$this->getSystemid()." - ".$this->getText("setStatusError")."</error>";
	    }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";

	    return $strReturn;
	}

    /**
     * Executes a systemtask.
     * Returns the progress-info or the error-/success message and the reload-infos using a
     * custom xml-structure:
     * <statusinfo></statusinfo><reloadurl></reloadurl>
     *
     * @return string
     */
    private function actionExecuteSystemTask() {
        $strReturn = "";
        $strTaskOutput = "";
        if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {

            if($this->getParam("task") != "") {
                //include the list of possible tasks
                include_once(_systempath_."/class_filesystem.php");
                $objFilesystem = new class_filesystem();
                $arrFiles = $objFilesystem->getFilelist(_adminpath_."/systemtasks/", array(".php"));
                asort($arrFiles);
                //search for the matching task
                foreach ($arrFiles as $strOneFile) {
                    if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

                        //instantiate the current task
                        include_once(_adminpath_."/systemtasks/".$strOneFile);
                        $strClassname = uniStrReplace(".php", "", $strOneFile);
                        $objTask = new $strClassname();
                        if($objTask instanceof interface_admin_systemtask && $objTask->getStrInternalTaskname() == $this->getParam("task")) {

                            class_logger::getInstance()->addLogRow("executing task ".$objTask->getStrInternalTaskname(), class_logger::$levelInfo);

                            //let the work begin...
                            $strTempOutput = trim($objTask->executeTask());

                            //progress information?
                            if($objTask->getStrProgressInformation() != "")
                                $strTaskOutput .= $objTask->getStrProgressInformation();

                            if(is_numeric($strTempOutput) && ($strTempOutput >= 0 && $strTempOutput <= 100) ) {
                                $strTaskOutput .= "<br />".$this->getText("systemtask_progress")."<br />".$this->objToolkit->percentBeam($strTempOutput, 400);
                            }
                            else {
                                $strTaskOutput .= $strTempOutput;
                            }

                            //create response-content
                            $strReturn .= "<statusinfo>".$strTaskOutput."</statusinfo>\n";

                            //reload requested by worker?
                            if($objTask->getStrReloadUrl() != "")
                                $strReturn .= "<reloadurl>".("&task=".$this->getParam("task").$objTask->getStrReloadParam())."</reloadurl>";

                            break;
                        }
                    }
                }
            }

        }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";

	    return $strReturn;
    }


}
?>