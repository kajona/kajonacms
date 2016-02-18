<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Search\System;


/**
 * Interface for search queries
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 *
 */
interface SearchQueryInterface {


    /**
     * @param SearchMetadataFilter $objMetadataFilter
     * @return mixed
     */
    public function setMetadataFilter($objMetadataFilter);

    /**
     * This method builds the query and the matching parameters-array in order to load the list
     * of results.
     *
     * @param string &$strQuery
     * @param string[] &$arrParameters
     *
     * @return void
     */
    public function getListQuery(&$strQuery, &$arrParameters);

    /**
     * Generates a query to count the results matching the current terms.
     *
     * @param string &$strQuery
     * @param string[] &$arrParameters
     *
     * @return void
     */
    public function getCountQuery(&$strQuery, &$arrParameters);

}
