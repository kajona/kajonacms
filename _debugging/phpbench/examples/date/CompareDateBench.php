<?php

namespace AGP\_Phpbench\Benchmarks\Examples\Date;

use DateTime;
use Kajona\System\System\Date;

/**
 * Benchmark Date vs DateTime
 *
 * Class CompareDateBench
 */
class CompareDateBench
{
    /**
     * @Iterations(2)
     * @Revs({1, 2, 4})
     * @ParamProviders({"provideDate"})
     * @Warmup(2)
     *
     */
    public function benchDate($arrParams)
    {
        new Date($arrParams["date"]);
    }

    /**
     * @Revs({1, 2, 4})
     * @Iterations(3)
     * @ParamProviders({"provideDateTime"})
     * @Warmup(2)
     */
    public function benchDateTime($arrParams)
    {
        new DateTime($arrParams["date"]);
    }

    /**
     * @Revs({1, 2, 4})
     * @Iterations(3)
     * @ParamProviders({"provideDate"})
     * @Warmup(2)
     */
    public function benchDate_GetTimeStamp($arrParams)
    {
        $objDate = new Date($arrParams["date"]);
        $objDate->getLongTimestamp();
    }



    /**
     * @Revs({1, 2, 4})
     * @Iterations(3)
     * @ParamProviders({"provideDateTime"})
     * @Warmup(2)
     */
    public function benchDateTime_GetTimeStamp($arrParams)
    {
        $objDate = new DateTime($arrParams["date"]);
        $objDate->getTimestamp();
    }


    public function provideDate()
    {
        return array(
            array("date" => ""),
            array("date" => "20161002000000"),
            array("date" => 20161002000000),
        );
    }

    public function provideDateTime()
    {
        return array(
            array("date" => ""),
            array("date" => "2000-01-01"),
        );
    }

}