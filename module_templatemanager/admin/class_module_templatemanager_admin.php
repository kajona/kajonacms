<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Admin-GUI of the templatemanager.
 * The templatemanger provides a way to handle the template-packs available.
 * In addition, setting packs as the current active-one is supported, too.
 *
 * @package module_templatemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_templatemanager_admin extends class_admin_simple implements interface_admin {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "templatemanager");
        $this->setArrModuleEntry("moduleId", _templatemanager_module_id_);
		parent::__construct();

	}

	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));

        return $arrReturn;
	}

    protected function actionNew() {
    }

    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        return "tbd";
        $objArraySectionIterator = new class_array_section_iterator(class_module_tags_tag::getNumberOfTags());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_tags_tag::getAllTags($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    protected function getNewEntryAction($strListIdentifier) {
        return "";
    }


    /**
     * Generates the form to edit an existing tag
     * @param \class_admin_formgenerator|null $objForm
     * @return string
     * @permissions edit
     */
    protected function actionEdit(class_admin_formgenerator $objForm = null) {

        return "tbd";
        $objTag = new class_module_tags_tag($this->getSystemid());
		if($objTag->rightEdit()) {

            if($objForm == null)
                $objForm = $this->getAdminForm($objTag);

            return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveTag"));
		}
		else
			return $this->getLang("commons_error_permissions");

    }



}
