<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Config-file for the package-manager. Contains the list of manager available
 *
 * There's no need to change anything in this file.
 * All values and settings may be overridden by placing them in the projects' config-file at
 *
 *   /project/module_packagemanager/system/config/packagemanager.php
 *
 * @package module_packagemanager
 */

$config = array();

//comma-separated list of registered content-providers
$config["contentproviders"] = "Kajona\\Packagemanager\\System\\PackagemanagerContentproviderKajona,";
$config["contentproviders"] .= "Kajona\\Packagemanager\\System\\PackagemanagerContentproviderKajonabase,";
$config["contentproviders"] .= "Kajona\\Packagemanager\\System\\PackagemanagerContentproviderLocal";