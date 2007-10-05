<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_dashboard_widget.php																	*
* 	Class to manage the a widget placed on the dashboard												*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_dashboard_widget.php 1735 2007-10-05 19:45:00Z sidler $	                        *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * Class to represent a single adminwidget
 * 
 * @package modul_dashboard
 */
class class_modul_dashboard_widget extends class_model implements interface_model {
    
    private $strColumn = "";
    private $strUser = "";
    private $strWidgetId = "";
    
    
	/**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_dashboard";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]              = _dbprefix_."dashboard";
		$arrModul["modul"]              = "dashboard";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }
    
    public function initObject() {
        
    }
    
    public function updateObjectToDb() {
        
    }
    
    /**
     * Saves a widget as a new one to the database
     *
     */
    public function saveObjectToDb() {
        
        $this->objDB->transactionBegin();

        $strDashboardId = $this->createSystemRecord($this->getModuleSystemid($this->arrModule["modul"]), "dashboard of user: ".$this->strUser);
        $this->setSystemid($strDashboardId);
        
        class_logger::getInstance()->addLogRow("new dashboardentry for user ".$this->getStrUser(), class_logger::$levelInfo);
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (dashboard_id, dashboard_user, dashboard_column, dashboard_widgetid) VALUES
                    ('".dbsafeString($strDashboardId)."', '".dbsafeString($this->getStrUser())."', 
                    '".dbsafeString($this->getStrColumn())."', '".dbsafeString($this->getStrWidgetId())."')";

        if($this->objDB->_query($strQuery)) {
            $this->objDB->transactionCommit();
            return true;
        }
        else {
            $this->objDB->transactionRollback();
            return false;
        }
    }
    
    
    
//--- GETTERS / SETTERS ---------------------------------------------------------------------------------

    public function setStrColumn($strColumn) {
        $this->strColumn = $strColumn;
    }
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }
    public function setStrWidgetId($strWidgetId) {
        $this->strWidgetId = $strWidgetId;
    }
    
    public function getStrColumn() {
        return $this->strColumn;
    }
    public function getStrUser() {
        return $this->strUser;
    }
    public function getStrWidgetId() {
        return $this->strWidgetId;
    }
    
}


?>
