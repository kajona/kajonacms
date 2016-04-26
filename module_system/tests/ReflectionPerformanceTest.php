<?php

namespace Kajona\System\System;

use Kajona\System\Tests\Testbase;
use ReflectionMethod;

class ReflectionPerformanceTest extends Testbase  {

    public function testMethodInvocation()
    {

        $objAspect = new SystemAspect();
        $strId = generateSystemid();



        echo "Calling by call_user_func\n";
        $arrTestStartDate = gettimeofday();

        for($intI = 0; $intI < 50000; $intI++) {
            call_user_func(array($objAspect, 'setSystemid'), $strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedUserFunc = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;


        echo $intTimeUsedUserFunc ." sec\n";



        echo "Calling by string named \n";
        $arrTestStartDate = gettimeofday();;

        for($intI = 0; $intI < 50000; $intI++) {
            $objAspect->{'setSystemid'}($strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedString = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedString ." sec\n";



        echo "Calling by reflection...\n";
        $arrTestStartDate = gettimeofday();;
        $objRef = new ReflectionMethod($objAspect, 'setSystemid');
        for($intI = 0; $intI < 50000; $intI++) {
            $objRef->invoke($objAspect, $strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedRef = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedRef ." sec\n";



        echo "Calling directly \n";
        $arrTestStartDate = gettimeofday();;

        for($intI = 0; $intI < 50000; $intI++) {
            $objAspect->setSystemid($strId);
        }

        $arrTimestampEnde = gettimeofday();
        $intTimeUsedDirect = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($arrTestStartDate['sec'] * 1000000 + $arrTestStartDate['usec'])) / 1000000;

        echo $intTimeUsedDirect ." sec\n";


//        $this->assertTrue($intTimeUsedUserFunc > $intTimeUsedString);
//        $this->assertTrue($intTimeUsedUserFunc > $intTimeUsedRef);
    }

}

