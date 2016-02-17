<?php

namespace Kajona\System\Tests;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Testbase;

class FileHashPerformanceTest extends Testbase  {

    public function testFileHashes()
    {

        $objFilesystem = new Filesystem();
        $objFilesystem->openFilePointer("/project/temp/hashes.temp");

        for($intI = 0; $intI <= 20000; $intI++) {
            $objFilesystem->writeToFile("Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum ");
        }

        $objFilesystem->closeFilePointer();


        $intRuns = 50;


        echo "Calling by sha1_file\n";
        $arrTestStartDate = gettimeofday();

        for($intI = 0; $intI < $intRuns; $intI++) {
            sha1_file(_realpath_."/project/temp/hashes.temp");
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedUserFunc = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedUserFunc ." sec\n";



        echo "Calling by md5_file\n";
        $arrTestStartDate = gettimeofday();

        for($intI = 0; $intI < $intRuns; $intI++) {
            md5_file(_realpath_."/project/temp/hashes.temp");
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedUserFunc = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedUserFunc ." sec\n";


        echo "Calling by filemtime\n";
        $arrTestStartDate = gettimeofday();

        for($intI = 0; $intI < $intRuns; $intI++) {
            filemtime(_realpath_."/project/temp/hashes.temp");
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedUserFunc = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedUserFunc ." sec\n";


    }

}

