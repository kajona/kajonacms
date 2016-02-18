<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

/**
 * Generates a phar out of a passed directory
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
interface PackagemanagerPharGeneratorInterface
{

    /**
     * @param $strSourceDir string the directory to be included in the phar, absolute paths
     * @param $strTargetPath string the full path including the name of the phar to be generated
     */
    public function generatePhar($strSourceDir, $strTargetPath);

    /**
     * Generates a phar and streams is directly to the client
     *
     * @param $strSourceDir string the directory to be included in the phar, absolute paths
     *
     * @return mixed
     */
    public function generateAndStreamPhar($strSourceDir);
}