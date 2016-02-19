<?php

namespace Kajona\Search\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";
use Kajona\System\System\Testbase;

class SearchTest extends Testbase {


    public function testSearchPerformance() {
        /*$objSearchCommon = new class_module_search_commons();

        $objSearchSearch = new class_module_search_search();
        $objSearchSearch->setStrQuery("kajona");

        $arrResults = array();

        $intSumWithIndex = 0;
        $strResultsWith = "";
        for($i = 0; $i < 99; $i++) {
            $time_start = microtime(true);
            $arrResults = $objSearchCommon->doAdminSearch2($objSearchSearch);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            $intSumWithIndex += $time;
            $strResultsWith .= $time.",";
        }
        echo "Suche 100 Durchläufe mit Index: ", sprintf('%f', $intSumWithIndex / 100), " sec./Suche " . count($arrResults) . " Ergebnisse \n";
        //echo $strResultsWith."\n";

        $intSumWithoutIndex = 0;
        $strResultsWithout = "";
        for($i = 0; $i < 99; $i++) {
            $time_start = microtime(true);
            $arrResults = $objSearchCommon->doAdminSearch($objSearchSearch);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            $intSumWithoutIndex += $time;
            $strResultsWithout .= $time.",";
        }
        echo "Suche 100 Durchläufe ohne Index: ", sprintf('%f', $intSumWithoutIndex / 100), " sec./Suche " . count($arrResults) . " Ergebnisse \n";
        //echo $strResultsWithout."\n";*/
    }

    public function testCreateSampleFaq() {
        /* echo "Creating faqs\n";

        for ($i = 1; $i < 150 ; $i++)
           {
            $objFaq1 = new class_module_faqs_faq();

           $objFaq1->setStrQuestion("Was ist Kajona? ".$i);
           $objFaq1->setStrAnswer("Kajona ist ein Open Source Content Management System basierend auf PHP und einer Datenbank. Dank der modularen Bauweise ist Kajona einfach erweiter- und anpassbar.");

            $objFaq1->updateObjectToDb();
            }
         echo "Faq´s created\n";*/
    }



}

