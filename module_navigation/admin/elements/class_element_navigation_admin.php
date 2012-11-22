<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

/**
 * Admin class of the navigation element
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_element_navigation_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor

     */
    public function __construct() {
        $this->setArrModuleEntry("name", "element_navigation");
        $this->setArrModuleEntry("table", _dbprefix_."element_navigation");
        $this->setArrModuleEntry("tableColumns", "navigation_id,navigation_template,navigation_foreign");
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

        $arrNavigationsDropdown = array();
        foreach(class_module_navigation_tree::getObjectList() as $objOneNavigation)
            $arrNavigationsDropdown[$objOneNavigation->getSystemid()] = $objOneNavigation->getStrDisplayName();

        //Build the form
        $strReturn .= $this->objToolkit->formInputDropdown("navigation_id", $arrNavigationsDropdown, $this->getLang("commons_name"), (isset($arrElementData["navigation_id"]) ? $arrElementData["navigation_id"] : ""));
        //Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/module_navigation");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement(
                $this->objToolkit->formInputDropdown("navigation_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["navigation_template"]) ? $arrElementData["navigation_template"] : ""))
            );
        else
            $strReturn .= $this->objToolkit->formInputDropdown("navigation_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["navigation_template"]) ? $arrElementData["navigation_template"] : ""));


        $arrYesNo = array(
            1 => $this->getLang("commons_yes"),
            0 => $this->getLang("commons_no")
        );

        $strReturn .= $this->objToolkit->formTextRow($this->getLang("navigation_foreign_hint"));
        $strReturn .= $this->objToolkit->formInputDropdown("navigation_foreign", $arrYesNo, $this->getLang("navigation_foreign"), (isset($arrElementData["navigation_foreign"]) ? $arrElementData["navigation_foreign"] : ""));

        $strReturn .= $this->objToolkit->setBrowserFocus("navigation_template");

        return $strReturn;
    }

}
