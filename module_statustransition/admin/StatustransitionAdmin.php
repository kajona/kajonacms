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
use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\Statustransition\System\StatustransitionGraphWriter;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
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

    public function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        $strAction = parent::getNewEntryAction($strListIdentifier, $bitDialog);

        if ($strListIdentifier == "liststep") {
            return $this->objToolkit->listButton(
                Link::getLinkAdmin(
                    $this->getArrModule("modul"), "newStep", "&systemid=".$this->getParam("systemid"), $this->getLang("commons_list_new"), $this->getLang("commons_list_new"), "icon_new"
                )
            );
        } else {
            return $strAction;
        }
    }

    public function actionListStep()
    {
        $this->setStrCurObjectTypeName('step');
        $this->setCurObjectClassName(StatustransitionFlowStep::class);

        $objFlow = $this->objFactory->getObject($this->getParam("systemid"));
        
        $strList = parent::actionList();
        $strGraph = StatustransitionGraphWriter::write($objFlow);

        $strHtml = "<div class='row'>";
        $strHtml .= "<div class='col-md-6'>" . $strList . "</div>";
        $strHtml .= "<div class='col-md-6'><div id='flow-graph' class='mermaid'>" . $strGraph . "</div></div>";
        $strHtml .= "</div>";

        $strHtml .= <<<HTML
<script type="text/javascript">
    $(document).ready(function(){
        KAJONA.admin.loader.loadFile(["/core/module_statustransition/admin/scripts/mermaid/mermaid.min.js", "/core/module_statustransition/admin/scripts/mermaid/mermaid.forest.css"], function(){
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
                "<a href=\"#\" title=\"".$this->getLang("prozess_uebernehmen")."\" rel=\"tooltip\" onClick=\"KAJONA.v4skin.setObjectListItems(
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
