<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the imagelightbox-element
 *
 * @package modul_pages
 *
 */
class class_element_imagelightbox extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_imagelightbox";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"] 		= _dbprefix_."element_universal";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]   = "";

		parent::__construct($arrModule);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		//Build the form
		$strReturn .= $this->objToolkit->formInputText("char2", $this->getText("imagelightbox_title"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));
		$strReturn .= $this->objToolkit->formInputTextArea("text", $this->getText("imagelightbox_subtitle"), (isset($arrElementData["text"]) ? $arrElementData["text"] : ""));
		$strReturn .= $this->objToolkit->formInputText("char1", $this->getText("imagelightbox_image"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""),
		                                               "inputText", getLinkAdminPopup("folderview", "list", "&form_element=char1&systemid="._filemanager_default_imagesrepoid_, $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));

        //load templates
		$objFilesystem = new class_filesystem();
		$arrTemplates = $objFilesystem->getFilelist("/templates/element_imagelightbox", ".tpl");
		$arrTemplatesDD = array();
		if(count($arrTemplates) > 0) {
			foreach($arrTemplates as $strTemplate) {
				$arrTemplatesDD[$strTemplate] = $strTemplate;
			}
		}
		if(count($arrTemplates) == 1)
            $this->addHiddenFormElement($this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getText("imagelightbox_template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" )));
        else
            $strReturn .= $this->objToolkit->formInputDropdown("char3", $arrTemplatesDD, $this->getText("imagelightbox_template"), (isset($arrElementData["char3"]) ? $arrElementData["char3"] : "" ));

		

		$strReturn .= $this->objToolkit->setBrowserFocus("char2");

		return $strReturn;
	}

    /**
     * saves the submitted data to the database
     * It IS wanted to not let the system save the element here!
     *
     * @param string $strSystemid
     * @return bool
     */
    public function actionSave($strSystemid) {
        $strImage = $this->getParam("char1");
        //We have to replace the webpath to remain flexible
        $strImage = str_replace(_webpath_, "", $strImage);
        //And to the database
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                char1 = '".dbsafeString($strImage)."',
                char2 = '".dbsafeString($this->getParam("char2"))."',
                char3 = '".dbsafeString($this->getParam("char3"))."',
                text = '".dbsafeString($this->getParam("text"))."'
                WHERE content_id='".dbsafeString($strSystemid)."'";

        if($this->objDB->_query($strQuery))
            return true;
        else
            return false;
    }


}
?>