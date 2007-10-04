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
*	$Id: class_adminwidget_systeminfo.php 1565 2007-06-14 09:54:52Z sidler $	                        *
********************************************************************************************************/

include_once(_adminpath_."/widgets/class_adminwidget.php");
include_once(_adminpath_."/widgets/interface_adminwidget.php");

class class_adminwidget_systeminfo extends class_adminwidget implements interface_adminwidget {
    
    
    /**
     * Allows the widget to add additional fields to the edit-/create form. 
     * Use the toolkit class as usual.
     *
     * @return string
     */
    public function getEditForm() {
        return $this->objToolkit->formTextRow("i was called. hooray. and only, because i represent a hook-method. what a pleasure.");
    }
    
    /**
     * This method is called, when the widget should generate it's content.
     * Return the completet content using the methods provided by the base class.
     * Do NOT use the toolkit right here! 
     *
     * @return string
     */
    public function getWidgetOutput() {
        
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
 
