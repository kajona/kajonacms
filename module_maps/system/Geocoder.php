<?php
/*"******************************************************************************************************
 *   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
 *   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
 *       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
 *-------------------------------------------------------------------------------------------------------*
 *   $Id$                                      *
 ********************************************************************************************************/

namespace Kajona\Maps\System;

use Kajona\System\System\Exception;
use Kajona\System\System\Remoteloader;
use SimpleXMLElement;


/**
 * Class to receive geo coordinates of local addresses using external services like Google Maps.
 * Please set your API hosts and keys in the service-specific function, e.g. lookupAddressUsingGoogle()
 * Choose which remote service you want to use by setting the ApiId like:
 *     $objGeocoder = new Geocoder(0);    (0 = Google Maps; 1 = Yahoo! Maps)
 * ATTENTION: Please respect the terms of use of the remote services! E.g. Google only allows the use
 * in conjunction with displaying the results on a Google map.
 *
 * @author jschroeter
 */
class Geocoder
{
    /**
     * 0 = Google Maps
     * 1 = Yahoo! Maps
     *
     * @var int
     */
    private $intApiId = 1;

    private $strResponseRaw = "";
    private $strStreet = "";
    private $strPostalCode = "";
    private $strCity = "";
    private $strAdministrativeArea = "";
    private $strSubAdministrativeArea = "";
    private $strCountryCode = "";
    private $floatLatitude = 0.0;
    private $floatLongitude = 0.0;
    private $intAccuracy = 0;

    /**
     * Constructor
     *
     * @param int $intApiId
     */
    public function __construct($intApiId = 0)
    {
        $this->intApiId = $intApiId;
    }

    /**
     * Does the remote address lookup.
     * Returns true if an address was found. Keep in mind that it could be an wrong address,
     * so in some cases it makes sense to compare e.g the requested with the received postal
     * code or country code.
     *
     * @param string $strStreet
     * @param string $strPostalCode
     * @param string $strCity
     * @param string $strCountry
     *
     * @return bool
     */
    public function lookupAddress($strStreet = "", $strPostalCode = "", $strCity = "", $strCountry = "")
    {
        $bitReturn = false;

        $this->strResponseRaw = "";
        $this->strStreet = "";
        $this->strPostalCode = "";
        $this->strCity = "";
        $this->strCountryCode = "";
        $this->floatLatitude = 0.0;
        $this->floatLongitude = 0.0;
        $this->intAccuracy = 0;

        if ($this->intApiId == 0) {
            $bitReturn = $this->lookupAddressUsingGoogle($strStreet, $strPostalCode, $strCity, $strCountry);
        }
        elseif ($this->intApiId == 1) {
            $bitReturn = $this->lookupAddressUsingYahoo($strStreet, $strPostalCode, $strCity, $strCountry);
        }

        return $bitReturn;
    }

    /**
     * Does the remote address lookup using the Google Maps API. Returns true if an address was found.
     * For further information please refer to http://code.google.com/apis/maps/documentation/geocoding/index.html
     *
     * @param string $strStreet
     * @param string $strPostalCode
     * @param string $strCity
     * @param string $strCountry
     *
     * @return bool
     */
    private function lookupAddressUsingGoogle($strStreet = "", $strPostalCode = "", $strCity = "", $strCountry = "")
    {
        $bitReturn = false;

        $strHost = "maps.google.de"; //change this to your country/language e.g. maps.google.it
        $strApiKey = ""; //change this; probably not needed anymore
        $strQuery = "/maps/geo?output=xml"."&key=".$strApiKey."&sensor=false&oe=utf8&q=".urlencode($strStreet.", ".$strPostalCode." ".$strCity.", ".$strCountry);

        $objRemoteloader = new Remoteloader();
        $objRemoteloader->setStrHost($strHost);
        $objRemoteloader->setStrQueryParams($strQuery);


        //send another delayed requests if requests were sent too fast
        $intDelay = 0;
        $bitResponsePending = true;
        while ($bitResponsePending) {
            try {
                $bitForceReload = $intDelay > 0 ? true : false;
                $strResponse = $objRemoteloader->getRemoteContent($bitForceReload);
            }
            catch (Exception $objExeption) {
                $bitResponsePending = false;
                $bitReturn = false;
                $strResponse = false;
            }

            if ($strResponse != false) {
                $this->strResponseRaw = $strResponse;

                /** @var SimpleXMLElement $xml */
                $xml = simplexml_load_string($strResponse);
                $status = $xml->Response->Status->code;

                if (strcmp($status, "200") == 0) {
                    //Successful geocode
                    $bitResponsePending = false;

                    //extract response
                    if ($xml->Response->Placemark->AddressDetails->Country->Locality->Thoroughfare) {
                        $this->strStreet = $xml->Response->Placemark->AddressDetails->Country->Locality->Thoroughfare->ThoroughfareName;
                        $this->strPostalCode = "";
                        $this->strCity = $xml->Response->Placemark->AddressDetails->Country->Locality->LocalityName;
                    }
                    elseif ($xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality) {
                        $this->strStreet = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
                        $this->strPostalCode = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality->PostalCode->PostalCodeNumber;
                        $this->strCity = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->Locality->LocalityName;

                    }
                    elseif ($xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality) {
                        $this->strStreet = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName;
                        $this->strPostalCode = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->DependentLocality->PostalCode->PostalCodeNumber;
                        $this->strCity = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;

                    }
                    else {
                        $this->strStreet = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
                        $this->strPostalCode = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->PostalCode->PostalCodeNumber;
                        $this->strCity = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
                    }

                    if ($xml->Response->Placemark->AddressDetails->Country->AdministrativeArea) {
                        $this->strAdministrativeArea = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName;

                        if ($xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea) {
                            $this->strSubAdministrativeArea = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea->SubAdministrativeArea->SubAdministrativeAreaName;
                        }
                    }

                    $this->strCountryCode = $xml->Response->Placemark->AddressDetails->Country->CountryNameCode;

                    $arrCoordinates = explode(",", $xml->Response->Placemark->Point->coordinates);
                    $this->floatLatitude = $arrCoordinates[1];
                    $this->floatLongitude = $arrCoordinates[0];

                    $this->intAccuracy = (int)$xml->Response->Placemark->AddressDetails["Accuracy"];

                    $bitReturn = $this->intAccuracy >= 4 ? true : false;
                }
                elseif (strcmp($status, "620") == 0) {
                    //sent requests too fast
                    $intDelay += 100000;
                }
                else {
                    //failure to geocode
                    $bitResponsePending = false;
                    $bitReturn = false;
                }

            }
            usleep($intDelay);
        }

        return $bitReturn;
    }

    /**
     * Does the remote address lookup using the Yahoo! Maps Geocoding API. Returns true if an address was found.
     * For further information please refer to http://developer.yahoo.com/maps/rest/V1/geocode.html
     *
     * @param string $strStreet
     * @param string $strPostalCode
     * @param string $strCity
     * @param string $strCountry
     *
     * @return bool
     */
    private function lookupAddressUsingYahoo($strStreet = "", $strPostalCode = "", $strCity = "", $strCountry = "")
    {
        $bitReturn = false;

        $strHost = "where.yahooapis.com";
        $strApiKey = "YD-9G7bey8_JXxQP6rxl.fBFGgCdNjoDMACQA--"; //change this
        $strQuery = "/geocode?appid=".$strApiKey."&flags=P&q=".urlencode($strStreet.", ".$strPostalCode." ".$strCity.", ".$strCountry);

        try {
            $objRemoteloader = new Remoteloader();
            $objRemoteloader->setStrHost($strHost);
            $objRemoteloader->setStrQueryParams($strQuery);
            $strResponse = $objRemoteloader->getRemoteContent();
        }
        catch (Exception $objExeption) {
            $bitReturn = false;
            $strResponse = false;
        }

        if ($strResponse != false) {
            $this->strResponseRaw = $strResponse;

            $arrResponse = unserialize($strResponse);
            $arrResponse = $arrResponse["ResultSet"];

            if ($arrResponse["Error"] == 0) {
                $arrResult = $arrResponse["Result"][0];
                //extract response
                $this->strStreet = $arrResult["street"]." ".$arrResult["house"];
                $this->strPostalCode = $arrResult["postal"];
                $this->strCity = $arrResult["city"];
                $this->strCountryCode = $arrResult["country"];

                $this->strAdministrativeArea = $arrResult["statecode"];
                $this->strSubAdministrativeArea = $arrResult["countycode"];

                $this->floatLatitude = $arrResult["latitude"];
                $this->floatLongitude = $arrResult["longitude"];

                //precision="address"
                //TODO: define standard accuracy values and map responses
                $this->intAccuracy = $arrResult["quality"];

                $bitReturn = true;
            }
        }

        return $bitReturn;
    }


    /**
     * Mainly for debugging
     *
     * @return string
     */
    public function getStrResponseRaw()
    {
        return $this->strResponseRaw;
    }

    /**
     * @return string
     */
    public function getStrStreet()
    {
        return $this->strStreet;
    }

    /**
     * @return string
     */
    public function getStrPostalCode()
    {
        return $this->strPostalCode;
    }

    /**
     * @return string
     */
    public function getStrCity()
    {
        return $this->strCity;
    }

    /**
     * @return string
     */
    public function getStrAdministrativeArea()
    {
        return $this->strAdministrativeArea;
    }

    /**
     * @return string
     */
    public function getStrSubAdministrativeArea()
    {
        return $this->strSubAdministrativeArea;
    }

    /**
     * @return string
     */
    public function getStrCountryCode()
    {
        return $this->strCountryCode;
    }

    /**
     * @return float
     */
    public function getFloatLatitude()
    {
        return $this->floatLatitude;
    }

    /**
     * @return float
     */
    public function getFloatLongitude()
    {
        return $this->floatLongitude;
    }

}


