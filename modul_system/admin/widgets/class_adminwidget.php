<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget.php																				*
* 	base-class to be extended by all adminwidgets														*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

/**
 * Base class to be extended by all adminwidgets.
 * Holds a few methods to create a framework-like behaviour 
 *
 * @package modul_system
 */
abstract class class_adminwidget {

    private $arrModule = array();
    
    /**
     * instance of class_db
     *
     * @var class_db
     */
    private $objDb;
    
    /**
     * instance of class_toolkit
     *
     * @var class_toolkit_admin
     */
    protected $objToolkit;
    
    /**
     * instance of class_texte
     *
     * @var class_texte
     */
    private $objTexte;
    
    public function __construct($arrModule = array()) {
        $this->arrModule["p_name"] 				= "modul_system";
    	$this->arrModule["p_author"] 			= "sidler@mulchprod.de";
    	$this->arrModule["p_moduleId"] 			= _system_modul_id_;
    	$this->arrModule["p_modul"]				= "system";
    	
    	$this->arrModule = array_merge($this->arrModule, $arrModule);
    	
    	$this->objDb = class_carrier::getInstance()->getObjDB();
    	$this->objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
    	$this->objTexte = class_carrier::getInstance()->getObjText();
    	
    }

    /**
     * This method invokes the rendering of the widget. Calls
     * the implementing class.
     *
     * @return string
     */
    public final function generateWidgetOutput() {
        $strWidgetContent = $this->getWidgetOutput();
    }
    
    /**
     * Overwrite this method!
     *
     * @return string
     * @see interface_adminwidget::getWidgetOutput()
     */
    public function getWidgetOutput() {
        return "";
    }
    
    /**
     * Loads a text-fragement from the textfiles
     *
     * @param string $strKey
     * @return string
     */
    public final function getText($strKey) {
        return $this->objTexte->getText($strKey, "adminwidget", "admin");
    }
}


?>
 
