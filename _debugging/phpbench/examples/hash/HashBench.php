<?php

namespace AGP\_Phpbench\Benchmarks\Examples\Hash;

/**
 * Class HashBench
 */
class HashBench
{
    /**
     * @Revs(2)
     * @Iterations(1)
     */
    public function benchMd5()
    {
        hash('md5', 'Hello World!');
    }

    /**
     * @Revs(2)
     * @Iterations(1)
     */
    public function benchSha1()
    {
        hash('sha1', 'Hello World!');
    }
}