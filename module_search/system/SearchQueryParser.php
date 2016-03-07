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
 * The query parser analyzes the search-query as entered / passed by the user and creates the matching objects
 * to handle the query.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @author sidler@mulchprod.de
 * @since 4.4
 */
class SearchQueryParser {

    public function safeReplaceCharacter($strString, $strCharacter)
    {
        $intLastPos = 0;
        $arrPositions = array();
        while (($intLastPos = strpos($strString, $strCharacter, $intLastPos))!== false) {
            $arrPositions[] = $intLastPos;
            $intLastPos = $intLastPos + strlen($strCharacter);
        }


        $intReplacements = 0;
        foreach($arrPositions as $intPos) {
            $intPos -= $intReplacements;
            if($intPos > 1 && $strString[$intPos-1] != " ") {
                $strString = substr($strString, 0, $intPos).substr($strString, $intPos+1);
                $intReplacements++;
            }

        }

        return $strString;
    }


    /**
     * @param string $strSearchQuery
     * @return SearchQueryInterface
     */
    public function parseText($strSearchQuery) {


        //replace special characters to avoid conflicts with - and + signs
        $strSearchQuery = $this->safeReplaceCharacter($strSearchQuery, "-");
        $strSearchQuery = $this->safeReplaceCharacter($strSearchQuery, "+");




        $arrHits = array();
        preg_match_all('/(?<must>[+-]?)(?<field>\w{1,}(?<semperator>:)){0,1}(?<term>\w{1,})/u', $strSearchQuery, $arrHits, PREG_SET_ORDER);


        if(count($arrHits) == 1) {

            $objParser = new SearchStandardAnalyzer();
            $objParser->analyze($arrHits[0]['term']);
            $arrResult = array_keys($objParser->getResults());
            if(count($arrResult) == 1) {
                $arrHits[0]['term'] = $arrResult[0];
            }

            $objSearchQuery = new SearchTermQuery(new SearchTerm(str_replace(":", "", $arrHits[0]['term']), substr($arrHits[0]['field'], 0, -1)));
            return $objSearchQuery;
        }

        if(count($arrHits) > 1) {
            $objSearchQuery = new SearchBooleanQuery();
            $arrMusts = array();
            $arrMustNots = array();
            $arrNoOperators = array();

            foreach($arrHits as $arrHit) {

                $objParser = new SearchStandardAnalyzer();
                $objParser->analyze($arrHit['term']);
                $arrResult = array_keys($objParser->getResults());
                if(count($arrResult) == 1) {
                    $arrHit['term'] = $arrResult[0];
                }
                else {
                    continue;
                }

                $objTerm = new SearchTerm(str_replace(":", "", $arrHit['term']), substr($arrHit['field'], 0, -1));
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


            //If all arrays are empty return null -> no search possible then
            if(count($arrMusts) == 0 && count($arrMustNots) == 0 && count($arrNoOperators) == 0) {
                return null;
            }

            /** @var $objTerm SearchTerm */
            foreach($arrMusts as $objTerm) {
                $objSearchQuery->add($objTerm, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_MUST);
            }
            /** @var $objTerm SearchTerm */
            foreach($arrMustNots as $objTerm) {
                $objSearchQuery->add($objTerm, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_MUST_NOT);
            }

            if(count($arrMusts) > 0) {
                foreach($arrNoOperators as $objTerm) {
                    $objSearchQuery->add($objTerm, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_SHOULD);
                }
            }
            elseif(count($arrMustNots) > 0 || (count($arrMusts) == 0 && count($arrMustNots) == 0)) {
                foreach($arrNoOperators as $objTerm) {
                    $objSearchQuery->add($objTerm, SearchBooleanQuery::BOOLEAN_CLAUSE_OCCUR_MUST);
                }

            }

            return $objSearchQuery;
        }


        return null;
    }
}