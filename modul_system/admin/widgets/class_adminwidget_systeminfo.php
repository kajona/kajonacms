<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");


/**
 * @package modul_dashboard
 *
 */
class class_adminwidget_systeminfo extends class_adminwidget implements interface_adminwidget {
    
    /**
     * Basic constructor, registers the fields to be persisted and loaded
     *
     */
    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("php", "server", "kajona"));
    }
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputCheckbox("php", $this->getText("sysinfo_checkboxphp"), $this->getFieldValue("php"));
        $strReturn .= $this->objToolkit->formInputCheckbox("server", $this->getText("sysinfo_checkboxserver"), $this->getFieldValue("server"));
        $strReturn .= $this->objToolkit->formInputCheckbox("kajona", $this->getText("sysinfo_checkboxkajona"), $this->getFieldValue("kajona"));
        return $strReturn;
    }
    
    /**
     * This method is called, when the widget should generate it's content.
     * Return the complete content using the methods provided by the base class.
     * Do NOT use the toolkit right here! 
     *
     * @return string
     */
    public function getWidgetOutput() {
        $strReturn = "";
        //check wich infos to produce
        if($this->getFieldValue("php") == "checked") {
            $strReturn .= $this->widgetText($this->getText("sysinfo_php_version").PHP_VERSION);
            $strReturn .= $this->widgetText($this->getText("sysinfo_php_memlimit").bytesToString(ini_get("memory_limit"), true));
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("server") == "checked") {
            $strReturn .= $this->widgetText($this->getText("sysinfo_server_system").php_uname("s")." ".php_uname("r"));
            $strReturn .= $this->widgetText($this->getText("sysinfo_server_diskspace").bytesToString(disk_total_space(_realpath_)));
            $strReturn .= $this->widgetText($this->getText("sysinfo_server_diskspacef").bytesToString(disk_free_space(_realpath_)));
            $strReturn .= $this->widgetSeparator();
        }
        if($this->getFieldValue("kajona") == "checked") {
            $strReturn .= $this->widgetText($this->getText("sysinfo_kajona_version").class_modul_system_module::getModuleByName("system")->getStrVersion());
            $strReturn .= $this->widgetText($this->getText("sysinfo_kajona_versionAvail").$this->getLatestKernelVersion());
            $strReturn .= $this->widgetText($this->getText("sysinfo_kajona_nrOfModules").count(class_modul_system_module::getAllModules()));
        }
        return $strReturn;
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return string
     */
    public function getWidgetName() {
        return $this->getText("sysinfo_name");
    }
    
    /**
     * Queries the kajona-updatecheck-server to fetch the latest version
     *
     * @return string
     */
    private function getLatestKernelVersion() {
    	$strReturn = "";
    	
        //Fetch the xml-file of available updates
        //To do this, use sockets, since php 5.2 url_fopen is disabled in most cases
        $strChecksum = md5(urldecode(_webpath_)."getVersions");
        $strQueryString = "/updates.php?action=getVersions&domain=".urlencode(_webpath_)."&checksum=".urlencode($strChecksum);
        $strXmlVersionList = false;

        try {
            include_once(_systempath_."/class_remoteloader.php");
            $objRemoteloader = new class_remoteloader();
            $objRemoteloader->setStrHost("updatecheck.kajona.de");
            $objRemoteloader->setStrQueryParams($strQueryString);
            $strXmlVersionList = $objRemoteloader->getRemoteContent();
        }
        catch (class_exception $objExeption) {
            $strXmlVersionList = false;
        }
        
        if(!$strXmlVersionList) {
            return "n.a.";
        }
            
        try {
            include_once(_systempath_."/class_xml_parser.php");
            $objXmlParser = new class_xml_parser();
            if($objXmlParser->loadString($strXmlVersionList)) {
                $arrRemoteModules = $objXmlParser->getNodesAttributesAsArray("module");
                //Do a little clean up
                $arrCleanModules = array();
                foreach ($arrRemoteModules as $arrOneRemoteModule) {
                    $arrCleanModules[$arrOneRemoteModule[0]["value"]] = $arrOneRemoteModule[1]["value"];
                }
                
                if(key_exists("system", $arrCleanModules)) {
                	$strReturn = $arrCleanModules["system"];
                }
            }
            else
                $strReturn .= "n.a.";

        }
        catch (class_exception $objException) {
            $strReturn .= "n.a.";
        }    
    	
    	return $strReturn;
    }
    
}


?>