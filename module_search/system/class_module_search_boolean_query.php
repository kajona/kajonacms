<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class class_module_search_boolean_query implements interface_search_query {
    const   BOOLEAN_CLAUSE_OCCUR_MUST = "1";
    const   BOOLEAN_CLAUSE_OCCUR_MUST_NOT = "2";
    const   BOOLEAN_CLAUSE_OCCUR_SHOULD = "3";
    const   BOOLEAN_CLAUSE_OCCUR_SHOULD_NOT = "4";

    /**
     * @var class_module_search_term[]
     */
    private $arrMustOccurs = array();

    /**
     * @var class_module_search_term[]
     */
    private $arrMustNotOccurs = array();

    /**
     * @var class_module_search_term[]
     */
    private $arrShouldOccurs = array();

    /**
     * @var class_module_search_term[]
     */
    private $arrShouldNotOccurs = array();

    /**
     * @var class_module_search_metadata_filter
     */
    private $objMetadataFilter;

    /**
     * @return array
     */
    public function getMustNotOccurs() {
        return $this->arrMustNotOccurs;
    }

    /**
     * @return array
     */
    public function getMustOccurs() {
        return $this->arrMustOccurs;
    }

    /**
     * @return array
     */
    public function getShouldNotOccurs() {
        return $this->arrShouldNotOccurs;
    }

    /**
     * @return array
     */
    public function getShouldOccurs() {
        return $this->arrShouldOccurs;
    }

    /**
     * @param $objSearchTerm class_module_search_term
     * @param $booleanClauseOccur
     */
    public function add($objSearchTerm, $booleanClauseOccur) {
        switch($booleanClauseOccur) {
            case $this::BOOLEAN_CLAUSE_OCCUR_MUST:
                $this->arrMustOccurs[] = $objSearchTerm;
                break;
            case $this::BOOLEAN_CLAUSE_OCCUR_MUST_NOT:
                $this->arrMustNotOccurs[] = $objSearchTerm;
                break;
            case $this::BOOLEAN_CLAUSE_OCCUR_SHOULD:
                $this->arrShouldOccurs[] = $objSearchTerm;
                break;
            case $this::BOOLEAN_CLAUSE_OCCUR_SHOULD_NOT:
                $this->arrShouldNotOccurs[] = $objSearchTerm;
                break;
        }
    }


    /**
     * This method returns the the query
     *
     * @param $strQuery
     * @param $arrParameters
     *
     * @return string
     */
    public function getCountQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "SELECT COUNT(*) FROM (SELECT search_ix_document_id, search_ix_system_id ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_ix_document_id, search_ix_system_id) as cq";
    }


    /**
     * This method returns the the query
     *
     * @param $strQuery
     * @param $arrParameters
     *
     * @return string
     */
    public function getListQuery(&$strQuery, &$arrParameters) {

        /* should look like ;-)
            select search_ix_document_id, search_ix_system_id , sum(search_ix_content_score) as score  from
              (select search_ix_document_id, search_ix_system_id from kajona_search_ix_document as D
                left join kajona_system ON search_ix_system_id = system_id
               where
                system_module_nr in  (1, 2) and
                exists (select 1 from kajona_search_ix_content where search_ix_content_content= ? and search_ix_document_id= search_ix_content_document_id) and
                exists (select 1 from kajona_search_ix_content where search_ix_content_content= ? and search_ix_document_id= search_ix_content_document_id)
              ) a
            inner join (select search_ix_content_document_id, search_ix_content_score from kajona_search_ix_content where
                    (search_ix_content_content ='hallo' AND search_ix_content_field_name = 'title')
                    OR (search_ix_content_content ='welt')
                    OR (search_ix_content_content ='blub' AND search_ix_content_field_name = 'subtitle')) z
            on  search_ix_document_id=search_ix_content_document_id group by search_ix_document_id;
        */


        $strQuery .= "SELECT search_ix_document_id, search_ix_system_id , SUM(search_ix_content_score) AS score ";
        $this->internalBuildQuery($strQuery, $arrParameters);
        $strQuery .= " GROUP BY search_ix_document_id, search_ix_system_id ORDER BY score DESC ";

    }

    /**
     * Adds the queried tables and the filter-restrictions to the generated query
     * @param string $strQuery
     * @param string[] $arrParameters
     */
    private function internalBuildQuery(&$strQuery, &$arrParameters) {
        $strQuery .= "FROM (SELECT search_ix_document_id, search_ix_system_id from "._dbprefix_."search_ix_document AS D
                 ".($this->objMetadataFilter != null ? "  LEFT JOIN "._dbprefix_."system ON search_ix_system_id = system_id " : "")."
                     WHERE ";

        $strWhereMust = "1=1 ";

        // metadata filter
        if($this->objMetadataFilter != null ) {
            $this->objMetadataFilter->getQuery($strWhereMust, $arrParameters);
        }

        /* @var $objTerm class_module_search_term */
        foreach($this->arrMustNotOccurs as $objTerm) {
            //$strWhereMust .= "AND NOT EXISTS (select 1 from " . _dbprefix_ . "search_ix_content WHERE search_ix_content_content= ? ". ($objTerm->getStrField()!=null ? " AND search_ix_content_field_name = ? ": "" )  ." AND search_ix_document_id = search_ix_content_document_id) ";
            $strWhereMust .= "AND search_ix_document_id NOT IN (select search_ix_content_document_id from " . _dbprefix_ . "search_ix_content WHERE search_ix_content_content LIKE ? ". ($objTerm->getStrField()!=null ? " AND search_ix_content_field_name = ? ": "")." ) ";
            $arrParameters[] = $objTerm->getStrText()."%";

            if($objTerm->getStrField() != null)
                $arrParameters[] = $objTerm->getStrField();
        }
        /* @var $objTerm class_module_search_term */
        foreach($this->arrMustOccurs as $objTerm) {
            //$strWhereMust .= "AND exists (select 1 from " . _dbprefix_ . "search_ix_content WHERE search_ix_content_content= ? ". ($objTerm->getStrField()!=null ? " AND search_ix_content_field_name = ? ": "" )  ." AND search_ix_document_id = search_ix_content_document_id) ";
            $strWhereMust .= "AND search_ix_document_id IN (select search_ix_content_document_id from " . _dbprefix_ . "search_ix_content WHERE search_ix_content_content LIKE ? ". ($objTerm->getStrField()!=null ? " AND search_ix_content_field_name = ? ": "")." ) ";
            $arrParameters[] = $objTerm->getStrText()."%";

            if($objTerm->getStrField() != null)
                $arrParameters[] = $objTerm->getStrField();
        }

        $strQuery .= $strWhereMust . ") a ";

        $arrMustShouldTerms = array();
        /* @var $objTerm class_module_search_term */
        foreach (array_merge($this->arrMustOccurs, $this->arrShouldOccurs) as $objTerm) {
            $strWhere = " (search_ix_content_content LIKE ? ";
            $arrParameters[] = $objTerm->getStrText()."%";

            if ($objTerm->getStrField() != null) {
                $strWhere .= "AND search_ix_content_field_name = ? ";
                $arrParameters[] = $objTerm->getStrField();
            }
            $strWhere .= ")";
            $arrMustShouldTerms[] = $strWhere;
        }
        $strQuery .= "INNER JOIN (select search_ix_content_document_id, search_ix_content_score from " . _dbprefix_ . "search_ix_content where ". implode(" OR ", $arrMustShouldTerms).") z
                        ON search_ix_document_id = search_ix_content_document_id";
    }



    /**
     * @param class_module_search_metadata_filter $objMetadataFilter
     * @return mixed
     */
    public function setMetadataFilter($objMetadataFilter) {
        $this->objMetadataFilter = $objMetadataFilter;
    }
}