<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives from the official kajona-repo.
 * Provides both, a search and a download-part.
 *
 * @module module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_kajona extends class_module_packagemanager_contentprovider_remote_base {

    function __construct() {
        //TODO: replace with kajona url
        $strBrowseHost = "";
        $strBrowseUrl   = "";
        $strDownloadUrl = "";

        if(isset($_SERVER["HTTP_HOST"])) {
            $strBrowseHost  = $_SERVER["HTTP_HOST"];
            $strBrowseUrl   = str_replace("http://".$_SERVER["HTTP_HOST"], "", _webpath_)."/xml.php?module=packageserver&action=list";
            $strDownloadUrl = str_replace("http://".$_SERVER["HTTP_HOST"], "", _webpath_)."/download.php";
        }

        parent::__construct("provider_kajona", $strBrowseHost, $strBrowseUrl, $strDownloadUrl, __CLASS__);
    }
}