<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_adminwidget_systeminfo.php																	*
* 	widget showing a few infos about the current system													*																				*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");

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
        return "sysinfos";
    }
    
    
    /**
     * Return a short (!) name of the widget.
     *
     * @return 
     */
    public function getWidgetName() {
        return $this->getText("sysinfo_name");
    }
    
}


?>
 
