<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_search_plugin_portal.php 3530 2011-01-06 12:30:26Z sidler $                                  *
********************************************************************************************************/

/**
 * Interface for all portal search-plugins
 *
 * @package module_search
 * @author sidler@mulchprod.de
 */
interface interface_search_plugin_portal {

    /**
     * Constructor, receiving the term to search for
     *
     * @param string $strSearchterm as db-query
     */
    public function __construct($strSearchterm);


    /**
     * This method is invoked from outside, starts to search for the passed term
     * and returns the results
     *
     * @return class_search_result[]
     */
    public function doSearch();

}
