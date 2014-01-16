<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: $                                  *
********************************************************************************************************/

/**
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class class_module_search_term_query implements interface_search_query {
    /**
     * @var class_module_search_metadata_filter
     */
    private $objMetadataFilter;

    /**
     * @param \class_module_search_term $objTerm
     */
    public function setObjTerm($objTerm) {
        $this->objTerm = $objTerm;
    }

    /**
     * @return \class_module_search_term
     */
    public function getObjTerm() {
        return $this->objTerm;
    }

    /**
     * @var class_module_search_term
     */
    private $objTerm;

    function __construct($objTerm) {
        $this->setObjTerm($objTerm);
    }


    /**
     * Generates a query to count the results matching the current terms.
     *
     * @param string $strQuery
     * @param string[] $arrParameters
     */
    public function getCountQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "SELECT COUNT(*) FROM (SELECT search_index_document_id ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_index_document_id ) as cq";
    }

    /**
     * This method builds the query and the matching parameters-array in order to load the list
     * of results.
     *
     * @param string $strQuery
     * @param string[] $arrParameters
     */
    public function getListQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "SELECT search_index_document_id, search_index_system_id, sum(search_index_content_score) AS score ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_index_document_id, search_index_system_id ORDER BY score DESC";

    }

    /**
     * builds the from and where restrictions of the current query
     * @param $strQuery
     * @param $arrParameters
     */
    private function internalBuildQuery(&$strQuery, &$arrParameters) {

        $strQuery .= "FROM "._dbprefix_."search_index_document AS D
                INNER JOIN "._dbprefix_."search_index_content AS z
                        ON search_index_content_document_id = search_index_document_id
             ".($this->objMetadataFilter != null ? "  LEFT JOIN "._dbprefix_."system ON search_index_system_id = system_id " : "")."
                     WHERE 1=1 ";

        //metadata filter
        if($this->objMetadataFilter != null) {
            $this->objMetadataFilter->getQuery($strQuery, $arrParameters);
        }

        $strQuery .= " AND z.search_index_content_content LIKE ? AND D.search_index_document_id = z.search_index_content_document_id ";
        $arrParameters[] = $this->getObjTerm()->getStrText()."%";

        if ($this->getObjTerm()->getStrField() != null) {
            $strQuery .= "AND search_index_content_field_name = ? ";
            $arrParameters[] = $this->getObjTerm()->getStrField();
        }
    }


    /**
     * @param class_module_search_metadata_filter $objMetadataFilter
     * @return mixed
     */
    public function setMetadataFilter($objMetadataFilter) {
        $this->objMetadataFilter = $objMetadataFilter;
    }
}
