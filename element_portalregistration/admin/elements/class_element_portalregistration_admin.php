<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                        *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portalregistration-element
 *
 * @package element_portalregistration
 * @author sidler@mulchprod.de
 */
class class_element_portalregistration_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("name", "element_portalregistration");
        $this->setArrModuleEntry("table", _dbprefix_ . "element_preg");
        $this->setArrModuleEntry("tableColumns", "portalregistration_template,portalregistration_group,portalregistration_success");
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

        $strReturn .= $this->objToolkit->formTextRow($this->getLang("portalregistration_hint"));

        //Build the form
        //Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_portalregistration", ".tpl");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        if(count($arrTemplates) == 1) {
            $this->addOptionalFormElement(
                $this->objToolkit->formInputDropdown("portalregistration_template", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["portalregistration_template"]) ? $arrElementData["portalregistration_template"] : ""))
            );
        }
        else {
            $strReturn .= $this->objToolkit->formInputDropdown(
                "portalregistration_template",
                $arrTemplatesDD,
                $this->getLang("template"),
                (isset($arrElementData["portalregistration_template"]) ? $arrElementData["portalregistration_template"] : "")
            );
        }

        //Load groups available
        $arrGroups = class_module_user_group::getObjectList();
        $arrGroupsDD = array();
        foreach($arrGroups as $objOneGroup) {
            if($objOneGroup->getStrSubsystem() == "kajona") {
                $arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
            }
        }

        $strReturn .= $this->objToolkit->formInputDropdown(
            "portalregistration_group",
            $arrGroupsDD,
            $this->getLang("portalregistration_group"),
            (isset($arrElementData["portalregistration_group"]) ? $arrElementData["portalregistration_group"] : "")
        );
        $strReturn .= $this->objToolkit->formInputPageSelector(
            "portalregistration_success",
            $this->getLang("commons_page_success"),
            (isset($arrElementData["portalregistration_success"]) ? $arrElementData["portalregistration_success"] : "")
        );

        $strReturn .= $this->objToolkit->setBrowserFocus("portalregistration_template");

        return $strReturn;
    }

}
