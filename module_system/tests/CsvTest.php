<?php

namespace Kajona\System\Tests;
use Kajona\System\System\Csv;
use Kajona\System\System\Testbase;

class CsvTest extends Testbase  {



    public function test() {

        echo "\ttesting class_csv...\n";

        //test code
        $strFile = "/files/public/testCsv.csv";

        $arrValues = array(
                            array("v11", "v21", "v31"),
                            array("v12", "v22", "v32"),
                            array("v13", "v23", "v33"),
                            array("v14", "v24", "v34"),
                            array("v15", "v25", "v35")
                            );

        $objCsv = new Csv();
        $objCsv->setArrData($arrValues);
        $objCsv->setArrMapping(array(0 => "c1", 1 => "c2", 2 => "c3"));
        //write to file
        $objCsv->setStrFilename($strFile);
        $this->assertTrue($objCsv->writeArrayToFile(), __FILE__." checkCsvWriteArrayToFile");
        //read from file
        $objCsv = new Csv();
        $objCsv->setArrMapping(array("c1" => 0, "c2" => 1,  "c3" => 2));
        $objCsv->setStrFilename($strFile);
        $this->assertTrue($objCsv->createArrayFromFile(), __FILE__." checkCsvCreateArrayFromFileReader");
        $arrValuesFromCsv = $objCsv->getArrData();
        $this->assertEquals(count(array_diff($arrValues[0], $arrValuesFromCsv[0])), 0, __FILE__." checkCsvCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[1], $arrValuesFromCsv[1])), 0, __FILE__." checkCsvCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[2], $arrValuesFromCsv[2])), 0, __FILE__." checkCsvCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[3], $arrValuesFromCsv[3])), 0, __FILE__." checkCsvCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[4], $arrValuesFromCsv[4])), 0, __FILE__." checkCsvCreateArrayFromFile");

        //test with set encloser
        $objCsv = new Csv();
        $objCsv->setArrData($arrValues);
        $objCsv->setTextEncloser("'");
        $objCsv->setArrMapping(array(0 => "c1", 1 => "c2", 2 => "c3"));
        //write to file
        $objCsv->setStrFilename($strFile);
        $this->assertTrue($objCsv->writeArrayToFile(), __FILE__." checkCsvEncloserWriteArrayToFile");
        //read from file
        $objCsv = new Csv();
        $objCsv->setArrMapping(array("c1" => 0, "c2" => 1,  "c3" => 2));
        $objCsv->setTextEncloser("'");
        $objCsv->setStrFilename($strFile);
        $this->assertTrue($objCsv->createArrayFromFile(), __FILE__." checkCsvEncloserCreateArrayFromFileReader");
        $arrValuesFromCsv = $objCsv->getArrData();
        $this->assertEquals(count(array_diff($arrValues[0], $arrValuesFromCsv[0])), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[1], $arrValuesFromCsv[1])), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[2], $arrValuesFromCsv[2])), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[3], $arrValuesFromCsv[3])), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");
        $this->assertEquals(count(array_diff($arrValues[4], $arrValuesFromCsv[4])), 0, __FILE__." checkCsvEncloserCreateArrayFromFile");

        echo "\tsaved generated CSV file to <a href=\""._webpath_.$strFile."\">"._webpath_.$strFile."</a>\n";

    }

}

