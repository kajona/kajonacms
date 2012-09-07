<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_votings_admin.php 4042 2011-07-25 17:37:44Z sidler $                              *
********************************************************************************************************/


/**
 * Admin class of the votings-module. Responsible for editing votings and organizing them.
 *
 * @package module_votings
 * @author sidler@mulchprod.de
 */
class class_module_votings_admin extends class_admin_simple implements interface_admin {

    const STR_LIST_ANSWER = "STR_LIST_ANSWER";

	/**
	 * Constructor
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "votings");
        $this->setArrModuleEntry("moduleId", _votings_module_id_);
        parent::__construct();
	}


	public function getOutputModuleNavi() {
	    $arrReturn = array();
    	$arrReturn[] = array("view", getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
    	$arrReturn[] = array("", "");
		$arrReturn[] = array("edit", getLinkAdmin($this->getArrModule("modul"), "newVoting", "", $this->getLang("actionNewVoting"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


    protected function getArrOutputNaviEntries() {
        $arrPathLinks = parent::getArrOutputNaviEntries();
        $arrPath = $this->getPathArray($this->getSystemid());
        //Link to root-folder
        foreach($arrPath as $strOneVoting) {

            $objInstance = class_objectfactory::getInstance()->getObject($strOneVoting);

            if($objInstance instanceof class_module_votings_answer) {
                $arrPathLinks[] = getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=".$objInstance->getPrevId(), $objInstance->getStrDisplayName());
            }
            if($objInstance instanceof class_module_votings_voting) {
                $arrPathLinks[] = getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=".$strOneVoting, $objInstance->getStrDisplayName());
            }
        }

        return $arrPathLinks;
    }


	/**
	 * Returns a list of all categories and all votings
	 * The list can be filtered by categories
	 *
	 * @return string
     * @autoTestable
     * @permissions view
	 */
	protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_votings_voting::getVotingsCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_votings_voting::getVotings(false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
	}

    protected function renderAdditionalActions(class_model $objListEntry) {

        if($objListEntry->rightEdit() && $objListEntry instanceof class_module_votings_voting) {
            return array(
                $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "listAnswers", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("actionListAnswers"), "icon_folderActionOpen.png"))
            );
        }

        return parent::renderAdditionalActions($objListEntry);
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == self::STR_LIST_ANSWER) {
            if($this->getObjModule()->rightEdit()) {
                return array(
                    getLinkAdmin($this->getArrModule("modul"), "newAnswer", "&systemid=".$this->getSystemid(), $this->getLang("actionNewAnswer"), $this->getLang("actionNewAnswer"), "icon_new.png")
                );
            }
        }
        else
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);

        return array();
    }

    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_votings_answer) {
            if($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("voting_delete_answer", $this->getArrModule("modul")),
                    getLinkAdminHref($this->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid())
                );
            }
        }
        else
            return parent::renderDeleteAction($objListEntry);

        return "";
    }


    /**
     * Renders the form to create a new entry
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNew() {
        return $this->actionNewVoting();
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        $objObject = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objObject->rightEdit() && $objObject instanceof class_module_votings_answer)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editAnswer", "&systemid=".$objObject->getSystemid()));

        if($objObject->rightEdit() && $objObject instanceof class_module_votings_voting)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editVoting", "&systemid=".$objObject->getSystemid()));

        return "";
    }


    protected function actionEditVoting() {
        return $this->actionNewVoting("edit");
    }



    /**
     * Show the form to create or edit a voting
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewVoting($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objVoting = new class_module_votings_voting();
        if($strMode == "edit") {
            $objVoting = new class_module_votings_voting($this->getSystemid());

            if(!$objVoting->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getVotingAdminForm($objVoting);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveVoting"));
    }


    private function getVotingAdminForm(class_module_votings_voting $objVoting) {
        $objForm = new class_admin_formgenerator("voting", $objVoting);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed values as a new category to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveVoting() {
        $objVoting = null;

        if($this->getParam("mode") == "new")
            $objVoting = new class_module_votings_voting();

        else if($this->getParam("mode") == "edit")
            $objVoting = new class_module_votings_voting($this->getSystemid());

        if($objVoting != null) {
            $objForm = $this->getVotingAdminForm($objVoting);
            if(!$objForm->validateForm())
                return $this->actionNewVoting($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objVoting->updateObjectToDb();

            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "", ($this->getParam("pe") != "" ? "&peClose=1" : "")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }




    /**
	 * Returns a list of all answers of the voting selected before
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionListAnswers() {

        $objVoting = new class_module_votings_voting($this->getSystemid());
        $strReturn = $this->objToolkit->formHeadline($objVoting->getStrTitle());

        $objArraySectionIterator = new class_array_section_iterator($objVoting->getAllAnswersCount());
        $objArraySectionIterator->setPageNumber(1);
        $objArraySectionIterator->setIntElementsPerPage($objVoting->getAllAnswersCount());
        $objArraySectionIterator->setArraySection($objVoting->getAllAnswers($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $strReturn.$this->renderList($objArraySectionIterator, true, self::STR_LIST_ANSWER);
	}

    protected function actionEditAnswer() {
        return $this->actionNewAnswer("edit");
    }


    /**
     * Show the form to create or edit a votings' answer option
     *
     * @param string $strMode
     * @param class_admin_formgenerator $objForm
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNewAnswer($strMode = "new", class_admin_formgenerator $objForm = null) {

        $objAnswer = new class_module_votings_answer();
        if($strMode == "edit") {
            $objAnswer = new class_module_votings_answer($this->getSystemid());

            if(!$objAnswer->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        if($objForm == null)
            $objForm = $this->getVotingAnswerForm($objAnswer);

        if(!validateSystemid($this->getParam("votingid"))) {
            if($strMode == "new")
                $this->setParam("votingid", $this->getSystemid());
            else
                $this->setParam("votingid", $objAnswer->getPrevId());
        }

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        $objForm->addField(new class_formentry_hidden("", "votingid"))->setStrValue($this->getParam("votingid"));
        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "saveAnswer"));
    }


    private function getVotingAnswerForm(class_module_votings_answer $objAnswer) {
        $objForm = new class_admin_formgenerator("answer", $objAnswer);
        $objForm->generateFieldsFromObject();
        return $objForm;
    }

    /**
     * Saves the passed values as a new answer to the db
     *
     * @return string "" in case of success
     * @permissions edit
     */
    protected function actionSaveAnswer() {
        $objAnswer = null;

        if($this->getParam("mode") == "new")
            $objAnswer = new class_module_votings_answer();

        else if($this->getParam("mode") == "edit")
            $objAnswer = new class_module_votings_answer($this->getSystemid());

        if($objAnswer != null) {
            $objForm = $this->getVotingAnswerForm($objAnswer);
            if(!$objForm->validateForm())
                return $this->actionNewAnswer($this->getParam("mode"), $objForm);

            $objForm->updateSourceObject();
            $objAnswer->updateObjectToDb($this->getParam("votingid"));

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "listAnswers", "&systemid=".$this->getParam("votingid")));
            return "";
        }

        return $this->getLang("commons_error_permissions");
    }

}

