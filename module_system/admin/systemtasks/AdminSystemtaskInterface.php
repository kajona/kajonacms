<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/


namespace Kajona\System\Admin\Systemtasks;

/**
 * Interface to specify the layout of a systemtask.
 * To load a simple systemtask into the system, implement the hook-methods defined in this interface.
 *
 * @package module_system
 */
interface AdminSystemtaskInterface {

    /**
     * Returns the internal name of the task. This name should correspond with the filename of the task.
     *
     * @return string
     */
    public function getStrInternalTaskName();

    /**
     * Returns the human-readable, language-dependent name of the task. This name is displayed in the admin-
     * area.
     *
     * @return string
     */
    public function getStrTaskName();

    /**
     * Starts the execution of the task.
     * The return value can have different meanings. If you return a number between 0 and 100,
     * the system generates a percent-beam indicating the progress. In this case, make sure to set the
     * reload url via $this->setStrReloadParam().
     * If you return anything different than a number between 0 and 100, the returned text is
     * rendered as given.
     *
     * @return string, a number between 0 and 100 to indicate the progress, otherwise a string-based message
     */
    public function executeTask();

    /**
     * Uses the common toolkit and generates, if needed, the form to be shown right before
     * the execution of a task. This can be helpful to collect data needed to run the task.
     *
     * @return string or "" if now form is needed
     */
    public function getAdminForm();

    /**
     * If a form is generated via getAdminForm(), getSubmitParams returns
     * the params to add  to the post-params.
     * Note: This happens after submitting the page. So, you can handle all
     * actions necessary to be ready to process the values submitted by the user via the form.
     *
     * @return string or "" if now params are needed
     */
    public function getSubmitParams();


    /**
     * The group identifier is used to group the tasks available in an installation.
     * Don't use too specific identifiers to avoid having a single group for every task,
     * refer to a rather general therm, e.g. "caching" or "database".
     * The identifier is resolved via the systems' language-files internally.
     * Currently, there are: "", "database", "cache", "stats"
     *
     * @return string or "" for default group
     */
    public function getGroupIdentifier();

}

