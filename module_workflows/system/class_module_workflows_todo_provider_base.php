<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * @package module_workflows
 */
abstract class class_module_workflows_todo_provider_base implements interface_todo_provider
{
    public static function getExtensionName()
    {
        return self::EXTENSION_POINT;
    }

    public function getCurrentTodosByCategory($strCategory, $bitLimited = true)
    {
        if (in_array($strCategory, array_keys($this->getWorkflowClasses()))) {
            return $this->getPendingWorkflows($strCategory, $bitLimited);
        } else {
            return array();
        }
    }

    public function getCategories()
    {
        return $this->getWorkflowClasses();
    }

    protected function getPendingWorkflows($strWorkflowClass, $bitLimited)
    {
        $objLang = class_lang::getInstance();
        $arrUsers = array_merge(array(class_session::getInstance()->getUserID()), class_session::getInstance()->getGroupIdsAsArray());
        $arrResult = array();

        if ($bitLimited) {
            $arrWorkflows = class_module_workflows_workflow::getPendingWorkflowsForUser($arrUsers, 0, self::LIMITED_COUNT, array($strWorkflowClass));
        } else {
            $arrWorkflows = class_module_workflows_workflow::getPendingWorkflowsForUser($arrUsers, false, false, array($strWorkflowClass));
        }

        foreach ($arrWorkflows as $objWorkflow) {
            if ($objWorkflow->getObjWorkflowHandler()->providesUserInterface()) {
                /** @var class_module_workflows_workflow $objWorkflow */
                $objTodo = new class_todo_entry();
                $objTodo->setStrIcon($objWorkflow->getStrIcon());
                $objTodo->setStrCategory($strWorkflowClass);
                $objTodo->setStrDisplayName($objWorkflow->getStrDisplayName());

                if ($this->shouldUseTriggerDate()) {
                    $objTodo->setObjValidDate($objWorkflow->getObjTriggerdate());
                }

                $objTodo->setArrModuleNavi(array(
                    class_link::getLinkAdmin("workflows", "showUI", "&systemid=" . $objWorkflow->getSystemid(), "", $objLang->getLang("workflow_ui", "workflows"), "icon_workflow_ui")
                ));

                $arrResult[] = $objTodo;
            }
        }

        return $arrResult;
    }

    /**
     * Returns an array containing all classes
     *
     * @return array<workflow_class => "category label">
     */
    abstract protected function getWorkflowClasses();

    /**
     * Can be overwritten to use the trigger date as valid date for the todo entry
     *
     * @return bool
     */
    protected function shouldUseTriggerDate()
    {
        return false;
    }
}

