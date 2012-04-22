<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Admin-GUI of the packagemanager.
 * The packagemanager provides a way to handle the template-packs available.
 * In addition, setting packs as the current active-one is supported, too.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_admin extends class_admin_simple implements interface_admin {

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "packagemanager");
        $this->setArrModuleEntry("moduleId", _packagemanager_module_id_);
		parent::__construct();

	}

    public function getOutputModuleNavi() {
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
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        // TODO: Implement actionNew() method.
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        // TODO: Implement actionEdit() method.
    }

    /**
     * Renders the general list of records
     * @return string
     * @permissions view
     */
    protected function actionList() {
        // TODO: Implement actionList() method.
    }
}
