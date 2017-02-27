<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\Xml;

/**
 * @author christoph.kappestein@artemeon.de
 * @module flow
 */
trait FlowControllerTrait
{
    /**
     * @inject flow_manager
     * @var FlowManager
     */
    protected $objFlowManager;

    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        $objCurrentStatus = $this->objFlowManager->getCurrentStepForModel($objListEntry);
        if ($objCurrentStatus === null) {
            return "";
        }

        $strIcon = AdminskinHelper::getAdminImage($objCurrentStatus->getStrIcon(), $objCurrentStatus->getStrDisplayName());
        $strMenuId = "status-menu-" . generateSystemid();
        $strDropdownId = "status-dropdown-" . generateSystemid();
        $strReturn = $this->objToolkit->listButton(
            "<span class='dropdown status-dropdown' id='" . $strDropdownId . "'><a href='#' data-toggle='dropdown' role='button'>" . $strIcon . "</a><div class='dropdown-menu generalContextMenu' role='menu' id='" . $strMenuId . "'></div></span>"
        );

        $strParams = http_build_query(["admin" => 1, "module" => $objListEntry->getArrModule('module'), "action" => "showStatusMenu", "systemid" => $objListEntry->getSystemid()], null, "&");
        $strReturn .= '<script type="text/javascript">
require(["jquery", "ajax"], function($, ajax){
    $("#' . $strDropdownId . '").on("show.bs.dropdown", function () {
        ajax.loadUrlToElement("#' . $strMenuId . '", "/xml.php?' . $strParams . '");
    });
});
</script>';

        return $strReturn;
    }

    /**
     * Action to set the next status
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetStatus()
    {
        $objObject = $this->objFactory->getObject($this->getSystemid());
        if ($objObject instanceof Model) {
            $strTransitionId = $this->getParam("transition_id");
            $objFlow = $this->objFlowManager->getFlowForModel($objObject);
            $objTransition = Objectfactory::getInstance()->getObject($strTransitionId);

            if ($objTransition instanceof FlowTransition) {
                $arrActions = $objTransition->getArrActions();
                $objForm = new AdminFormgenerator("", null);
                $bitInputRequired = false;

                foreach ($arrActions as $objAction) {
                    if ($objAction instanceof FlowActionUserInputInterface) {
                        $objForm->addField(new FormentryHeadline())->setStrValue($objAction->getTitle());
                        $objAction->configureUserInputForm($objForm);
                        $bitInputRequired = true;
                    }
                }

                if ($bitInputRequired) {
                    if ($_SERVER["REQUEST_METHOD"] == "GET" || !$objForm->validateForm()) {
                        $strForm = $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "setStatus", "&systemid=" . $objObject->getStrSystemid() . "&transition_id=" . $strTransitionId));
                        return $strForm;
                    } else {
                        foreach ($arrActions as $objAction) {
                            if ($objAction instanceof FlowActionUserInputInterface) {
                                $objActionForm = new AdminFormgenerator("", null);
                                $objAction->configureUserInputForm($objActionForm);
                                $arrFields = $objActionForm->getArrFields();

                                $arrData = [];
                                foreach ($arrFields as $strName => $objField) {
                                    $arrData[$strName] = $this->getParam($strName);
                                }

                                $objAction->handleUserInput($objObject, $objTransition, $arrData);
                            }
                        }
                    }
                }

                $objHandler = $objFlow->getHandler();
                $bitReturn = $objHandler->handleStatusTransition($objObject, $objTransition);

                if ($bitReturn) {
                    $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&systemid=" . $objObject->getStrPrevId()));
                }
            }
        }

        return "";
    }

    /**
     * Renders the status menu
     *
     * @return string
     * @permissions view
     * @responseType html
     */
    protected function actionShowStatusMenu()
    {
        Xml::setBitSuppressXmlHeader(true);

        $objListEntry = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        $arrTransitions = $this->objFlowManager->getPossibleTransitionsForModel($objListEntry);
        if (!empty($arrTransitions)) {
            $arrMenu = array();
            foreach ($arrTransitions as $objTransition) {
                /** @var FlowTransition $objTransition */
                $objTargetStatus = $objTransition->getTargetStatus();

                $arrMenu[] = array(
                    "name" => AdminskinHelper::getAdminImage($objTargetStatus->getStrIcon()) . " " . $objTargetStatus->getStrDisplayName(),
                    "link" => Link::getLinkAdminHref($this->getArrModule("modul"), "setStatus", "&systemid=" . $objListEntry->getStrSystemid() . "&transition_id=" . $objTransition->getSystemid()),
                );
            }

            if (!empty($arrMenu)) {
                $strHtml = $this->objToolkit->registerMenu(generateSystemid(), $arrMenu);

                // hack to remove the div around the ul since the div is already in the html
                preg_match("#<ul>(.*)</ul>#ims", $strHtml, $arrMatches);

                return $arrMatches[0];
            }
        }

        return "<ul><li class='dropdown-header'>No status available</li></ul>";
    }
}
