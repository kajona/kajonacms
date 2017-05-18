<?php

namespace Kajona\System\Tests\Benchmarks;

use Kajona\System\System\StringUtil;

/**
 * Class ProcessQueryBench
 *
 * @Revs(10000)
 * @Iterations(1)
 */
class ProcessQueryBench
{
    public function benchIndexOf()
    {
        $strQuery = $this->getQuery();

        $intCount = 1;
        while (StringUtil::indexOf($strQuery, "?") !== false) {
            $intPos = StringUtil::indexOf($strQuery, "?");
            $strQuery = substr($strQuery, 0, $intPos)."$".$intCount++.substr($strQuery, $intPos + 1);
        }
    }

    public function benchStrpos()
    {
        $strQuery = $this->getQuery();

        $intCount = 1;
        while (strpos($strQuery, "?") !== false) {
            $intPos = strpos($strQuery, "?");
            $strQuery = substr($strQuery, 0, $intPos)."$".$intCount++.substr($strQuery, $intPos + 1);
        }
    }

    public function benchRegexp()
    {
        $strQuery = $this->getQuery();

        $strQuery = preg_replace_callback('/\?/', function($strValue){
            static $intI = 0;
            $intI++;
            return '$' . $intI;
        }, $strQuery);
    }

    private function getQuery()
    {
        return <<<SQL
SELECT * FROM foo WHERE foo = ? AND bar = ? AND baz = ? ORDER BY id DESC
SQL;
    }
}
