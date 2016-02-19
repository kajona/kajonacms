<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\System\Admin\AdminInterface;
use Kajona\System\Portal\PortalInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;
use ReflectionClass;
use ReflectionMethod;

class GeneralActionTest extends Testbase
{


    public function testAdminModules()
    {

        AdminskinHelper::defineSkinWebpath();
        Carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = Resourceloader::getInstance()->getFolderContent("/admin", array(".php"), false, null,
            function (&$strOneFile, $strPath) {
                $strOneFile = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\System\\Admin\\AdminController", null, array(), true);
            });

        foreach ($arrFiles as $objAdminInstance) {
            if ($objAdminInstance !== null) {
                $this->runSingleFile($objAdminInstance);
            }
        }

        Carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }


    public function testPortalModules()
    {


        Carrier::getInstance()->getObjRights()->setBitTestMode(true);

        //load all admin-classes
        $arrFiles = Resourceloader::getInstance()->getFolderContent("/portal", array(".php"), false, function ($strOneFile) {
            if (preg_match("/class_module_(.*)_portal.php/i", $strOneFile)) {
                $strClassname = uniSubstr($strOneFile, 0, -4);
                $objReflection = new ReflectionClass($strClassname);
                if (!$objReflection->isAbstract()) {
                    return true;
                }
            }
            return false;
        },
            function (&$strOneFile, $strPath) {
                $strOneFile = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\System\\Portal\\PortalController", null, array(), true);
            });

        foreach ($arrFiles as $objPortalInstance) {
            if ($objPortalInstance !== null) {
                $this->runSingleFile($objPortalInstance);
            }
        }

        Carrier::getInstance()->getObjRights()->setBitTestMode(false);

    }


    /**
     * @param AdminInterface|PortalInterface $objViewInstance
     */
    private function runSingleFile($objViewInstance)
    {

        $objReflection = new ReflectionClass($objViewInstance);
        $arrMethods = $objReflection->getMethods();

        $objAnnotations = new Reflection(get_class($objViewInstance));

        //collect the autotestable annotations located on class-level
        foreach ($objAnnotations->getAnnotationValuesFromClass("@autoTestable") as $strValue) {
            foreach (explode(",", $strValue) as $strOneMethod) {
                echo "found method " . get_class($objViewInstance) . "@" . $strOneMethod . " marked as class-based @autoTestable, preparing call\n";
                echo "   calling via action() method\n";
                $objViewInstance->action($strOneMethod);
            }
        }


        /** @var ReflectionMethod $objOneMethod */
        foreach ($arrMethods as $objOneMethod) {

            if ($objAnnotations->hasMethodAnnotation($objOneMethod->getName(), "@autoTestable")) {
                echo "found method " . get_class($objViewInstance) . "@" . $objOneMethod->getName() . " marked as @autoTestable, preparing call\n";

                if (uniSubstr($objOneMethod->getName(), 0, 6) == "action" && $objReflection->hasMethod("action")) {
                    echo "   calling via action() method\n";
                    $objViewInstance->action(uniSubstr($objOneMethod->getName(), 6));
                } else {
                    echo "   direct call";
                    $objOneMethod->invoke($objViewInstance);
                }
            }
        }

    }

}
