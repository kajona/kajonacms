<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_stats_admin.php 3376 2010-08-01 08:46:47Z sidler $                                  *
********************************************************************************************************/


/**
 * Admin class of the stats-module - xml based.
 * Triggers the report-generation 
 *
 * @package modul_stats
 * @author sidler@mulchpro.de
 */
class class_modul_stats_admin_xml extends class_admin implements interface_xml_admin {

    /**
     * @var class_date
     */
	private $objDateStart;
    /**
     * @var class_date
     */
	private $objDateEnd;
	private $intInterval;
    

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_stats";
		$arrModule["moduleId"] 			= _stats_modul_id_;
		$arrModule["modul"]				= "stats";

		parent::__construct($arrModule);
        
        
        
        $intDateStart = class_carrier::getInstance()->getObjSession()->getSession(class_modul_stats_admin::$STR_SESSION_KEY_DATE_START);
		//Start: first day of current month
        $this->objDateStart = new class_date();
        $this->objDateStart->setTimeInOldStyle($intDateStart);
        
		//End: Current Day of month
        $intDateEnd = class_carrier::getInstance()->getObjSession()->getSession(class_modul_stats_admin::$STR_SESSION_KEY_DATE_END);
        $this->objDateEnd = new class_date();
        $this->objDateEnd->setTimeInOldStyle($intDateEnd);

        
        $this->intInterval = class_carrier::getInstance()->getObjSession()->getSession(class_modul_stats_admin::$STR_SESSION_KEY_INTERVAL);
	}

    
    /**
     * Triggers the "real" creation of the report and wraps the code inline into a xml-structure
     * 
     * @return string 
     */
    protected function actionGetReport() {
        $strPlugin = $this->getParam("plugin");
        
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objFilesystem = new class_filesystem();
            $arrPlugins = $objFilesystem->getFilelist(_adminpath_."/statsreports", ".php");

            foreach($arrPlugins as $strOnePlugin) {
                $strClassName = str_replace(".php", "", $strOnePlugin);
                $objPlugin = new $strClassName($this->objDB, $this->objToolkit, $this->getObjText());

                if($objPlugin->getReportCommand() == $strPlugin && $objPlugin instanceof interface_admin_statsreports) {
                    //get date-params as ints
                    $intStartDate = mktime(0, 0, 0, $this->objDateStart->getIntMonth() , $this->objDateStart->getIntDay(), $this->objDateStart->getIntYear());
                    $intEndDate = mktime(0, 0, 0, $this->objDateEnd->getIntMonth() , $this->objDateEnd->getIntDay(), $this->objDateEnd->getIntYear());
                    $objPlugin->setEndDate($intEndDate);
                    $objPlugin->setStartDate($intStartDate);
                    $objPlugin->setInterval($this->intInterval);

                    $arrImage = $objPlugin->getReportGraph();

                    if(!is_array($arrImage))
                        $arrImage = array($arrImage);
                    foreach($arrImage as $strImage) {
                        if($strImage != "") {
                    	   $strReturn .= $this->objToolkit->getGraphContainer($strImage."?reload=".time());
                        }
                    }


                    $strReturn .=  $objPlugin->getReport();
                    $strReturn =  "<content><![CDATA[" .$strReturn. "]]></content>";
                }
            }
        }
		else
			$strReturn .= "<error>".xmlSafeString($this->getText("fehler_recht"))."</error>";

        return $strReturn;
    }
	

}

?>