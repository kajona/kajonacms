<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_navigation_tree.php 4582 2012-04-11 18:27:04Z sidler $                              *
********************************************************************************************************/

/**
 * Admin class to handle all guestbook-stuff like creating guestbook, deleting posts, ...
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 */
class class_module_guestbook_admin extends class_admin_simple implements interface_admin  {


    private $STR_POST_LIST = "STR_POST_LIST";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->setArrModuleEntry("moduleId", _guestbook_module_id_);
		$this->setArrModuleEntry("modul", "guestbook");
		parent::__construct();
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getLang("actionNew"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


    /**
     * Renders the form to create a new entry
     *
     * @param string $strMode
     * @param \class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew($strMode = "new", class_admin_formgenerator $objForm = null) {
        $objGB = new class_module_guestbook_guestbook();
        if($strMode == "edit") {
            $objGB = new class_module_guestbook_guestbook($this->getSystemid());

            if(!$objGB->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getAdminForm($objGB);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveGb"));
    }

    private function getAdminForm(class_module_guestbook_guestbook $objGB) {
        $objForm = new class_admin_formgenerator("gb", $objGB);
        $objForm->generateFieldsFromObject();
        $objForm->getField("guestbooktitle")->setStrLabel($this->getLang("commons_title"));
        return $objForm;
    }

    /**
     * @return string
     * @permissions edit
     */
    protected function actionSaveGb() {
        $objGb = null;

        if($this->getParam("mode") == "new")
            $objGb = new class_module_guestbook_guestbook();

        else if($this->getParam("mode") == "edit")
            $objGb = new class_module_guestbook_guestbook($this->getSystemid());

        if($objGb != null) {

            $objForm = $this->getAdminForm($objGb);
            if(!$objForm->validateForm())
                return $this->actionNew($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objGb->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }

    /**
     * Renders the form to edit an existing entry
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objInstance instanceof class_module_guestbook_guestbook)
            return $this->actionNew("edit");

        if($objInstance instanceof class_module_guestbook_post)
            return $this->actionEditPost();
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($this->getObjModule()->rightEdit() && $strListIdentifier != $this->STR_POST_LIST) {
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("actionNew"), $this->getLang("actionNew"), "icon_new.gif"));
        }
    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_guestbook_guestbook) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->arrModule["modul"], "viewGuestbook", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("actionViewGuestbook"), "icon_bookLens.gif")
                )
            );
        }
    }

    /**
     * @param interface_model|class_model $objListEntry
     * @return string
     */
    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_guestbook_post) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("post_loeschen_frage", $objListEntry->getArrModule("modul")),
                    getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid())
                );
            }
        }
        else
            return parent::renderDeleteAction($objListEntry);
    }


    /**
	 * Returns a list off all guestbooks available
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionList() {

        $objIterator = new class_array_section_iterator(class_module_guestbook_guestbook::getGuestbooksCount());
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_guestbook_guestbook::getGuestbooks($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $this->renderList($objIterator);
	}


	/**
	 * Returns a list of all posts belonging to the selected guestbook
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionViewGuestbook() {
		$objGuestbook = new class_module_guestbook_guestbook($this->getSystemid());
		if($objGuestbook->rightView()) {

            $objIterator = new class_array_section_iterator(class_module_guestbook_post::getPostsCount($this->getSystemid()));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(class_module_guestbook_post::getPosts($this->getSystemid(), false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

            return $this->renderList($objIterator, false, $this->STR_POST_LIST);
		}
		else
			return $this->getLang("commons_error_permissions");

	}

    /**
     * Shows a form to edit the content of a post
     *
     * @param \class_admin_formgenerator|null $objForm
     *
     * @return string
     */
    protected function actionEditPost(class_admin_formgenerator $objForm = null) {
        $strReturn = "";
        $objPost = new class_module_guestbook_post($this->getSystemid());
        //check rights
        if($objPost->rightEdit()) {
            if($objForm == null)
                $objForm = $this->getPostForm($objPost);

            return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "savePost"));
        }
        else
            $strReturn .= $this->getLang("commons_error_permissions");

        return $strReturn;
    }


    private function getPostForm(class_module_guestbook_post $objPost) {
        $objForm = new class_admin_formgenerator("post", $objPost);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * @return string
     * @permissions edit
     */
    protected function actionSavePost() {

        $objPost = new class_module_guestbook_post($this->getSystemid());

        if($objPost->rightEdit()) {

            $objForm = $this->getPostForm($objPost);
            if(!$objForm->validateForm())
                return $this->actionEditPost($objForm);

            $objForm->updateSourceObject();
            $objPost->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "viewGuestbook", "&systemid=".$objPost->getPrevId()));
            return "";
        }
        else
            return $this->getLang("commons_error_permissions");
    }

}

