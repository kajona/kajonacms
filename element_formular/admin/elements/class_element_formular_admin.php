<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the formular-element
 *
 * @package element_formular
 * @author sidler@mulchprod.de
 */
class class_element_formular_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("name", "element_formular");
        $this->setArrModuleEntry("table", _dbprefix_ . "element_formular");
        $this->setArrModuleEntry("tableColumns", "formular_class,formular_email,formular_success,formular_error,formular_template");
        parent::__construct();
    }

    /**
     * Returns a form to edit the element-data
     *
     * @param mixed $arrElementData
     *
     * @return string
     */
    public function getEditForm($arrElementData) {
        $strReturn = "";

        //Build the form
        $strReturn .= $this->objToolkit->formInputText("formular_email", $this->getLang("formular_email"), (isset($arrElementData["formular_email"]) ? $arrElementData["formular_email"] : ""));
        $strReturn .= $this->objToolkit->formInputText("formular_success", $this->getLang("formular_success"), (isset($arrElementData["formular_success"]) ? $arrElementData["formular_success"] : ""));
        $strReturn .= $this->objToolkit->formInputText("formular_error", $this->getLang("formular_error"), (isset($arrElementData["formular_error"]) ? $arrElementData["formular_error"] : ""));
        //Load the available classes
        $arrClasses = class_resourceloader::getInstance()->getFolderContent("/portal/forms", array(".php"));
        $arrClassesDD = array();
        if(count($arrClasses) > 0) {
            foreach($arrClasses as $strClass) {
                $arrClassesDD[$strClass] = $strClass;
            }
        }
        $strReturn .= $this->objToolkit->formInputDropdown("formular_class", $arrClassesDD, $this->getLang("formular_class"), (isset($arrElementData["formular_class"]) ? $arrElementData["formular_class"] : ""));


        //Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_form", ".tpl");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        if(count($arrTemplatesDD) == 1) {
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("formular_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["formular_template"]) ? $arrElementData["formular_template"] : "")));
        }
        else {
            $strReturn .= $this->objToolkit->formInputDropdown("formular_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["formular_template"]) ? $arrElementData["formular_template"] : ""));
        }


        $strReturn .= $this->objToolkit->setBrowserFocus("formular_email");

        return $strReturn;
    }

    public function getRequiredFields() {
        return array("formular_email" => "email", "formular_template" => "text");
    }

}
