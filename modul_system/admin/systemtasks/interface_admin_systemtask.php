<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

/**
 * Interface to specify the layout of a systemtask.
 * To load a simple systemtask into the system, implement the hook-methods defined in this interface.
 *
 * @package modul_system
 */
interface interface_admin_systemtask {
 
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
     * reload url via $thjis->setStrReloadParam().
     * If you return anything different than a number between 0 and 100, the returned text is
     * rendered as given.
	 *
	 * @return string, a number betwenn 0 and 100 to indicate the progress, otherwise a string-based message
	 */
	public function executeTask();
	
	/**
	 * Uses the commmon toolkit and generates, if needed, the form to be shown right before
	 * the execution of a task. This can be helpfull to collect data needed to run the task.
	 *
	 * @return string or "" if now form is needed
	 */
	public function getAdminForm();

    /**
	 * If a form is generated via getAdminForm(), getSubmitParams returns
     * the params to add  to the post-params.
     *
     * Note: This happens after submitting the page. So, you can handle all
     * actions necessary to be ready to process the values submited by the user via the form.
	 *
	 * @return string or "" if now params are needed
	 */
	public function getSubmitParams();


    /**
     * The group identifier is used to group the tasks available in an installation.
     * Don't use too specific identifierts to avoid having a single group for every task,
     * refer to a rather general therm, e.g. "caching" or "database".
     * The identifiert is resolved via the systems' language-files internally.
     * Currently, there are: "", "database", "cache", "stats"
     *
     * @return string or "" for default group
     */
    public function getGroupIdentifier();

	
}

?>