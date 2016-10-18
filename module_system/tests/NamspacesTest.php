<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Tests;

use Kajona\System\System\BootstrapCache;
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

        //rename the packageconfig if present
        if(is_file(_realpath_."project/packageconfig.php")) {
            rename(_realpath_."project/packageconfig.php", _realpath_."project/packageconfig.php.back");
            Classloader::getInstance()->flushCache();
        }

        $arrMergedFiles = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES);

        //iterate classes
        foreach($arrMergedFiles as $strClassName => $strFileName) {
            //do not check legacy classes
            if(StringUtil::startsWith($strClassName, "class_") || StringUtil::startsWith($strClassName, "interface_") || StringUtil::startsWith($strClassName, "trait_")) {
                continue;
            }

            $arrMatches = array();
            //remove char until first \
            // $strClassName =  Kajona\Mediamanager\Admin\Elements\ElementDownloadsAdmin --> Mediamanager\Admin\Elements\ElementDownloadsAdmin

            $strStrippedClassname = StringUtil::substring($strClassName, StringUtil::indexOf($strClassName, "\\")+1);
            $strClassNameTemp = StringUtil::toLowerCase($strStrippedClassname);

            //Get string between C:\\....module_<relevant_string>.php
            // --> C:/Dev/projects/agpV4//core/module_mediamanager/admin/elements/ElementDownloadsAdmin.php --> mediamanager/admin/elements/ElementDownloadsAdmin
            preg_match('/(.*?module_)(.*)(.php)/', $strFileName, $arrMatches);

            $strFileNameTemp = StringUtil::toLowerCase($arrMatches[2]);
            $strFileNameTemp = StringUtil::replace("/", "\\", $strFileNameTemp);

            //now compare e.g. "Mediamanager\Admin\Elements\ElementDownloadsAdmin" with "mediamanager/admin/elements/ElementDownloadsAdmin"
            $this->assertEquals($strClassNameTemp, $strFileNameTemp);



            //test the module name vs the namespace module name
            $strClassNamespaceModule = StringUtil::substring($strStrippedClassname, 0, StringUtil::indexOf($strStrippedClassname, "\\"));
            //special handling of underscores in namespaces: Aaaa_Bbbb, in filesytem aaaa_bbbb
            $arrExp = explode("_", $strClassNamespaceModule);
            $arrNew = array();
            foreach ($arrExp as $str) {
                $arrNew[] = lcfirst($str);
            }
            $strClassNamespaceModule = implode("_", $arrNew);

            $strFilesystemModuleName = StringUtil::substring($strFileName, StringUtil::indexOf($strFileName, "module_")+7);
            $strFilesystemModuleName = StringUtil::substring($strFilesystemModuleName, 0, StringUtil::indexOf($strFilesystemModuleName, "/"));
            $this->assertEquals(lcfirst($strClassNamespaceModule), $strFilesystemModuleName, $strStrippedClassname);
        }


        if(is_file(_realpath_."project/packageconfig.php.back")) {
            rename(_realpath_."project/packageconfig.php.back", _realpath_."project/packageconfig.php");
            Classloader::getInstance()->flushCache();
        }


    }



}
