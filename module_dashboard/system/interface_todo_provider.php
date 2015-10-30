<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * interface_todo_provider
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
interface interface_todo_provider extends interface_generic_plugin {

    const EXTENSION_POINT = "core.dashboard.admin.todo_provider";

    /**
     * Returns an human readable name of this provider
     *
     * @return string
     */
    public function getName();

    /**
     * Returns all todo entries which are currently open for the logged in user. This is used i.e. to display the user a
     * list of open tasks
     *
     * @param string $strCategory
     * @return class_todo_entry[]
     */
    public function getCurrentTodosByCategory($strCategory);

    /**
     * Returns an array of all available categories
     *
     * @return array
     */
    public function getCategories();

    /**
     * Returns whether the currently logged in user can view these events
     *
     * @return boolean
     */
    public function rightView();

}
