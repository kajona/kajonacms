<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * The query parser analyzes the search-query as entered / passed by the user and creates the matching objects
 * to handle the query.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 4.4
 */
class class_module_search_query_parser {

    /**
     * @param string $strSearchQuery
     * @return interface_search_query
     */
    public function parseText($strSearchQuery) {

        $arrHits = array();
        preg_match_all('/(?<must>[+-]?)(?<field>\w{1,}(?<semperator>:)){0,1}(?<term>\w{1,})/u', $strSearchQuery, $arrHits, PREG_SET_ORDER);


        if(count($arrHits) == 1) {

            $objParser = new class_module_search_standard_analyzer();
            $objParser->analyze($arrHits[0]['term']);
            $arrResult = array_keys($objParser->getResults());
            if(count($arrResult) == 1) {
                $arrHits[0]['term'] = $arrResult[0];
            }

            $objSearchQuery = new class_module_search_term_query(new class_module_search_term(str_replace(":", "", $arrHits[0]['term']), substr($arrHits[0]['field'], 0, -1)));
            return $objSearchQuery;
        }

        if(count($arrHits) > 1) {
            $objSearchQuery = new class_module_search_boolean_query();
            $arrMusts = array();
            $arrMustNots = array();
            $arrNoOperators = array();

            foreach($arrHits as $arrHit) {

                $objParser = new class_module_search_standard_analyzer();
                $objParser->analyze($arrHit['term']);
                $arrResult = array_keys($objParser->getResults());
                if(count($arrResult) == 1) {
                    $arrHit['term'] = $arrResult[0];
                }
                else {
                    continue;
                }

                $objTerm = new class_module_search_term(str_replace(":", "", $arrHit['term']), substr($arrHit['field'], 0, -1));
                switch($arrHit['must']) {
                    case "+":
                        $arrMusts[] = $objTerm;
                        break;
                    case "-":
                        $arrMustNots[] = $objTerm;
                        break;
                    case "":
                        $arrNoOperators[] = $objTerm;
                        break;
                }
            }

            /** @var $objTerm class_module_search_term */
            foreach($arrMusts as $objTerm) {
                $objSearchQuery->add($objTerm, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_MUST);
            }
            /** @var $objTerm class_module_search_term */
            foreach($arrMustNots as $objTerm) {
                $objSearchQuery->add($objTerm, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_MUST_NOT);
            }

            if(count($arrMusts) > 0) {
                foreach($arrNoOperators as $objTerm) {
                    $objSearchQuery->add($objTerm, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_SHOULD);
                }
            }
            elseif(count($arrMustNots) > 0 || (count($arrMusts) == 0 && count($arrMustNots) == 0)) {
                foreach($arrNoOperators as $objTerm) {
                    $objSearchQuery->add($objTerm, class_module_search_boolean_query::BOOLEAN_CLAUSE_OCCUR_MUST);
                }

            }

            return $objSearchQuery;
        }


        return null;
    }
}