<?php

namespace Kajona\System\Tests;

use Kajona\System\System\Link;

class LinkTest extends Testbase
{
    const STR_EQ_ENC = "%3D";//     urlencode("=")
    const STR_PERC_ENC = "%25";//   urlencode("%")
    const STR_BACKS_ENC = "%5C";//  urlencode("\\")
    const STR_AND_ENC = "%26";//  urlencode("&")
    const STR_HASH_ENC = "%23";//  urlencode("#")

    /**
     *  Test conversion of an array to param url string
     *
     * @dataProvider dataProviderSanitizeUrlParams
     */
    public function testSanitizeUrlParams($arrInput, $strExpectedValue)
    {
        //Check value
        $strValue = Link::sanitizeUrlParams($arrInput);
        $this->assertSame($strExpectedValue, $strValue);

        //Now check if method is called twice or three times the values are still the same
        $strValue1 = Link::sanitizeUrlParams($arrInput);
        $strValue2 = Link::sanitizeUrlParams($strValue1);
        $strValue3 = Link::sanitizeUrlParams($strValue2);

        $this->assertSame($strExpectedValue, $strValue1);
        $this->assertSame($strExpectedValue, $strValue2);
        $this->assertSame($strExpectedValue, $strValue3);
        $this->assertSame($strValue1, $strValue2);
        $this->assertSame($strValue1, $strValue3);
        $this->assertSame($strValue2, $strValue3);
    }

    /**
     *  Test conversion of an array to param url string using http_build_query
     *
     * @dataProvider dataProviderdataProviderSanitizeUrlParamsHttpBuildQuery
     */
    public function testSanitizeUrlParamsHttpBuildQuery($arrInput, $strExpectedValue)
    {
        //first use http_build_query
        $arrInput = http_build_query($arrInput);

        $strValue = Link::sanitizeUrlParams($arrInput);
        $this->assertSame($strExpectedValue, $strValue);

        //Now check if method is called twice or three times the values are still the same
        $strValue1 = Link::sanitizeUrlParams($arrInput);
        $strValue2 = Link::sanitizeUrlParams($strValue1);
        $strValue3 = Link::sanitizeUrlParams($strValue2);

        $this->assertSame($strExpectedValue, $strValue1);
        $this->assertSame($strExpectedValue, $strValue2);
        $this->assertSame($strExpectedValue, $strValue3);
        $this->assertSame($strValue1, $strValue2);
        $this->assertSame($strValue1, $strValue3);
        $this->assertSame($strValue2, $strValue3);
    }

    public function dataProviderSanitizeUrlParams()
    {
        $strSystemId = generateSystemid();

        $strEq = self::STR_EQ_ENC;
        $strPerc = self::STR_PERC_ENC;
        $strBacksl = self::STR_BACKS_ENC;
        $strHashl = self::STR_HASH_ENC;
        $strAnd = self::STR_AND_ENC;

        //input value -> expected value
        return array(
            //Input string
            array("",                                               ""),
            array("a=true&b=false",                                 "a=true&b=false"),
            array("a=1&b=2",                                        "a=1&b=2"),
            array("a=1&b=Kajona\\System\\System",                   "a=1&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array("a=%2%&b=Kajona\\System\\System",                 "a={$strPerc}2{$strPerc}&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array("0=0&1=1&2=a#b",                                  "0=0&1=1&2=a{$strHashl}b"),

            //Input string - check if systemid param is removed
            array("a=true&b=false&systemid=123",                    "a=true&b=false"),
            array("a=true&b=false&systemid={$strSystemId}",         "a=true&b=false"),

            //Input string - cannot handle "=" and "&" correctly
            array("a=&2&b=Kajona\\System\\System=&b&=",             "a=&="),
            array("0=0&1=1&2=a=b",                                  "0=0&1=1"),
            array("0=0&1=1&2=a&b",                                  "0=0&1=1&2=a"),


            //Input array
            array(array(),                                                      ""),
            array(array("a" => true, "b" => false),                             "a=true&b=false"),
            array(array("a" => "true", "b" => "false"),                         "a=true&b=false"),

            array(array("a" => 1, "b" => 2),                                    "a=1&b=2"),
            array(array("a" => "1", "b" => "2"),                                "a=1&b=2"),

            array(array("a" => "1", "b" => "Kajona\\System\\System"),           "a=1&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array(array("a" => "%2%", "b" => "Kajona\\System\\System"),         "a={$strPerc}2{$strPerc}&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array(array("a" => "&2&", "b" => "Kajona\\System\\System=&b&="),    "a={$strAnd}2{$strAnd}&b=Kajona{$strBacksl}System{$strBacksl}System{$strEq}{$strAnd}b{$strAnd}{$strEq}"),
            array(array("a" => "#2#", "b" => "Kajona\\System\\System=&b&="),    "a={$strHashl}2{$strHashl}&b=Kajona{$strBacksl}System{$strBacksl}System{$strEq}{$strAnd}b{$strAnd}{$strEq}"),

            array(array("0" => "0", "1"=>"1", "2" => "a#b"),                    "0=0&1=1&2=a{$strHashl}b"),
            array(array("0" => "0", "1"=>"1", "2" => "a=b"),                    "0=0&1=1&2=a{$strEq}b"),
            array(array("0" => "0", "1"=>"1", "2" => "a&b"),                    "0=0&1=1&2=a{$strAnd}b"),

            //Input array - check if systemid param is removed
            array(array("a" => "true", "b" => "false", "systemid" => "123"),                 "a=true&b=false"),
            array(array("a" => "true", "b" => "false", "systemid" => "{$strSystemId}"),      "a=true&b=false"),
        );
    }

    public function dataProviderdataProviderSanitizeUrlParamsHttpBuildQuery()
    {
        $strSystemId = generateSystemid();

        $strEq = self::STR_EQ_ENC;
        $strPerc = self::STR_PERC_ENC;
        $strBacksl = self::STR_BACKS_ENC;
        $strHashl = self::STR_HASH_ENC;
        $strAnd = self::STR_AND_ENC;

        //input value -> expected value
        return array(
            //Input array
            array(array(),                                                      ""),
            array(array("a" => 1, "b" => 2),                                    "a=1&b=2"),
            array(array("a" => "1", "b" => "2"),                                "a=1&b=2"),

            array(array("a" => "1", "b" => "Kajona\\System\\System"),           "a=1&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array(array("a" => "%2%", "b" => "Kajona\\System\\System"),         "a={$strPerc}2{$strPerc}&b=Kajona{$strBacksl}System{$strBacksl}System"),
            array(array("a" => "&2&", "b" => "Kajona\\System\\System=&b&="),    "a={$strAnd}2{$strAnd}&b=Kajona{$strBacksl}System{$strBacksl}System{$strEq}{$strAnd}b{$strAnd}{$strEq}"),
            array(array("a" => "#2#", "b" => "Kajona\\System\\System=&b&="),    "a={$strHashl}2{$strHashl}&b=Kajona{$strBacksl}System{$strBacksl}System{$strEq}{$strAnd}b{$strAnd}{$strEq}"),

            array(array("0" => "0", "1"=>"1", "2" => "a#b"),                    "0=0&1=1&2=a{$strHashl}b"),
            array(array("0" => "0", "1"=>"1", "2" => "a=b"),                    "0=0&1=1&2=a{$strEq}b"),
            array(array("0" => "0", "1"=>"1", "2" => "a&b"),                    "0=0&1=1&2=a{$strAnd}b"),

            //Input array - check if systemid param is removed
            array(array("a" => "true", "b" => "false", "systemid" => "123"),                 "a=true&b=false"),
            array(array("a" => "true", "b" => "false", "systemid" => "{$strSystemId}"),      "a=true&b=false"),
        );
    }
}

