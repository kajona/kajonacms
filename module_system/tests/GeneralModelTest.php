<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "/../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;
use ReflectionClass;

class GeneralModelTest extends Testbase
{


    public function testModuleModels()
    {

        echo "preparing object saves...\n";

        Carrier::getInstance()->getObjRights()->setBitTestMode(true);

        $arrFiles = Resourceloader::getInstance()->getFolderContent("/system", array(".php"), false, null,
            function (&$strOneFile, $strFilename) {

                $objInstance = Classloader::getInstance()->getInstanceFromFilename($strFilename, "Kajona\\System\\System\\Model");

                if ($objInstance == null) {
                    return;
                }

                $objClass = new ReflectionClass($objInstance);

                $objAnnotations = new Reflection($objInstance);

                //block from autotesting?
                if ($objAnnotations->hasClassAnnotation("@blockFromAutosave")) {
                    echo "skipping class " . uniSubstr($strOneFile, 0, -4) . " due to @blockFromAutosave annotation" . "\n";
                    return;
                }

                $strOneFile = $objClass->newInstance();

            });

        $arrSystemids = array();

        /** @var $objOneInstance Model */
        foreach ($arrFiles as $objOneInstance) {

            if (!is_object($objOneInstance)) {
                continue;
            }

            echo "testing object of type " . get_class($objOneInstance) . "@" . $objOneInstance->getSystemid() . "\n";
            $this->assertTrue($objOneInstance->updateObjectToDb(), "saving object " . get_class($objOneInstance));
            $arrSystemids[$objOneInstance->getSystemid()] = get_class($objOneInstance);
            echo " ...saved object of type " . get_class($objOneInstance) . "@" . $objOneInstance->getSystemid() . "\n";
        }

        $objObjectfactory = Objectfactory::getInstance();
        foreach ($arrSystemids as $strSystemid => $strClass) {

            echo "instantiating " . $strSystemid . "@" . $strClass . "\n";

            $objInstance = $objObjectfactory->getObject($strSystemid);

            $this->assertTrue($objInstance != null);

            $this->assertEquals(get_class($objInstance), $strClass);


            echo "deleting " . $strSystemid . "@" . $strClass . "\n";
            $objInstance->deleteObjectFromDatabase();
        }


        Carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }

}

