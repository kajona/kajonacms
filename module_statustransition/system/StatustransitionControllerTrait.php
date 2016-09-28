<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Link;
use Kajona\System\System\Model;

/**
 * @author christoph.kappestein@artemeon.de
 * @module statustransition
 */
trait StatustransitionControllerTrait
{
    /**
     * @inject statustransition_manager
     * @var StatustransitionManager
     */
    protected $objStatustransitionManager;

    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry->getIntRecordDeleted() == 1) {
            return "";
        }

        $objStatusTransitionHandler = $this->objStatustransitionManager->getHandler($objListEntry);

        if ($objStatusTransitionHandler instanceof StatustransitionHandler) {
            $objStatus = $objStatusTransitionHandler->getStatus($objListEntry->getIntRecordStatus());

            if ($objStatus instanceof StatustransitionStatus) {
                // get the available transitions for the given status and build up menu
                $arrTransitions = $objStatus->getArrTransitions($objListEntry);
                $arrMenu = array();
                foreach ($arrTransitions as $strTransitionKey => $objTransition) {
                    /** @var StatustransitionTransition $objTransition */

                    $objTargetWorkflowStatus = $objStatusTransitionHandler->getStatus($objTransition->getIntTargetStatus());
                    $arrMenu[] = array(
                        "name" => AdminskinHelper::getAdminImage($objTargetWorkflowStatus->getStrIcon()) . " " . $this->getLang($objTransition->getStrChoiceLabel()),
                        "link" => Link::getLinkAdminHref($this->getArrModule("modul"), "setStatus", "&systemid=" . $objListEntry->getStrSystemid() . "&".StatustransitionHandler::STR_PARAM_TRANSITIONKEY."=" . $strTransitionKey),
                    );
                }

                $strTitleCurrentStatus = $this->getLang($objStatus->getStrTitle());
                if ($objListEntry->rightEdit() && !empty($arrMenu)) {
                    $strMenu = $this->objToolkit->registerMenu(generateSystemid(), $arrMenu);

                    return $this->objToolkit->listButton(
                        "<span class='dropdown'><a href='#' data-toggle='dropdown' role='button'>" . AdminskinHelper::getAdminImage($objStatus->getStrIcon(),
                            $strTitleCurrentStatus) . "</a>" . $strMenu . "</span>"
                    );
                }

                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage($objStatus->getStrIcon(), $strTitleCurrentStatus));
            } else {
                return "";
            }
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
            $strTransitionKey = $this->getParam(StatustransitionHandler::STR_PARAM_TRANSITIONKEY);

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

            $objStatusTransitionHandler = $this->objStatustransitionManager->getHandler($objObject);
            $bitReturn = $objStatusTransitionHandler->handleStatusTransition($objObject->getIntRecordStatus(), $strTransitionKey, $objObject);

            if ($bitReturn) {
                $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "&systemid=" . $objObject->getStrPrevId()));
            }
        }

        return "";
    }
}
