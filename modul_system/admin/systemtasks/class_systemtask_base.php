<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                       *
********************************************************************************************************/


include_once(_systempath_."/class_carrier.php");

/**
 * Base class for all systemtasks. Provides a few methods to be used by the concrete tasks.
 *
 * @package modul_system
 */
abstract class class_systemtask_base {
    
    private $strTextbase = "system";

	/**
	 * Instance of class_db
	 *
	 * @var class_db
	 */
    private $objDB;
    
    /**
     * Instance of class_text
     *
     * @var class_texte
     */
    private $objTexte;
    
    /**
     * Instance of class_toolkit
     *
     * @var class_toolkit_admin
     */
    protected $objToolkit;

    public function __construct() {
        $arrModule["author"]        = "sidler@mulchprod.de";
        $arrModule["moduleId"]      = _system_modul_id_;
        
        //load the external objects
        $this->objDB = class_carrier::getInstance()->getObjDB();
        $this->objTexte = class_carrier::getInstance()->getObjText();
        $this->objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
    }
    
    
    /**
     * Provides acces to the GET and POST paramy
     *
     * @param string $strName
     * @return string, "" if not found
     */
    protected function getParam($strName) {
    	$arrParams = array_merge(getArrayGet(), getArrayPost());
    	if(isset($arrParams[$strName]))
    	   return $arrParams[$strName];
    	else
    	   return "";   
    }
    
    /**
     * Delegate requests for strings to the text-subsystem
     *
     * @param string $strTextKey
     * @return string
     */
    protected function getText($strTextKey) {
        return $this->objTexte->getText($strTextKey, $this->strTextbase, "admin");	
    }
    
    /**
     * Method invoking the hook-methods to generate a form.
     *
     */
    public final function generateAdminForm() {
    	$strReturn = "";
    	$strFormContent = $this->getAdminForm();
    	
    	if($strFormContent != "") {
    		$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("system", "systemTasks", "task=".$this->getStrInternalTaskName()));
    		$strReturn .= $strFormContent;
            $strReturn .= $this->objToolkit->formInputHidden("work", "true");
    		$strReturn .= $this->objToolkit->formInputSubmit($this->objTexte->getText("systemtask_run", "system", "admin"));
    		$strReturn .= $this->objToolkit->formClose();
    		
    		$strReturn .= $this->objToolkit->divider();
    	}
    	
    	return $strReturn;
    }
    
    /**
     * Sets the current textbase, so a module.
     * If your textfiles are coming along with a module different than module system, pass the name here
     * to enable a proper text-loading.
     * 
     * @param string $strModulename
     */
    protected function setStrTextBase($strModulename) {
        $this->strTextbase = $strModulename;        
    }

    /**
     * Empty implementation, override in subclass!
     *
     */
    public function getAdminForm() {}
    
    /**
     * Empty implementation, oveerride in subclass!
     *
     */
    public function getStrInternalTaskName() {}
    

}
?>