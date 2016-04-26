<?php
namespace Kajona\Maps\Test;

// includes
use Kajona\Maps\System\Geocoder;

class GeocoderTest extends \Kajona\System\Tests\Testbase {

    public function test() {

        //disabled due to runtime problems, remote lookups to google / yahoo at each build are not that clever
        return "";

        //some test addresses
        $arrAddresses = array();
        $arrAddresses[] = array("Lankwitzer Strasse 19", "12107", "Berlin", "Deutschland");
        $arrAddresses[] = array("Lankwitzer Strasse 19", "", "Berlin", "");
        $arrAddresses[] = array("Zettachring 10a", "70567", "Stuttgart", "Deutschland");
        $arrAddresses[] = array("Helstorfer Str. 7", "30625", "Hannover", "DE");
        $arrAddresses[] = array("476 St Kilda Road", "3004", "Melbourne", "Australia");
        $arrAddresses[] = array("Wienerbergstrasse 3", "1101", "Wien", "");
        $arrAddresses[] = array("1325 Huaihai Zhong Road", "200031", "Shanghai", "China");
        $arrAddresses[] = array("2, rue de la Faisanderie", "67831", "LINGOLSHEIM", "France");
        $arrAddresses[] = array("Via Romagnosi 4", "00196", "Roma", "Italy");
        $arrAddresses[] = array("Saturnus 1", "3824", "Amersfoort", "Netherlands");
        $arrAddresses[] = array("113 Leninsky Prospekt", "117198", "Moscow", "Russia");
        $arrAddresses[] = array("45 King William Street", "EC4R 9AN", "London", "UK");
        $arrAddresses[] = array("4150 Network Circle", "CA 95054", "Santa Clara", "USA");

        echo "\ttry to geocode some addresses using google...\n\n";
        $this->processResult(0, $arrAddresses);
        echo "\ttry to geocode some addresses using yahoo...\n\n";
        $this->processResult(1, $arrAddresses);

    }


    private function processResult($intGeocoder, $arrAddresses) {
        $objGeocoder = new Geocoder($intGeocoder); //0 = Google Maps; 1 = Yahoo! Maps

        $intGeocodedAddresses = 0;
        foreach($arrAddresses as $intI => $oneAddress) {
            $address = $oneAddress[0].", ".$oneAddress[1]." ".$oneAddress[2].", ".$oneAddress[3];

            $bitLookup = $objGeocoder->lookupAddress($oneAddress[0], $oneAddress[1], $oneAddress[2], $oneAddress[3]);
            echo $intI.": ".$address;
            echo " <a href=\"#\" onclick=\"document.getElementById('response".$intGeocoder.$intI."').style.display='block'; return false;\">view response</a><div id=\"response".$intGeocoder.$intI."\" style=\"display: none; border: 1px solid black;\">".htmlentities($objGeocoder->getStrResponseRaw(), ENT_COMPAT, "UTF-8")."</div>\n";

            if($bitLookup) {
                $intGeocodedAddresses++;
                echo "\tlat: ".$objGeocoder->getFloatLatitude()." lng: ".$objGeocoder->getFloatLongitude()."\n";
                echo "\tStreet: ".$objGeocoder->getStrStreet()."\n";
                echo "\tPostal code: ".$objGeocoder->getStrPostalCode()."\n";
                echo "\tCity: ".$objGeocoder->getStrCity()."\n";
                echo "\tAdministrativeArea: ".$objGeocoder->getStrAdministrativeArea()."\n";
                echo "\tSubAdministrativeArea: ".$objGeocoder->getStrSubAdministrativeArea()."\n";
                echo "\tCountry: ".$objGeocoder->getStrCountryCode();
            }
            else {
                echo "\t<b>address not found / error</b>";
            }

            echo "\n";
        }

        echo "\n\n";
        $this->assertEquals($intGeocodedAddresses, count($arrAddresses), __FILE__." checkGeocodedAddresses");
    }
}


