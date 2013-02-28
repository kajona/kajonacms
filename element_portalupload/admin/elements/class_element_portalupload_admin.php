<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portalupload-element
 *
 * @package element_portalupload
 * @author sidler@mulchprod.de
 */
class class_element_portalupload_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     */
    public function __construct() {

        $this->setArrModuleEntry("name", "element_portalupload");
        $this->setArrModuleEntry("table", _dbprefix_ . "element_universal");
        $this->setArrModuleEntry("tableColumns", "char1,char2");

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

        $arrDlArchives = class_module_mediamanager_repo::getObjectList();

        //Build the form
        //Load the available templates
        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_portalupload", ".tpl");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }


        $arrDlDD = array();
        if(count($arrDlArchives) > 0) {
            foreach($arrDlArchives as $objOneArchive) {
                $arrDlDD[$objOneArchive->getSystemid()] = $objOneArchive->getStrDisplayName();
            }
        }


        if(count($arrTemplates) == 1)
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "")));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""));

        $strReturn .= $this->objToolkit->formInputDropdown("char2", $arrDlDD, $this->getLang("portalupload_download"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));
        $strReturn .= $this->objToolkit->setBrowserFocus("char2");

        return $strReturn;
    }


    public function getRequiredFields() {
        return array("char1" => "text", "char2" => "text");
    }
}
