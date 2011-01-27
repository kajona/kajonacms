<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_stats_admin.php 3376 2010-08-01 08:46:47Z sidler $                                  *
********************************************************************************************************/

/**
 * Admin-Part of the tags.
 * No classical functionality, rather a list of helper-methods, e.g. in order to
 * create the form to tag content.
 *
 * @package modul_tags
 */
class class_modul_tags_admin extends class_admin implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_tags";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _tags_modul_id_;
		$arrModule["table"] 		    = _dbprefix_."tags_tag";
		$arrModule["modul"]				= "tags";

		//base class
		parent::__construct($arrModule);
	}

	/**
	 * Action-block invoking all later actions
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		$strReturn = "";
        if($strAction == "")
            $strAction = "list";

        if($strAction == "list")
            $strReturn .= $this->actionList();
        else if($strAction == "deleteTag") {
    			$strReturn = $this->actionDeleteTag();
    			if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
        }
        else if($strAction == "editTag")
            $strReturn = $this->actionEditTag();
        else if($strAction == "saveTag") {
            if($this->validateForm()) {
                $strReturn = $this->actionSaveTag();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            }
            else
                $strReturn = $this->actionEditTag();
        }

		$this->strOutput = $strReturn;
	}

    public function  getRequiredFields() {
        if($this->getAction() == "saveTag")
            return array("tag_name" => "string");
        else
            parent::getRequiredFields();
    }

	public function getOutputContent() {
		return $this->strOutput;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("module_list"), "", "", true, "adminnavi"));
		
        return $arrReturn;
	}

    private function actionList() {
        $strReturn = "";
		//Check the rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			$intI = 0;

			//showing a list using the pageview
            $objArraySectionIterator = new class_array_section_iterator(class_modul_tags_tag::getNumberOfTags());
		    $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
		    $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
		    $objArraySectionIterator->setArraySection(class_modul_tags_tag::getAllTags($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

    		$arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->arrModule["modul"], "list");
            $arrTags = $arrPageViews["elements"];

			foreach($arrTags as $objTag) {
				$strActions = "";

	    		if($this->objRights->rightEdit($objTag->getSystemid()))
	    			$strActions.= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editTag", "&systemid=".$objTag->getSystemid(), "", $this->getText("tag_edit"), "icon_pencil.gif"));
	    		if($this->objRights->rightDelete($objTag->getSystemid()))
	    			$strActions.= $this->objToolkit->listDeleteButton($objTag->getStrName(), $this->getText("tag_delete_question"), getLinkAdminHref($this->arrModule["modul"], "deleteTag", "&systemid=".$objTag->getSystemid()));
	    		if($this->objRights->rightEdit($objTag->getSystemid()))
	    			$strActions.= $this->objToolkit->listStatusButton($objTag->getSystemid());
	    		if($this->objRights->rightRight($objTag->getSystemid()	))
	    			$strActions.= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objTag->getSystemid(), "", $this->getText("tag_rechte"), getRightsImageAdminName($objTag->getSystemid())));

	  			$strReturn .= $this->objToolkit->listRow3($objTag->getStrName(), count($objTag->getListOfAssignments())." ".$this->getText("tag_assignments"), $strActions, getImageAdmin("icon_dot.gif"), $intI++);
			}

			if(uniStrlen($strReturn) != 0)
			    $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

			if(count($arrTags) > 0)
			    $strReturn .= $arrPageViews["pageview"];

			if(count($arrTags) == 0)
				$strReturn .= $this->getText("list_tags_empty");

		}
		else
			$strReturn .= $this->getText("error_permissions");

		return $strReturn;
    }


    /**
	 * Deletes a tag
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteTag() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
            $objTag = new class_modul_tags_tag($this->getSystemid());
            if(!$objTag->deleteTag())
                throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getText("error_permissions");


		return $strReturn;
	}

    /**
     * Generates the form to edit an existing tag
     * @return string
     */
    private function actionEditTag() {
        $strReturn = "";
		if($this->objRights->rightEdit($this->getSystemid()) ) {
            $objTag = new class_modul_tags_tag($this->getSystemid());

			$strReturn .= $this->objToolkit->getValidationErrors($this, "saveTag");
			$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveTag"));
			$strReturn .= $this->objToolkit->formInputText("tag_name", $this->getText("tag_name"), ($this->getParam("tag_name") != "" ? $this->getParam("tag_name") : $objTag->getStrName()) );
			$strReturn .= $this->objToolkit->formInputHidden("systemid", $objTag->getSystemid());
			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
			$strReturn .= $this->objToolkit->formClose();

			$strReturn .= $this->objToolkit->setBrowserFocus("tag_name");
		}
		else
			$strReturn = $this->getText("error_permissions");

		return $strReturn;
    }

    
    /**
     * Saves the passed tag-data back to the database.
     * @return string "" in case of success
     */
    private function actionSaveTag() {
        $strReturn = "";
		if($this->objRights->rightEdit($this->getSystemid())) {
			//Collect data to save to db
			$objTag = new class_modul_tags_tag($this->getSystemid());
			$objTag->setStrName($this->getParam("tag_name"), true);
            $objTag->updateObjectToDb();
		}
		else
			$strReturn = $this->getText("error_permissions");

		return $strReturn;
    }


    /**
     * Generates a form to add tags to the passed systemid.
     * Since all functionality is performed using ajax, there's no page-reload when adding or removing tags.
     * Therefore the form-handling of existing forms can remain as is
     *
     * @param string $strTargetSystemid the systemid to tag
     * @param string $strAttribute additional info used to differ betweeen tag-sets for a single systemid
     * @return string
     */
    public function getTagForm($strTargetSystemid, $strAttribute = null) {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

            $strTagContent = "";

            $strTagsWrapperId = generateSystemid();

            $strTagContent .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveTags"), "", "", "KAJONA.admin.tags.saveTag(document.getElementById('tagname').value+'', '".$strTargetSystemid."', '".$strAttribute."');return false;");

            $strTagContent .= $this->objToolkit->formTextRow($this->getText("tag_name_hint"));
            $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getText("tag_name"));
            $strTagContent .= $this->objToolkit->formInputSubmit($this->getText("button_add"), $this->getText("button_add"), "");
            $strTagContent .= $this->objToolkit->formClose();

            

            $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);


            $strReturn .= $this->objToolkit->getFieldset($this->getText("tagsection_header"), $strTagContent);
        }
        else
            $strReturn .= $this->getText("error_permissions");

        return $strReturn;
    }


}
?>