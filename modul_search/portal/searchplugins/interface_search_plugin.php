<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Interface for all search-plugins
 *
 * @package modul_downloads
 */
interface interface_search_plugin {

    /**
     * Constructor, receiving the term to search for
     *
     * @param string $strSearchterm as db-query
     * @param string $strSearchtermRaw as text
     */
    public function __construct($strSearchterm, $strSearchtermRaw);


    /**
     * This method is invoked from outside, starts to search for the passed term
     * and returns the results
     *
     * @return array
     */
    public function doSearch();

}
?>