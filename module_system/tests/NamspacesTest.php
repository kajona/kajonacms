<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Tests;

use Kajona\System\System\Classloader;
use Kajona\System\System\StringUtil;

/**
 * @author stefan.meyer1@yahoo.com
 * @since 6.0
 */
class NamspacesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Check if folder structure is equals to namespace e.g.
     *  e.g. NameSpace = Kajona\Mediamanager\Admin\Elements\ElementDownloadsAdmin
     *       PathToFile = C:/Dev/projects/agpV4//core/module_mediamanager/admin/elements/ElementDownloadsAdmin.php
     *
     * The test checks if
     *  "Mediamanager\Admin\Elements\ElementDownloadsAdmin" is equals to "mediamanager/admin/elements/ElementDownloadsAdmin" (incase sensitive check)
     *
     */
    public function testNamespaceAndFilePath()
    {
        //get all files and check if namespace is equals to file path
        $objReflection = new \ReflectionClass(Classloader::getInstance());
        $objReflectionPropertyCodeFolders = $objReflection->getProperty("arrCodeFolders");
        $objReflectionPropertyCodeFolders->setAccessible(true);

        $objReflectionMethodClassesInFolder = $objReflection->getMethod("getClassesInFolder");
        $objReflectionMethodClassesInFolder->setAccessible(true);

        //get all relevant classes
        $arrMergedFiles = array();
        foreach ($objReflectionPropertyCodeFolders->getValue() as $strFolder) {
            $arrMergedFiles = array_merge($arrMergedFiles, $objReflectionMethodClassesInFolder->invoke(Classloader::getInstance(), $strFolder));
        }


        //iterate classes
        foreach($arrMergedFiles as $strClassName => $strFileName) {
            //do not check legacy classes
            if(StringUtil::startsWith($strClassName, "class_") || StringUtil::startsWith($strClassName, "interface_") || StringUtil::startsWith($strClassName, "trait_")) {
                continue;
            }

            $arrMatches = array();
            //remove char until first \
            // $strClassName =  Kajona\Mediamanager\Admin\Elements\ElementDownloadsAdmin --> Mediamanager\Admin\Elements\ElementDownloadsAdmin
            preg_match('/(.*?\\\\)(.*)/', $strClassName, $arrMatches);
            $strClassNameTemp = StringUtil::toLowerCase($arrMatches[2]);

            //Get string between C:\\....module_<relevant_string>.php
            // --> C:/Dev/projects/agpV4//core/module_mediamanager/admin/elements/ElementDownloadsAdmin.php --> mediamanager/admin/elements/ElementDownloadsAdmin
            preg_match('/(.*?module_)(.*)(.php)/', $strFileName, $arrMatches);
            $strFileNameTemp = StringUtil::toLowerCase($arrMatches[2]);
            $strFileNameTemp = StringUtil::replace("/", "\\", $strFileNameTemp);

            //now compare e.g. "Mediamanager\Admin\Elements\ElementDownloadsAdmin" with "mediamanager/admin/elements/ElementDownloadsAdmin"
            $this->assertEquals($strClassNameTemp, $strFileNameTemp);
        }




    }
}
