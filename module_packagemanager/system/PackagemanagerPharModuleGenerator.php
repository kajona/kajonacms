<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use class_http_responsetypes;
use class_response_object;
use FilesystemIterator;
use Phar;

/**
 * Class to generate a phar from a directory
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.8
 */
class PackagemanagerPharModuleGenerator implements PackagemanagerPharGeneratorInterface
{

    /**
     * @param $strSourceDir string the directory to be included in the phar, absolute paths
     * @param $strTargetPath string the full path including the name of the phar to be generated
     */
    public function generatePhar($strSourceDir, $strTargetPath)
    {
        if(\class_config::getInstance()->getPhpIni("phar.readonly") == 1) {
            ini_set("phar.readonly", "0");
        }

        if(\class_config::getInstance()->getPhpIni("phar.readonly") == 1) {
            throw new \class_exception("Phar generation is not possible, the ini-value phar.readonly is set to 1. Please change the php.ini value to 0 in order to generate a valid phar. See http://php.net/manual/en/phar.configuration.php#ini.phar.readonly for more details.", \class_exception::$level_ERROR);
        }



        $objPhar = new Phar(
            $strTargetPath,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
            basename($strTargetPath)
        );
        $objPhar->buildFromDirectory($strSourceDir);
        $objPhar->setStub($objPhar->createDefaultStub());
    }

    /**
     * Generates a phar and streams is directly to the client
     *
     * @param $strSourceDir string the directory to be included in the phar, absolute paths
     *
     * @return mixed
     */
    public function generateAndStreamPhar($strSourceDir)
    {
        //generate phar
        $strTarget = _realpath_."/project/temp/".basename($strSourceDir)."_".generateSystemid().".phar";
        $this->generatePhar($strSourceDir, $strTarget);

        //read and stream
        $strNewName = basename($strSourceDir).".phar";

        class_response_object::getInstance()->addHeader('Pragma: private');
        class_response_object::getInstance()->addHeader('Cache-control: private, must-revalidate');
        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_PHAR);
        class_response_object::getInstance()->addHeader("Content-Disposition: attachment; filename=" . saveUrlEncode($strNewName));

        class_response_object::getInstance()->sendHeaders();


        $objFilesystem = new \class_filesystem();
        $objFilesystem->streamFile($strTarget);
        $objFilesystem->fileDelete($strTarget);
        die();
    }
}
