<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;

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
        $arrTransitions = $this->objFlowManager->getPossibleTransitionsForModel($objListEntry);
        if (!empty($arrTransitions)) {
            $arrMenu = array();
            foreach ($arrTransitions as $objTransition) {
                /** @var FlowTransition $objTransition */
                $objTargetStep = $objTransition->getTargetStep();

                $arrMenu[] = array(
                    "name" => AdminskinHelper::getAdminImage($objTargetStep->getStrIcon()) . " " . $objTargetStep->getStrDisplayName(),
                    "link" => Link::getLinkAdminHref($this->getArrModule("modul"), "setStatus", "&systemid=" . $objListEntry->getStrSystemid() . "&transition_id=" . $objTransition->getSystemid()),
                );
            }

            if ($objListEntry->rightEdit() && !empty($arrMenu)) {
                $strMenu = $this->objToolkit->registerMenu(generateSystemid(), $arrMenu);
                $strIcon = AdminskinHelper::getAdminImage($objCurrentStatus->getStrIcon(), $objCurrentStatus->getStrDisplayName());

                return $this->objToolkit->listButton(
                    "<span class='dropdown'><a href='#' data-toggle='dropdown' role='button'>" . $strIcon . "</a>" . $strMenu . "</span>"
                );
            }

            return $this->objToolkit->listButton(AdminskinHelper::getAdminImage($objCurrentStatus->getStrIcon(), $objCurrentStatus->getStrDisplayName()));
        } else {
            return parent::renderStatusAction($objListEntry, $strAltActive, $strAltInactive);
        }
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

            /*
            if ($objObject->rightEdit() && $strTransitionKey == StatustransitionHandlerRiskContainer::STR_STATUS_KEY_REVIEW_TO_OPEN) {
                // show form
                $objForm = new RiskContainerRejectFormgenerator("riskcontainer", $objObject);
                $objForm->generateFieldsFromObject();

                if ($_SERVER["REQUEST_METHOD"] == "GET") {
                    $strForm = $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "setStatus", "&systemid=" . $objObject->getStrSystemid()));
                    return $strForm;
                } else {
                    // save remark
                    $objForm->updateSourceObject();
                    $objObject->updateObjectToDb();
                }
            }
            */

            $objFlow = $this->objFlowManager->getFlowForModel($objObject);
            $objTransition = Objectfactory::getInstance()->getObject($strTransitionId);

            if ($objTransition instanceof FlowTransition) {
                $objHandler = $objFlow->getHandler();
                $bitReturn = $objHandler->handleStatusTransition($objObject, $objTransition);

                if ($bitReturn) {
                    $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&systemid=" . $objObject->getStrPrevId()));
                }
            }
        }

        return "";
    }
}
