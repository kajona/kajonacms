# phpbench
PHPBench is a benchmarking framework for php.

#Install
Just open your command line interface and run `composer install` in folder **_debugging/phpbench**

# Creating a benchmark
All benchmarks must be created withing the specific module folder (e.g.**<module_name>/tests/benchmarks**).
So if you write a benchmark for the system module, please but your benchmark class in the **module_system/tests/benchmarks** folder.
If the module folder does not exist, create it.

## Creating a benchmark class
PHPBench does not require that the benchmark class is aware of the PHPBench library - it does not need to extend a parent class or implement an interface.

Benchmark classes have the following characteristics:

* The class and filename must be the same.
* Class methods that start with **bench** will be executed by the benchrunner and timed.

The following is a simple benchmark class:

```
<?php

 //SomeBenchmark.php
 
 class SomeBenchmark
 {
     public function benchMd5()
     {
         hash('md5', 'Hello World!');
     }
 
     public function benchSha1()
     {
         hash('sha1', 'Hello World!');
     }
 }
```


## Executing a benchmark
After having created you benchmark class open your command line interface and jump to **__debugging/phpbench** folder.
Within this folder execute use the given *phpbench.bat* (or phpbench for unix) to run the *run* command of phpbench (be sure that you have executed `composer install` before running the benchmark).

`phpbench run /folder/to/SomeBenchmark.php --report=default --dump-file=some_benchmark.xml`

This command executes all benchmark methods within the defined benchmark class (so all methods prefixed with *bench*).
The result of the benchmark is being displayed directly in the command line interface.
Furthermore the result of the report is being dumped to the *some_benchmark.xml* file. So at a later point in time you may also view the result again
with the *report* command of phpbench:

`phpbench report --file=some_benchmark.xml --report=default` (for default report)

`phpbench report --file=some_benchmark.xml --report=aggregate` (for aggregate report)

## Execution examples
**Run benchmark and create the dump file only:**  
`phpbench run <module>/tests/benchmarks/SomeBenchmark.php --dump-file=some_benchmark.xml` 

**Run benchmarks and given them a context (useful when running benchmarks for different types of implementation):**  
`phpbench run <module>/tests/benchmarks/CreateObjects.php --context="MySQL"      --dump-file=report_createobjects_mysql.xml"`  
`phpbench run <module>/tests/benchmarks/CreateObjects.php --context="PostgreSQL" --dump-file=report_createobjects_postgresql.xml"`  

**View report results from a dumped file:**
`phpbench report --file=report_createobjects_postgres_1.xml --report=default`  
`phpbench report --file=report_createobjects_postgres_1.xml --report=aggregate`  

## Useful annotation in benchmarks classes
* @Revs  
  * Description: Define number of revolutions per benchmark iteration
  * Example:@Revs(2), @Revs({1,2,4})
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#improving-precision-revolutions]
  
  
* @Iterations  
  * Description: The number of times the benchmark will perform
  * Example:@Iterations(2), @Iterations({1,2,4})
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#verifying-and-improving-stability-iterations]
  
  
* @ParamProviders
  * Description: Parameter sets can be provided to benchmark subjects
  * Example:@ParamProviders({"provideDate"})
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#parameterized-benchmarks]
  
  
* @OutputTimeUnit  
  * Description: Define which time units are shown in the report results (default is microseconds)
  * Example:@OutputTimeUnit("seconds", precision=3)
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#microseconds-to-minutes-time-units]
  
  
* @Warmup 
  * Description: Use the @Warmup annotation to execute any number of revolutions before actually measuring the revolutions time.
  * Example:@Warmup(2)
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#warming-up-getting-ready-for-the-show]
  

* @BeforeMethods 
  * Description: Define number of methods which can be executed before each benchmark subject
  * Example:@BeforeMethods(({"setUp"}))
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#subject-runtime-state-before-and-after]
  
  
* @AfterMethods  
  * Description: Define number of methods which can be executed after each benchmark subject
  * Example:@BeforeMethods(({"tearDown"}))
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#subject-runtime-state-before-and-after]
  
  
* @BeforeClassMethods
  * Description: Define a mtehod which will be executed once before all benchmark methods are executed (e.g. establish an external state. For example, creating or populating a database, creating files, etc.)
  * Example:@BeforeClassMethods({"setupClass"})  
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#benchmark-external-state-before-and-after]
  
  
* @AfterClassMethods
  * Description: Define a mtehod which will be executed once after all benchmark methods were executed (e.g. deleting file, closing database connection)
  * Example:@AfterClassMethods({"teardownClass"})
  * Link:[http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#benchmark-external-state-before-and-after]
    
    


# Report examples and column definition
## Report example aggregate
| benchmark        | subject                    | groups | params                    | revs | its | mem_peak   | best      | mean      | mode      | worst     | stdev    | rstdev | diff    |
|------------------|----------------------------|--------|---------------------------|------|-----|------------|-----------|-----------|-----------|-----------|----------|--------|---------|
| CompareDateBench | benchDate                  |        | {"date":""}               | 1    | 2   | 3,712,008b | 10.000μs  | 10.000μs  | 10.000μs  | 10.000μs  | 0.000μs  | 0.00%  | +18.75% |
| CompareDateBench | benchDate                  |        | {"date":""}               | 2    | 2   | 3,712,008b | 12.500μs  | 12.750μs  | 12.750μs  | 13.000μs  | 0.250μs  | 1.96%  | +36.27% |
| CompareDateBench | benchDate                  |        | {"date":""}               | 4    | 2   | 3,712,008b | 8.000μs   | 8.375μs   | 8.375μs   | 8.750μs   | 0.375μs  | 4.48%  | +2.99%  |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 2   | 3,712,016b | 9.000μs   | 9.500μs   | 9.500μs   | 10.000μs  | 0.500μs  | 5.26%  | +14.47% |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 2   | 3,712,016b | 8.500μs   | 8.750μs   | 8.750μs   | 9.000μs   | 0.250μs  | 2.86%  | +7.14%  |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 2   | 3,712,016b | 8.000μs   | 8.125μs   | 8.125μs   | 8.250μs   | 0.125μs  | 1.54%  | 0.00%   |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 2   | 3,711,976b | 9.000μs   | 9.500μs   | 9.500μs   | 10.000μs  | 0.500μs  | 5.26%  | +14.47% |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 2   | 3,711,976b | 9.000μs   | 9.000μs   | 9.000μs   | 9.000μs   | 0.000μs  | 0.00%  | +9.72%  |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 2   | 3,711,976b | 8.250μs   | 8.500μs   | 8.500μs   | 8.750μs   | 0.250μs  | 2.94%  | +4.41%  |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 1    | 3   | 3,592,072b | 26.000μs  | 33.000μs  | 28.157μs  | 45.000μs  | 8.524μs  | 25.83% | +75.38% |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 2    | 3   | 3,592,072b | 15.000μs  | 15.833μs  | 15.399μs  | 17.000μs  | 0.850μs  | 5.37%  | +48.68% |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 4    | 3   | 3,592,072b | 10.000μs  | 10.083μs  | 10.015μs  | 10.250μs  | 0.118μs  | 1.17%  | +19.42% |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 1    | 3   | 3,592,080b | 26.000μs  | 27.667μs  | 28.207μs  | 29.000μs  | 1.247μs  | 4.51%  | +70.63% |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 2    | 3   | 3,592,080b | 16.500μs  | 16.667μs  | 16.530μs  | 17.000μs  | 0.236μs  | 1.41%  | +51.25% |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 4    | 3   | 3,592,080b | 10.000μs  | 10.250μs  | 10.250μs  | 10.500μs  | 0.204μs  | 1.99%  | +20.73% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 1    | 3   | 3,712,024b | 694.000μs | 701.667μs | 695.395μs | 717.000μs | 10.842μs | 1.55%  | +98.84% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 2    | 3   | 3,712,024b | 354.000μs | 370.667μs | 361.066μs | 395.500μs | 17.899μs | 4.83%  | +97.81% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 4    | 3   | 3,712,024b | 186.000μs | 195.917μs | 188.880μs | 213.250μs | 12.299μs | 6.28%  | +95.85% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 3   | 3,712,032b | 692.000μs | 723.667μs | 733.941μs | 749.000μs | 23.697μs | 3.27%  | +98.88% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 3   | 3,712,032b | 354.000μs | 363.500μs | 366.260μs | 371.500μs | 7.223μs  | 1.99%  | +97.76% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 3   | 3,712,032b | 183.500μs | 185.750μs | 186.514μs | 187.500μs | 1.671μs  | 0.90%  | +95.63% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 3   | 3,711,992b | 725.000μs | 732.000μs | 728.227μs | 742.000μs | 7.257μs  | 0.99%  | +98.89% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 3   | 3,711,992b | 362.000μs | 368.500μs | 365.185μs | 377.500μs | 6.570μs  | 1.78%  | +97.80% |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 3   | 3,711,992b | 185.500μs | 201.333μs | 189.308μs | 230.750μs | 20.821μs | 10.34% | +95.96% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 1    | 3   | 3,592,104b | 31.000μs  | 31.333μs  | 31.061μs  | 32.000μs  | 0.471μs  | 1.50%  | +74.07% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 2    | 3   | 3,592,104b | 17.500μs  | 18.500μs  | 18.500μs  | 19.500μs  | 0.816μs  | 4.41%  | +56.08% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 4    | 3   | 3,592,104b | 11.500μs  | 15.083μs  | 12.363μs  | 21.750μs  | 4.718μs  | 31.28% | +46.13% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 1    | 3   | 3,592,112b | 31.000μs  | 31.000μs  | 31.000μs  | 31.000μs  | 0.000μs  | 0.00%  | +73.79% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 2    | 3   | 3,592,112b | 18.500μs  | 19.500μs  | 19.502μs  | 20.500μs  | 0.816μs  | 4.19%  | +58.33% |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 4    | 3   | 3,592,112b | 13.000μs  | 13.083μs  | 13.015μs  | 13.250μs  | 0.118μs  | 0.90%  | +37.90% |




## Report example default
| benchmark        | subject                    | groups | params                    | revs | iter | mem_peak   | time_rev  | comp_z_value | comp_deviation |
|------------------|----------------------------|--------|---------------------------|------|------|------------|-----------|--------------|----------------|
| CompareDateBench | benchDate                  |        | {"date":""}               | 1    | 0    | 3,712,008b | 10.000μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDate                  |        | {"date":""}               | 1    | 1    | 3,712,008b | 10.000μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDate                  |        | {"date":""}               | 2    | 0    | 3,712,008b | 13.000μs  | +1.00σ       | +1.96%         |
| CompareDateBench | benchDate                  |        | {"date":""}               | 2    | 1    | 3,712,008b | 12.500μs  | -1σ          | -1.96%         |
| CompareDateBench | benchDate                  |        | {"date":""}               | 4    | 0    | 3,712,008b | 8.750μs   | +1.00σ       | +4.48%         |
| CompareDateBench | benchDate                  |        | {"date":""}               | 4    | 1    | 3,712,008b | 8.000μs   | -1σ          | -4.48%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 0    | 3,712,016b | 9.000μs   | -1σ          | -5.26%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 1    | 3,712,016b | 10.000μs  | +1.00σ       | +5.26%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 0    | 3,712,016b | 9.000μs   | +1.00σ       | +2.86%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 1    | 3,712,016b | 8.500μs   | -1σ          | -2.86%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 0    | 3,712,016b | 8.000μs   | -1σ          | -1.54%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 1    | 3,712,016b | 8.250μs   | +1.00σ       | +1.54%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 0    | 3,711,976b | 10.000μs  | +1.00σ       | +5.26%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 1    | 1    | 3,711,976b | 9.000μs   | -1σ          | -5.26%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 0    | 3,711,976b | 9.000μs   | 0.00σ        | 0.00%          |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 2    | 1    | 3,711,976b | 9.000μs   | 0.00σ        | 0.00%          |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 0    | 3,711,976b | 8.250μs   | -1σ          | -2.94%         |
| CompareDateBench | benchDate                  |        | {"date":"20161002000000"} | 4    | 1    | 3,711,976b | 8.750μs   | +1.00σ       | +2.94%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 1    | 0    | 3,592,072b | 45.000μs  | +1.41σ       | +36.36%        |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 1    | 1    | 3,592,072b | 28.000μs  | -0.59σ       | -15.15%        |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 1    | 2    | 3,592,072b | 26.000μs  | -0.82σ       | -21.21%        |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 2    | 0    | 3,592,072b | 17.000μs  | +1.37σ       | +7.37%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 2    | 1    | 3,592,072b | 15.000μs  | -0.98σ       | -5.26%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 2    | 2    | 3,592,072b | 15.500μs  | -0.39σ       | -2.11%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 4    | 0    | 3,592,072b | 10.250μs  | +1.41σ       | +1.65%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 4    | 1    | 3,592,072b | 10.000μs  | -0.71σ       | -0.83%         |
| CompareDateBench | benchDateTime              |        | {"date":""}               | 4    | 2    | 3,592,072b | 10.000μs  | -0.71σ       | -0.83%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 1    | 0    | 3,592,080b | 26.000μs  | -1.34σ       | -6.02%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 1    | 1    | 3,592,080b | 29.000μs  | +1.07σ       | +4.82%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 1    | 2    | 3,592,080b | 28.000μs  | +0.27σ       | +1.20%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 2    | 0    | 3,592,080b | 16.500μs  | -0.71σ       | -1%            |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 2    | 1    | 3,592,080b | 17.000μs  | +1.41σ       | +2.00%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 2    | 2    | 3,592,080b | 16.500μs  | -0.71σ       | -1%            |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 4    | 0    | 3,592,080b | 10.250μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 4    | 1    | 3,592,080b | 10.500μs  | +1.22σ       | +2.44%         |
| CompareDateBench | benchDateTime              |        | {"date":"2000-01-01"}     | 4    | 2    | 3,592,080b | 10.000μs  | -1.22σ       | -2.44%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 1    | 0    | 3,712,024b | 694.000μs | -0.71σ       | -1.09%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 1    | 1    | 3,712,024b | 717.000μs | +1.41σ       | +2.19%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 1    | 2    | 3,712,024b | 694.000μs | -0.71σ       | -1.09%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 2    | 0    | 3,712,024b | 362.500μs | -0.46σ       | -2.2%          |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 2    | 1    | 3,712,024b | 395.500μs | +1.39σ       | +6.70%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 2    | 2    | 3,712,024b | 354.000μs | -0.93σ       | -4.5%          |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 4    | 0    | 3,712,024b | 213.250μs | +1.41σ       | +8.85%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 4    | 1    | 3,712,024b | 188.500μs | -0.6σ        | -3.79%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":""}               | 4    | 2    | 3,712,024b | 186.000μs | -0.81σ       | -5.06%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 0    | 3,712,032b | 749.000μs | +1.07σ       | +3.50%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 1    | 3,712,032b | 692.000μs | -1.34σ       | -4.38%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 2    | 3,712,032b | 730.000μs | +0.27σ       | +0.88%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 0    | 3,712,032b | 354.000μs | -1.32σ       | -2.61%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 1    | 3,712,032b | 371.500μs | +1.11σ       | +2.20%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 2    | 3,712,032b | 365.000μs | +0.21σ       | +0.41%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 0    | 3,712,032b | 183.500μs | -1.35σ       | -1.21%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 1    | 3,712,032b | 186.250μs | +0.30σ       | +0.27%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 2    | 3,712,032b | 187.500μs | +1.05σ       | +0.94%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 0    | 3,711,992b | 742.000μs | +1.38σ       | +1.37%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 1    | 3,711,992b | 729.000μs | -0.41σ       | -0.41%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 1    | 2    | 3,711,992b | 725.000μs | -0.96σ       | -0.96%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 0    | 3,711,992b | 377.500μs | +1.37σ       | +2.44%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 1    | 3,711,992b | 362.000μs | -0.99σ       | -1.76%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 2    | 2    | 3,711,992b | 366.000μs | -0.38σ       | -0.68%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 0    | 3,711,992b | 230.750μs | +1.41σ       | +14.61%        |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 1    | 3,711,992b | 187.750μs | -0.65σ       | -6.75%         |
| CompareDateBench | benchDate_GetTimeStamp     |        | {"date":"20161002000000"} | 4    | 2    | 3,711,992b | 185.500μs | -0.76σ       | -7.86%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 1    | 0    | 3,592,104b | 32.000μs  | +1.41σ       | +2.13%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 1    | 1    | 3,592,104b | 31.000μs  | -0.71σ       | -1.06%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 1    | 2    | 3,592,104b | 31.000μs  | -0.71σ       | -1.06%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 2    | 0    | 3,592,104b | 18.500μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 2    | 1    | 3,592,104b | 19.500μs  | +1.22σ       | +5.41%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 2    | 2    | 3,592,104b | 17.500μs  | -1.22σ       | -5.41%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 4    | 0    | 3,592,104b | 21.750μs  | +1.41σ       | +44.20%        |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 4    | 1    | 3,592,104b | 11.500μs  | -0.76σ       | -23.76%        |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":""}               | 4    | 2    | 3,592,104b | 12.000μs  | -0.65σ       | -20.44%        |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 1    | 0    | 3,592,112b | 31.000μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 1    | 1    | 3,592,112b | 31.000μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 1    | 2    | 3,592,112b | 31.000μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 2    | 0    | 3,592,112b | 18.500μs  | -1.22σ       | -5.13%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 2    | 1    | 3,592,112b | 20.500μs  | +1.22σ       | +5.13%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 2    | 2    | 3,592,112b | 19.500μs  | 0.00σ        | 0.00%          |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 4    | 0    | 3,592,112b | 13.000μs  | -0.71σ       | -0.64%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 4    | 1    | 3,592,112b | 13.250μs  | +1.41σ       | +1.27%         |
| CompareDateBench | benchDateTime_GetTimeStamp |        | {"date":"2000-01-01"}     | 4    | 2    | 3,592,112b | 13.000μs  | -0.71σ       | -0.64%         |


## Column explanation reports (default and aggregate report)
* **benchmark:** Short name of the benchmark class (i.e. no namespace).
* **subject:** Name of the subject method.
* **groups:** Comma separated list of groups.
* **params:** Parameters (represented as JSON) which are passed to the subject.
* **revs:** Number of revolutions. (How often the subject is executed per iteration)
* **its:** Number of iterations. (The number of times the benchmark will perform)
* **mem_peak:** (mean) Peak memory used by each iteration as retrieved by `memory_get_peak_usage` (http://php.net/manual/en/function.memory-get-peak-usage.php).

## Additional columns aggregate report
* **best:** Minimum time of all iterations in variant.
* **mean:** Mean time taken by all iterations in variant.
* **mode:** Mode of all iterations in variant. (https://en.wikipedia.org/wiki/Mode_(statistics))
* **worst:** Maximum time of all iterations in variant
* **stddev:** Standard deviation
* **rstddev:** The relative standard deviation. (https://en.wikipedia.org/wiki/Coefficient_of_variation)
* **diff:** difference between the iterations (compared to mean)

## Additional columns default report
* **time_rev:** Time per revolution (time_net / nb revs).
* **comp_z_value:** The number of standard deviations away from the mean of the iteration set (the variant).
* **comp_deviation:** ???no documentation found??? (19.01.2017)

# Detailed phpbench documentation
For a detailed documentation of phpbench please vistit [http://phpbench.readthedocs.io/]
