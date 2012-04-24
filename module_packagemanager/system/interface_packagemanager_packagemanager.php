<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/


/**
 * A packagemanager handles a single package or a list of packages installed locally.
 * It provides common methods to query installed packages and triggers updates or installs.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
interface interface_packagemanager_packagemanager {

    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @abstract
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages();

    /**
     * Moves the extracted(!) package from the temp-folder
     * to the target-folder.
     * In most cases, this is either located at /core or at /templates.
     *
     * @abstract
     */
    public function move2Filesystem();

    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     * @abstract
     */
    public function installOrUpdate();

    /**
     * Returns the metadata currently set.
     *
     * @abstract
     * @return class_module_packagemanager_metadata
     */
    public function getObjMetadata();

    /**
     * Validates, whether the current package is installable or not.
     * In nearly all cases
     *
     * @abstract
     * @return bool
     */
    public function isInstallable();

    /**
     * Gets the version of the package currently installed.
     * If not installed, null should be returned instead.
     *
     * @abstract
     * @return string|null
     */
    public function getVersionInstalled();
}