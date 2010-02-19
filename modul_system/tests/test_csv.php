<?php

class class_test_csv implements interface_testable {



    public function test() {

        echo "\ttesting class_csv...\n";

        //test code
        $strFile = "/portal/downloads/public/testCsv.csv";

        $arrCsvHeader = array("c1", "c2", "c3");
        $arrValues = array(
                            array("v11", "v21", "v31"),
                            array("v12", "v22", "v32"),
                            array("v13", "v23", "v33"),
                            array("v14", "v24", "v34"),
                            array("v15", "v25", "v35")
                            );

        $objCsv = new class_csv();
        $objCsv->setArrData($arrValues);
        $objCsv->setArrMapping(array(0 => "c1", 1 => "c2", 2 => "c3"));
        //write to file
        $objCsv->setStrFilename($strFile);
        class_assertions::assertTrue($objCsv->writeArrayToFile(), __FILE__." checkCsvWriteArrayToFile");
        //read from file
        $objCsv = new class_csv();
        $objCsv->setArrMapping(array("c1" => 0, "c2" => 1,  "c3" => 2));
        $objCsv->setStrFilename($strFile);
        class_assertions::assertTrue($objCsv->createArrayFromFile(), __FILE__." checkCsvCreateArrayFromFileReader");
        $arrValuesFromCsv = $objCsv->getArrData();
        class_assertions::assertEqual(count(array_diff($arrValues, $arrValuesFromCsv)), 0, __FILE__." checkCsvCreateArrayFromFile");

        //test with set encloser
        $objCsv = new class_csv();
        $objCsv->setArrData($arrValues);
        $objCsv->setTextEncloser("'");
        $objCsv->setArrMapping(array(0 => "c1", 1 => "c2", 2 => "c3"));
        //write to file
        $objCsv->setStrFilename($strFile);
        class_assertions::assertTrue($objCsv->writeArrayToFile(), __FILE__." checkCsvEncloserWriteArrayToFile");
        //read from file
        $objCsv = new class_csv();
        $objCsv->setArrMapping(array("c1" => 0, "c2" => 1,  "c3" => 2));
        $objCsv->setTextEncloser("'");
        $objCsv->setStrFilename($strFile);
        class_assertions::assertTrue($objCsv->createArrayFromFile(), __FILE__." checkCsvEncloserCreateArrayFromFileReader");
        $arrValuesFromCsv = $objCsv->getArrData();
        class_assertions::assertEqual(count(array_diff($arrValues, $arrValuesFromCsv)), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");

        echo "\tsaved generated CSV file to <a href=\""._webpath_.$strFile.">"._webpath_.$strFile."</a>\n";

    }

}

?>