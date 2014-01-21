<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Interface for search queries
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 *
 */
interface interface_search_query {

    /**
     * @param class_module_search_metadata_filter $objMetadataFilter
     * @return mixed
     */
    public function setMetadataFilter($objMetadataFilter);

    /**
     * This method builds the query and the matching parameters-array in order to load the list
     * of results.
     *
     * @param string $strQuery
     * @param string[] $arrParameters
     *
     * @return void
     */
    public function getListQuery(&$strQuery, &$arrParameters);

    /**
     * Generates a query to count the results matching the current terms.
     *
     * @param string $strQuery
     * @param string[] $arrParameters
     *
     * @return void
     */
    public function getCountQuery(&$strQuery, &$arrParameters);

}
