<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Workflows\System;

use Kajona\System\Admin\AdminFormgenerator;


/**
 * A single workflow has to implement this interface.
 *
 * A handler should be capable to fulfill the following semantic contract:
 *
 * - handler is created and attached to a new workflow -> nothing to do
 *   handler is marked as NEW
 *
 * - transition from new to scheduled:
 *     - initialize() is called, the handler can update it's data (and the data of the workflow)
 *     - schedule() is called, the handler can set the next date the workflow should be triggered
 *   handler is marked as SCHEDULED
 *
 * - execution of workflow and handler, run the handlers main logic
 *   execute is called. depending on the return value, the following scenarios are possible:
 *     - true: handler is marked as EXECUTED and
 *     - false:
 *          - schedule() is called
 *          - handler remains marked as SCHEDULED
 *          - number of executions is increased
 *
 * Please note:
 *   Do NOT call updateObjectToDb on the workflow-instance.
 *   This call is done at the end of every transition. Otherwise the data saved in the objects may get
 *   invalid!
 *
 * @package module_workflows
 */
interface WorkflowsHandlerInterface
{

    /**
     * The workflow is triggered to run.
     * Perform all actions required to get in a new state.
     *
     * The state of the workflow is updated afterwards by the controller.
     *
     * @return bool make sure to return the matching boolean value. It true is returned, the state is shifted to executed. Otherwise the workflow is rescheduled.
     *
     */
    public function execute();

    /**
     * Being called when the current workflow is removed from the database.
     * Provides the possibility to perform e.g. cleanups required to remain consistent.
     *
     * @return void
     */
    public function onDelete();

    /**
     * Setter to save the corresponding workflow-container.
     * Holds all params relevant for the current object
     * and stores object-defined params and values.
     *
     * @param WorkflowsWorkflow $objWorkflow
     *
     * @return void
     */
    public function setObjWorkflow($objWorkflow);

    /**
     * This method should schedule the workflow.
     * Do this by setting the trigger-date in class class_module_workflows_workflow
     *
     * This method is called by the controller. The workflows' state is set to scheduled afterwards.
     * The workflow-object itself is updated automatically, so no need to be done right here.
     *
     * @see class_module_workflows_workflow::setObjTriggerDate
     * @return void
     */
    public function schedule();


    /**
     * If your handler provides ui-actions like forms,
     * create and return them using this method.
     *
     * Return either a string-based form or a form-object.
     *
     * It may get called by the workflow-engine at certain places.
     * The form-wrapper itself is created by the engine :)
     *
     * @return string|AdminFormgenerator
     */
    public function getUserInterface();

    /**
     * The workflow-engine asks this method whether there are any forms / ui parts to
     * show or not.
     * The state can be depending on the current state, needed evaluations
     * can be made.
     *
     * @return bool
     */
    public function providesUserInterface();

    /**
     * Right after submitting the contents of the form provided via getUserInterface(),
     * the engine invokes processUserInput(). Update your data according to the params.
     *
     * The workflow-object itself is updated automatically, so no need to be done right here.
     *
     * @param array $arrParams
     *
     * @return void
     */
    public function processUserInput($arrParams);

    /**
     * Should return a human-readable name of the current workflow
     *
     * @return string
     */
    public function getStrName();

    /**
     * Each handler can be condigured using a set ot three values. It is up to the implementation
     * if the fields are used to store a list of values, e.g. separated by |.
     * The third field is of type text and therefore can consume up to about 65k chars.
     * The first and the second field are varchars limited to 254 characters.
     *
     * The method should return an array of three string, indicating the names of the values - if required.
     * Otherwise returning an empty array will result in the usage of the default names.
     *
     * @return array
     */
    public function getConfigValueNames();

    /**
     * The config-values are set by the controller to each handler during the execution of a single handler.
     * Even if a handler doesn't depend on the config values, the method should be implemented.
     *
     * @param string $strVal1
     * @param string $strVal2
     * @param string $strVal3
     *
     * @return void
     */
    public function setConfigValues($strVal1, $strVal2, $strVal3);

    /**
     * Should return an array of three values at maximum, indicating the default-values used to parametrize the handler itself.
     *
     * @return array
     */
    public function getDefaultValues();
}
