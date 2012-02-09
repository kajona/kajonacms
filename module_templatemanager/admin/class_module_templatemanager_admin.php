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

        class_module_templatemanager_template::syncTemplatepacks();
	}

	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));

        return $arrReturn;
	}

    public function getRequiredFields() {
        if($this->getAction() == "copyPack") {
            return array("pack_name" => "string");
        }

        return parent::getRequiredFields();
    }


    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_templatemanager_template::getAllTemplatepacksCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_templatemanager_template::getAllTemplatepacks($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }

    protected function getNewEntryAction($strListIdentifier) {
        $strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            $strReturn .= $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "download", "", $this->getLang("action_download"), $this->getLang("action_download"), "icon_install.gif"));
            $strReturn .= $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("action_new_copy"), $this->getLang("action_new_copy"), "icon_new.gif"));
        }

        return $strReturn;
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->getLang("commons_error_permissions");
    }

    /**
     * @param \class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew(class_admin_formgenerator $objForm = null) {
        if($objForm == null)
            $objForm = $this->getPackAdminForm();

        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "copyPack"));
    }

    private function getPackAdminForm() {
        $objFormgenerator = new class_admin_formgenerator("pack", new class_module_system_common());
        $objFormgenerator->addField(new class_formentry_text("pack", "name"))->setStrLabel($this->getLang("pack_name"))->setBitMandatory(true)->setStrValue($this->getParam("pack_name"));
        $objFormgenerator->addField(new class_formentry_headline())->setStrValue($this->getLang("pack_copy_include"));
        $arrModules = class_resourceloader::getInstance()->getArrModules();
        foreach($arrModules as $strOneModule) {
            //validate theres a template-folder existing
            if(is_dir(_corepath_."/".$strOneModule."/templates"))
                $objFormgenerator->addField(new class_formentry_checkbox("pack", "modules[".$strOneModule."]"))->setStrLabel($strOneModule)->setStrValue(true);
        }
        return $objFormgenerator;
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionCopyPack() {
        $objForm = $this->getPackAdminForm();

        if(is_dir(_realpath_._templatepath_."/".$this->getParam("pack_name")))
            $objForm->addValidationError("name", $this->getLang("pack_folder_existing"));

        if(!$objForm->validateForm())
            return $this->actionNew($objForm);


        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCreate(_templatepath_."/".$this->getParam("pack_name"));

        $arrModules = $this->getParam("pack_modules");
        foreach($arrModules as $strName => $strValue) {
            if($strValue != "") {
                $objFilesystem->folderCopyRecursive("/core/".$strName."/templates", _templatepath_."/".$this->getParam("pack_name"));
            }
        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul")));
    }



}
