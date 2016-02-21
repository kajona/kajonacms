<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Config-file for the package-manager. Contains the list of manager available
 *
 * There's no need to change anything in this file.
 * All values and settings may be overridden by placing them in the projects' config-file at
 *
 *   /project/system/config/packagemanager.php
 *
 * @package module_packagemanager
 */

$config = array();

//comma-separated list of registered content-providers
$config["contentproviders"] = "PackagemanagerContentproviderKajona,";
$config["contentproviders"] .= "PackagemanagerContentproviderKajonabase,";
$config["contentproviders"] .= "PackagemanagerContentproviderLocal";