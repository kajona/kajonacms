<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Search\System;


/**
 * A term query is based on a single word, so no boolean combinations
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 4.4
 */
class SearchTermQuery implements SearchQueryInterface
{
    /**
     * @var SearchMetadataFilter
     */
    private $objMetadataFilter;

    /**
     * @param SearchTerm $objTerm
     * @return void
     */
    public function setObjTerm($objTerm) {
        $this->objTerm = $objTerm;
    }

    /**
     * @return SearchTerm
     */
    public function getObjTerm() {
        return $this->objTerm;
    }

    /**
     * @var SearchTerm
     */
    private $objTerm;

    /**
     * @param SearchTerm $objTerm
     */
    function __construct($objTerm) {
        $this->setObjTerm($objTerm);
    }


    /**
     * Generates a query to count the results matching the current terms.
     *
     * @param string &$strQuery
     * @param string[] &$arrParameters
     * @return void
     */
    public function getCountQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "SELECT COUNT(*) AS cnt FROM (SELECT search_ix_document_id ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_ix_document_id ) as cq";
    }

    /**
     * This method builds the query and the matching parameters-array in order to load the list
     * of results.
     *
     * @param string &$strQuery
     * @param string[] &$arrParameters
     * @return void
     */
    public function getListQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "SELECT search_ix_document_id, search_ix_system_id, sum(search_ix_content_score) AS score ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_ix_document_id, search_ix_system_id ORDER BY score DESC";

    }

    /**
     * builds the from and where restrictions of the current query
     * @param string &$strQuery
     * @param string[] &$arrParameters
     * @return void
     */
    private function internalBuildQuery(&$strQuery, &$arrParameters) {

        $strQuery .= "FROM "._dbprefix_."search_ix_document AS D
                INNER JOIN "._dbprefix_."search_ix_content AS z
                        ON search_ix_content_document_id = search_ix_document_id
             ".($this->objMetadataFilter != null ? "  LEFT JOIN "._dbprefix_."system ON search_ix_system_id = system_id " : "")."
                     WHERE 1=1 ";

        //metadata filter
        if($this->objMetadataFilter != null) {
            $this->objMetadataFilter->getQuery($strQuery, $arrParameters);
        }

        $strQuery .= " AND z.search_ix_content_content LIKE ? AND D.search_ix_document_id = z.search_ix_content_document_id ";
        $arrParameters[] = $this->getObjTerm()->getStrText()."%";

        if ($this->getObjTerm()->getStrField() != null) {
            $strQuery .= "AND search_ix_content_field_name = ? ";
            $arrParameters[] = $this->getObjTerm()->getStrField();
        }
    }


    /**
     * @param SearchMetadataFilter $objMetadataFilter
     * @return mixed
     */
    public function setMetadataFilter($objMetadataFilter) {
        $this->objMetadataFilter = $objMetadataFilter;
    }
}
