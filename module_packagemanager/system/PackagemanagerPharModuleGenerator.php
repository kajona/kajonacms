<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use FilesystemIterator;
use Kajona\System\System\Config;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\ResponseObject;
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
     *
     * @throws Exception
     */
    public function generatePhar($strSourceDir, $strTargetPath)
    {
        if(Config::getInstance()->getPhpIni("phar.readonly") == 1) {
            ini_set("phar.readonly", "0");
        }

        if(Config::getInstance()->getPhpIni("phar.readonly") == 1) {
            throw new Exception("Phar generation is not possible, the ini-value phar.readonly is set to 1. Please change the php.ini value to 0 in order to generate a valid phar. See http://php.net/manual/en/phar.configuration.php#ini.phar.readonly for more details.", Exception::$level_ERROR);
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

        ResponseObject::getInstance()->addHeader('Pragma: private');
        ResponseObject::getInstance()->addHeader('Cache-control: private, must-revalidate');
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_PHAR);
        ResponseObject::getInstance()->addHeader("Content-Disposition: attachment; filename=" . saveUrlEncode($strNewName));

        ResponseObject::getInstance()->sendHeaders();


        $objFilesystem = new Filesystem();
        $objFilesystem->streamFile($strTarget);
        $objFilesystem->fileDelete($strTarget);
        die();
    }
}
