<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * This class provides a wrapper to gzip functionalities.
 * It can be used to create compressed versions of files
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Gzip {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Tries to compress a given file.
     * If the passed filename was test.txt, the created file is named
     * test.txt.gz
     *
     * @param string $strFilename
     * @param bool $bitDeleteSource
     *
     * @throws class_exception
     * @return bool
     */
    public function compressFile($strFilename, $bitDeleteSource = false) {
        if(strpos($strFilename, _realpath_) === false)
            $strFilename = _realpath_.$strFilename;
        //Check if sourcefile exists
        $strTargetFilename = $strFilename.".gz";
        if(file_exists($strFilename) && is_file($strFilename)) {
            //try to open target file pointer
            if($objTargetPointer = gzopen($strTargetFilename, "wb")) {
                //try to open sourcefile
                if($objSourcePointer = fopen($strFilename, "rb")) {
                    //Loop over filecontent
                    while(!feof($objSourcePointer)) {
                        gzwrite($objTargetPointer, fread($objSourcePointer, 1024 * 512));
                    }
                    @fclose($objSourcePointer);
                    @gzclose($objTargetPointer);
                    //Delete the sourcefile?
                    if($bitDeleteSource) {
                        $objFilesystem = new class_filesystem();
                        $objFilesystem->fileDelete(str_replace(_realpath_, "", $strFilename));
                    }
                    return true;
                }
                else {
                    @gzclose($objTargetPointer);
                    throw new class_exception("can't open sourcefile", class_exception::$level_ERROR);
                }
            }
            else
                throw new class_exception("can't open targetfile", class_exception::$level_ERROR);
        }
        else
            throw new class_exception("Sourcefile not valid", class_exception::$level_ERROR);

        return false;
    }

    /**
     * Tries to decompress a given file.
     * If the passed filename was test.txt.gz, the created file is named
     * test.txt
     *
     * @param string $strFilename
     *
     * @throws class_exception
     * @return bool
     */
    public function decompressFile($strFilename) {
        if(substr($strFilename, -3) != ".gz")
            throw new class_exception("sourcefile ".$strFilename." no valid .gz file", class_exception::$level_ERROR);

        if(strpos($strFilename, _realpath_) === false)
            $strFilename = _realpath_.$strFilename;

        //Check if sourcefile exists
        $strTargetFilename = substr($strFilename, 0, strlen($strFilename) - 3);

        if(file_exists($strFilename) && is_file($strFilename)) {
            //try to open sourcefile
            if($objSourcePointer = gzopen($strFilename, "rb")) {

                if($objTargetPointer = fopen($strTargetFilename, "wb")) {
                    //Loop over filecontent
                    while(!gzeof($objSourcePointer)) {
                        fwrite($objTargetPointer, gzread($objSourcePointer, 1024 * 512));
                        //$strContent .= gzread($objSourcePointer,  1024 * 512);
                    }
                    @gzclose($objSourcePointer);
                    @fclose($objTargetPointer);

                    return true;

                }
                else {
                    @gzclose($objSourcePointer);
                    throw new class_exception("can't write to targetfile ", class_exception::$level_ERROR);
                }

            }
            else {
                gzclose($objSourcePointer);
                throw new class_exception("can't open sourcefile", class_exception::$level_ERROR);
            }

        }
        else
            throw new class_exception("Sourcefile not valid", class_exception::$level_ERROR);

        return false;
    }



}

