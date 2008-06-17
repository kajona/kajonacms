<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   interface_admin_systemtask.php                                                                      *
*   Interface for all systemtasks                                                                       *
*                                                                                                       *
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
	 *
	 * @return string, "" in case of success, otherwise a string-based error-description
	 */
	public function executeTask();
	
	/**
	 * Uses the commmon toolkit and generates, if needed, the form to be shown right before
	 * the execution of a task. This can be helpfull to collect data needed to run the task.
	 *
	 * @return string or "" if now form is needed
	 */
	public function getAdminForm();
	
}

?>