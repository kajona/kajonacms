<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\GenericPluginInterface;

/**
 * TodoProviderInterface
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
interface TodoProviderInterface extends GenericPluginInterface {

    const EXTENSION_POINT = "core.dashboard.admin.todo_provider";
    const LIMITED_COUNT = 25;

    /**
     * Returns an human readable name of this provider
     *
     * @return string
     */
    public function getName();

    /**
     * Returns all todo entries which are currently open for the logged in user. This is used i.e. to display a list of
     * open tasks. If the flag $bitLimited is false all entries are returned else only a subset.
     *
     * @param string $strCategory
     * @param boolean $bitLimited
     * @return TodoEntry[]
     */
    public function getCurrentTodosByCategory($strCategory, $bitLimited = true);

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
