<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_systemtask_navigationcheck.php                                                                *
*   Checks the navigation-points fpr valid internal links                                               *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                        *
********************************************************************************************************/

//base class and interface
include_once(_adminpath_."/systemtasks/class_systemtask_base.php");
include_once(_adminpath_."/systemtasks/interface_admin_systemtask.php");

include_once(_systempath_."/class_modul_navigation_point.php");
include_once(_systempath_."/class_modul_navigation_tree.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * Checkes the existing navigation-points for valid internal links.
 *
 * @package modul_navigation
 */
class class_systemtask_navigationcheck extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
        //set the correct text-base
        $this->setStrTextBase("navigation");
    }
    
    
    /**
     * @see interface_admin_systemtast::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "navigationcheck";
    }
    
    /**
     * @see interface_admin_systemtast::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_navigationcheck_name");
    }
    
    /**
     * @see interface_admin_systemtast::executeTask()
     * @return string
     */
    public function executeTask() {
        $strReturn = "";
        
        //load all navigation points, tree by tree
        $arrTrees = class_modul_navigation_tree::getAllNavis();
        foreach($arrTrees as $objOneTree) {
            $strReturn .= $this->getText("systemtask_navigationcheck_treescan")." ".$objOneTree->getStrName()."...\n";  
            $strReturn .= $this->processLevel($objOneTree->getSystemid(), 0);    
        }
        
        
        return $this->objToolkit->getPreformatted(array($strReturn));
    }
    
    private function processLevel($intParentId, $intLevel) {
        $strReturn = "";
        $arrNaviPoints = class_modul_navigation_point::getNaviLayer($intParentId);
        foreach($arrNaviPoints as $objOnePoint) {
            for($intI = 0; $intI<=$intLevel; $intI++)
                $strReturn .= "  ";
                 
            $strReturn .= $this->processSinglePoint($objOnePoint);
            $strReturn .= $this->processLevel($objOnePoint->getSystemid(), $intLevel+1);
        }
        
        return $strReturn;
    }

    
    private function processSinglePoint($objPoint) {
        $strReturn = "";
        
        $strReturn .= $objPoint->getStrName()." ";
        
        if($objPoint->getStrPageI() == "" && $objPoint->getStrPageE() == "") {
            $strReturn .= $this->getText("systemtask_navigationcheck_invalidEmpty"); 
        }
        else if($objPoint->getStrPageI() != "" && $objPoint->getStrPageE() != "") {
            $strReturn .= $this->getText("systemtask_navigationcheck_invalidBoth");
        }
        else {
            $strReturn .= $this->getText("systemtask_navigationcheck_valid")." (".$objPoint->getStrPageI(). $objPoint->getStrPageE().")";
            
        }
        
        
        return $strReturn."\n";
    
    }
    
    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
        return "";
    }
    
}
?>