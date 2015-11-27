<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_reflectionPerformanceTest extends class_testbase  {

    public function testMethodInvocation()
    {

        $objAspect = new class_module_system_aspect();
        $strId = generateSystemid();



        echo "Calling by call_user_func\n";
        $arrTestStartDate = gettimeofday();

        for($intI = 0; $intI < 50000; $intI++) {
            call_user_func(array($objAspect, 'setSystemid'), $strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;


        echo $intTimeUsed ." sec\n";



        echo "Calling by string named \n";
        $arrTestStartDate = gettimeofday();;

        for($intI = 0; $intI < 50000; $intI++) {
            $objAspect->{'setSystemid'}($strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsed ." sec\n";



        echo "Calling by reflection...\n";
        $arrTestStartDate = gettimeofday();;
        $objRef = new ReflectionMethod($objAspect, 'setSystemid');
        for($intI = 0; $intI < 50000; $intI++) {
            $objRef->invoke($objAspect, $strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsed ." sec\n";



        echo "Calling directly \n";
        $arrTestStartDate = gettimeofday();;

        for($intI = 0; $intI < 50000; $intI++) {
            $objAspect->setSystemid($strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsed ." sec\n";
    }

}

