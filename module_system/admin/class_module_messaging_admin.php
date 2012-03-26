<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

/**
 * Admin-class to manage a users messages.
 * In addition, the user is able to configure each messageprovider (enable / disable, send by mail, ...)
 *
 * @package module_messaging
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_messaging_admin extends class_admin_simple implements interface_admin {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $this->setArrModuleEntry("modul", "messaging");
        $this->setArrModuleEntry("moduleId", _messaging_module_id_);
		parent::__construct();

	}


    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "config", "", $this->getLang("actionConfig"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		return $arrReturn;
	}

    /**
     * Renders the form to configure each messageprovider
     *
     * @permissions edit
     * @autoTestable
     *
     * @return string
     */
    protected function actionConfig() {
        $objHandler = new class_module_messaging_messagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();

        $strReturn = "";

        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->getArrModule("modul"), "saveConfig"));

        foreach($arrMessageproviders as $objOneProvider) {

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);

            $strReturn .= $this->objToolkit->formHeadline($objOneProvider->getStrName());
            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_enabled", $this->getLang("provider_enabled"), $objConfig->getBitEnabled() == 1);
            $strReturn .= $this->objToolkit->formInputCheckbox($objOneProvider->getStrIdentifier()."_bymail", $this->getLang("provider_bymail"), $objConfig->getBitBymail() == 1);
        }

        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    /**
     * Stores the submitted config-data back to the database
     *
     * @permissions edit
     * @return void
     */
    protected function actionSaveConfig() {

        $objHandler = new class_module_messaging_messagehandler();
        $arrMessageproviders = $objHandler->getMessageproviders();

        foreach($arrMessageproviders as $objOneProvider) {

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($this->objSession->getUserID(), $objOneProvider);
            $objConfig->setBitBymail($this->getParam($objOneProvider->getStrIdentifier()."_bymail") != "");
            $objConfig->setBitEnabled($this->getParam($objOneProvider->getStrIdentifier()."_enabled") != "");
            $objConfig->updateObjectToDb();

        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul")));
    }


    /**
	 * Returns a list of the languages
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_messaging_message::getNumberOfMessagesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_messaging_message::getMessagesForUser(
            $this->objSession->getUserID(),
            $objArraySectionIterator->calculateStartPos(),
            $objArraySectionIterator->calculateEndPos()
        ));

        return $this->renderList($objArraySectionIterator);

	}

    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->actionView();
    }

    /**
     * Renders the form to create a new entry
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul")));
    }

    /**
     * Creates a summary of the message
     *
     * @return string
     * @permissions view
     */
	protected function actionView() {
        $objMessage = new class_module_messaging_message($this->getSystemid());

        if($objMessage->getStrUser() == $this->objSession->getUserID()) {

            $strReturn = "";
            if(!$objMessage->getBitRead()) {
                $objMessage->setBitRead(true);
                $objMessage->updateObjectToDb();
            }

            $strReturn .= $this->objToolkit->formHeadline(dateToString($objMessage->getObjDate()));

            $strReturn .= $this->objToolkit->getTextRow(nl2br($objMessage->getStrBody()));

            return $strReturn;
        }
        else
            return $this->getLang("commons_error_permissions");

	}



}
