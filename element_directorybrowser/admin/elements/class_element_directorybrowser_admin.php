<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Class to handle the admin-stuff of the directorybrowser-element
 *
 * @package element_directorybrowser
 * @author sidler@mulchprod.de
 */
class class_element_directorybrowser_admin extends class_element_admin implements interface_admin_element {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("name", "element_directorybrowser");
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

        $arrTemplates = class_resourceloader::getInstance()->getTemplatesInFolder("/element_directorybrowser");
        $arrTemplatesDD = array();
        if(count($arrTemplates) > 0) {
            foreach($arrTemplates as $strTemplate) {
                $arrTemplatesDD[$strTemplate] = $strTemplate;
            }
        }

        if(count($arrTemplates) == 1) {
            $this->addOptionalFormElement($this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "")));
        }
        else {
            $strReturn .= $this->objToolkit->formInputDropdown("char1", $arrTemplatesDD, $this->getLang("template"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""));
        }

        $strReturn .= $this->objToolkit->formInputText("char2", $this->getLang("directory"), isset($arrElementData["char2"]) ? $arrElementData["char2"] : "", "inputText", getLinkAdminDialog("mediamanager", "folderListFolderview", "&form_element=char2", $this->getLang("commons_open_browser"), $this->getLang("commons_open_browser"), "icon_externalBrowser.png", $this->getLang("commons_open_browser")));

        $strReturn .= $this->objToolkit->setBrowserFocus("char2");

        return $strReturn;
    }

    /**
     * Required is: the path
     *
     * @return array
     */
    public function getRequiredFields() {
        return array("char2" => "string");
    }

}
