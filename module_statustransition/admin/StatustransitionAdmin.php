<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\Statustransition\System\StatustransitionFlow;
use Kajona\Statustransition\System\StatustransitionFlowAssignment;
use Kajona\Statustransition\System\StatustransitionFlowAssignmentFilter;
use Kajona\Statustransition\System\StatustransitionFlowChoiceInterface;
use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\Statustransition\System\StatustransitionFlowStepFilter;
use Kajona\Statustransition\System\StatustransitionGraphWriter;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Database;
use Kajona\System\System\Link;
use Kajona\System\System\Model;

/**
 * Admin class to setup status transition flows
 *
 * @author christoph.kappestein@gmail.com
 *
 * @objectListFlow Kajona\Statustransition\System\StatustransitionFlow
 * @objectNewFlow Kajona\Statustransition\System\StatustransitionFlow
 * @objectEditFlow Kajona\Statustransition\System\StatustransitionFlow
 *
 * @objectListStep Kajona\Statustransition\System\StatustransitionFlowStep
 * @objectNewStep Kajona\Statustransition\System\StatustransitionFlowStep
 * @objectEditStep Kajona\Statustransition\System\StatustransitionFlowStep
 * @objectFilterStep Kajona\Statustransition\System\StatustransitionFlowStepFilter
 *
 * @objectListAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 * @objectNewAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 * @objectEditAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 *
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionAdmin extends AdminEvensimpler implements AdminInterface
{
    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listFlow", "", $this->getLang("list_flow"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listAssignment", "", $this->getLang("list_assignment"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    public function renderAdditionalActions(Model $objListEntry)
    {
        $arrActions = parent::renderAdditionalActions($objListEntry);

        if ($objListEntry instanceof StatustransitionFlow) {
            $arrActions[] = $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "listStep", "&systemid=" . $objListEntry->getSystemid(), "", $this->getLang("action_steps"), "icon_kriterium"));
        }

        return $arrActions;
    }

    public function renderTagAction(Model $objListEntry)
    {
        if ($objListEntry instanceof StatustransitionFlowStep) {
            return "";
        }

        return parent::renderTagAction($objListEntry);
    }

    public function renderPermissionsAction(Model $objListEntry)
    {
        if ($objListEntry instanceof StatustransitionFlowStep) {
            return "";
        }

        return parent::renderPermissionsAction($objListEntry);
    }

    public function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry instanceof StatustransitionFlowStep) {
            return "";
        }

        return parent::renderStatusAction($objListEntry, $strAltActive, $strAltInactive);
    }

    public function renderCopyAction(Model $objListEntry)
    {
        if ($objListEntry instanceof StatustransitionFlowStep) {
            return "";
        }

        return parent::renderCopyAction($objListEntry);
    }

    public function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        $strAction = parent::getNewEntryAction($strListIdentifier, $bitDialog);

        if ($strListIdentifier == "liststep") {
            return $this->objToolkit->listButton(
                Link::getLinkAdmin(
                    $this->getArrModule("modul"), "newStep", "&systemid=".$this->getParam("systemid"),
                    $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                )
            );
        } elseif ($strListIdentifier == "listassignment") {
            return "";
        } else {
            return $strAction;
        }
    }

    /**
     * @return string
     * @permissions view
     */
    public function actionListStep()
    {
        $this->setStrCurObjectTypeName('step');
        $this->setCurObjectClassName(StatustransitionFlowStep::class);

        $objFlow = $this->objFactory->getObject($this->getParam("systemid"));

        /* Create list */
        $objFilter = StatustransitionFlowStepFilter::getOrCreateFromSession();
        $objArraySectionIterator = new ArraySectionIterator(StatustransitionFlowStep::getObjectCountFiltered($objFilter, $this->getSystemid()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(StatustransitionFlowStep::getObjectListFiltered($objFilter, $this->getSystemid(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        /* Render list and filter */
        $strList = $this->renderList($objArraySectionIterator, true, "list".$this->getStrCurObjectTypeName());
        $strGraph = StatustransitionGraphWriter::write($objFlow);

        $strHtml = "<div class='row'>";
        $strHtml .= "<div class='col-md-6'>" . $strList . "</div>";
        $strHtml .= "<div class='col-md-6'><div id='flow-graph' class='mermaid' style='color:#fff;'>" . $strGraph . "</div></div>";
        $strHtml .= "</div>";

        $strHtml .= <<<HTML
<script type="text/javascript">
    require(['mermaid', 'loader'], function(mermaid, loader){
        loader.loadFile(["/core/module_statustransition/scripts/mermaid/mermaid.forest.css"], function(){
            mermaid.init(undefined, $("#flow-graph"));
        });
    });
</script>
HTML;

        return $strHtml;
    }

    /**
     * @return string
     * @permissions view
     */
    public function actionListAssignment()
    {
        $arrInstances = array_filter($this->objResourceLoader->getFolderContent("/system", array(".php"), false, null, function(&$strFile, $strPath){
            $strFile = $this->objClassLoader->getInstanceFromFilename($strPath, null, StatustransitionFlowChoiceInterface::class);
        }));

        $arrFlows = StatustransitionFlow::getObjectListFiltered();
        $arrSelect = array();
        foreach ($arrFlows as $objFlow) {
            $arrSelect[$objFlow->getStrSystemid()] = $objFlow->getStrDisplayName();
        }

        $strLink = Link::getLinkAdminHref("statustransition", "saveAssignment");
        $strReturn = "";
        $strReturn .= $this->objToolkit->formHeader($strLink);
        $strReturn .= $this->objToolkit->listHeader();
        foreach ($arrInstances as $objInstance) {
            $strReturn .= $this->objToolkit->genericAdminList(
                generateSystemid(),
                get_class($objInstance),
                AdminskinHelper::getAdminImage($objInstance->getStrIcon()),
                ""
            );

            $arrFlows = $objInstance->getPossibleFlows();
            foreach ($arrFlows as $strKey => $strTitle) {
                // check whether we have a value
                $objFilter = new StatustransitionFlowAssignmentFilter();
                $objFilter->setStrClass(Database::getInstance()->escape(get_class($objInstance)));
                $objFilter->setStrKey($strKey);
                $arrAssignments = StatustransitionFlowAssignment::getObjectListFiltered($objFilter);
                $strSelected = "";
                if (!empty($arrAssignments)) {
                    $strSelected = reset($arrAssignments)->getStrFlow();
                }

                $strId = md5(get_class($objInstance) . $strKey);
                $strActions = $this->objToolkit->formInputDropdown($strId, $arrSelect, "", $strSelected);
                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $strTitle,
                    "",
                    $strActions
                );
            }
        }

        $strReturn .= $this->objToolkit->listFooter();
        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    /**
     * @return string
     * @permissions edit
     */
    public function actionSaveAssignment()
    {
        $arrInstances = array_filter($this->objResourceLoader->getFolderContent("/system", array(".php"), false, null, function(&$strFile, $strPath){
            $strFile = $this->objClassLoader->getInstanceFromFilename($strPath, null, StatustransitionFlowChoiceInterface::class);
        }));

        $arrFlows = StatustransitionFlow::getObjectListFiltered();
        $arrSelect = array();
        foreach ($arrFlows as $objFlow) {
            $arrSelect[$objFlow->getStrSystemid()] = $objFlow->getStrDisplayName();
        }

        foreach ($arrInstances as $objInstance) {
            $arrFlows = $objInstance->getPossibleFlows();
            foreach ($arrFlows as $strKey => $strTitle) {
                $strId = md5(get_class($objInstance) . $strKey);
                $strFlowId = $this->getParam($strId);

                if (isset($arrSelect[$strFlowId])) {
                    $objFilter = new StatustransitionFlowAssignmentFilter();
                    $objFilter->setStrClass(get_class($objInstance));
                    $objFilter->setStrKey($strKey);
                    $arrAssignments = StatustransitionFlowAssignment::getObjectListFiltered($objFilter);
                    if (!empty($arrAssignments)) {
                        $objAssignment = reset($arrAssignments);
                        $objAssignment->setStrFlow($strFlowId);
                        $objAssignment->updateObjectToDb();
                    } else {
                        $objAssignment = new StatustransitionFlowAssignment();
                        $objAssignment->setStrClass(get_class($objInstance));
                        $objAssignment->setStrKey($strKey);
                        $objAssignment->setStrFlow($strFlowId);
                        $objAssignment->updateObjectToDb();
                    }
                }
            }
        }

        $this->adminReload(Link::getLinkAdminHref("statustransition", "listAssignment"));
        return "";
    }

    /**
     * @return string
     * @permissions view
     */
    protected function actionStepBrowser()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn = "";
        $strFormElement = $this->getParam("form_element");

        $objStep = $this->objFactory->getObject($this->getSystemid());
        $arrSteps = array();

        if ($objStep instanceof StatustransitionFlow) {
            $arrSteps = $objStep->getSteps();
        } elseif ($objStep instanceof StatustransitionFlowStep) {
            /** @var StatustransitionFlow $objFlow */
            $objFlow = $this->objFactory->getObject($objStep->getPrevId());
            $arrSteps = $objFlow->getSteps();
        }

        $strReturn .= $this->objToolkit->listHeader();
        foreach ($arrSteps as $objStep) {
            if (!$objStep->rightView()) {
                continue;
            }

            $strAction = "";
            $strAction .= $this->objToolkit->listButton(
                "<a href=\"#\" title=\"".$this->getLang("prozess_uebernehmen")."\" rel=\"tooltip\" onClick=\"require('v4skin').setObjectListItems(
                    '".$strFormElement."', [{strSystemId: '".$objStep->getSystemid()."', strDisplayName: '".addslashes($objStep->getStrName())."', strIcon:'".addslashes(getImageAdmin("icon_treeLeaf", "", true))."'}], null, '".addslashes(getImageAdmin("icon_delete", "", true))."'); parent.KAJONA.admin.folderview.dialog.hide();\">"
                .AdminskinHelper::getAdminImage("icon_accept") . "</a>"
            );

            $strReturn .= $this->objToolkit->simpleAdminList($objStep, $strAction);
        }

        $strReturn .= $this->objToolkit->listFooter();

        if (count($arrSteps) == 0) {
            $strReturn .= $this->getLang("commons_list_empty");
        }

        return $strReturn;
    }

}
