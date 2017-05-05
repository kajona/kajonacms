<?php
/*"******************************************************************************************************
*   (c) 2015-2017 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Tests;

use Kajona\System\System\AbstractController;
use Kajona\System\System\Classloader;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\StringUtil;

/**
 * @author sidler@mulchprod.de
 * @since 6.2
 */
class PermissionsAnnotationTest extends \PHPUnit_Framework_TestCase
{
    public function permissionDataProvider()
    {
        $arrReturn = [];

        //load all admin-classes
        $arrFilesBackend = Resourceloader::getInstance()->getFolderContent("/admin", [".php"], true, null,
            function (&$strOneFile, $strPath) {
                $strOneFile = Classloader::getInstance()->getInstanceFromFilename($strPath, AbstractController::class, null, [], true);
            }
        );


        $arrFilesFrontend = Resourceloader::getInstance()->getFolderContent("/portal", [".php"], true, null,
            function (&$strOneFile, $strPath) {
                if (in_array($strOneFile, ["global_includes.php"])) {
                    $strOneFile = null;
                } else {
                    $strOneFile = Classloader::getInstance()->getInstanceFromFilename($strPath, AbstractController::class, null, [], true);
                }
            }
        );

        foreach (array_merge($arrFilesBackend, $arrFilesFrontend) as $objController) {
            if ($objController == null) {
                continue;
            }

            $objReflection = new \ReflectionClass($objController);


            foreach ($objReflection->getMethods() as $objOneMethod) {
                if (StringUtil::startsWith($objOneMethod->getName(), "action") && $objOneMethod->getName() !== "action") {
                    $arrReturn[] = [$objReflection->getName(), $objOneMethod->getName()];
                }
            }

        }

        return $arrReturn;
    }

    /**
     * @dataProvider permissionDataProvider
     *
     * @param $strClass
     * @param $strMethod
     */
    public function testPermissionsAnnotation($strClass, $strMethod)
    {
        $this->markTestSkipped("Pending until Kajona 7.0");
        $objKajonaReflection = new Reflection($strClass);
        $strPermissions = $objKajonaReflection->getMethodAnnotationValue($strMethod, AbstractController::STR_PERMISSION_ANNOTATION);
        $this->assertNotEmpty($strPermissions, $strClass."::".$strMethod);
    }


}
