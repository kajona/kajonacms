<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/


/**
 * A contentprovider is a single source, e.g. a fileupload or a remote-server providing
 * packages.
 * The provider is responsible of transferring the package to the local system and to check for updates.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_packagemanager
 */
interface interface_packagemanager_contentprovider {

    /**
     * A simple way to query the remote-packages.
     * Should provide a list of packages including their versions.
     *
     * @abstract
     * @return class_module_packagemanager_metadata
     */
    public function listAvailablePackages();

    /**
     * Triggers the download of a remote-package.
     * If the content-provider makes use of another transfer-technology, this should be handled right here.
     *
     * @abstract
     * @param class_module_packagemanager_metadata $objMetadata
     */
    public function downloadPackage(class_module_packagemanager_metadata $objMetadata);

    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @abstract
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages();
}