<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives from the official kajona-repo.
 * Provides both, a search and a download-part.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_localhost extends class_module_packagemanager_contentprovider_remote_base {

    function __construct() {
        parent::__construct(
            "provider_localhost",
            $_SERVER["HTTP_HOST"],
            dirname($_SERVER["REQUEST_URI"])."/xml.php?module=packageserver&action=list",
            dirname($_SERVER["REQUEST_URI"])."/download.php",
            __CLASS__,
            "http://",
            80
        );
    }
}