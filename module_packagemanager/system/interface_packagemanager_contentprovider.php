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
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @abstract
     * @return string
     */
    public function getDisplayTitle();

    /**
     * Renders the list of available packages or any other kind of gui-representation
     * of the packageprovider.
     *
     * Whenever the provider is capable of uploading new packages, the copy & n upload process
     * should be triggered by the admin-class again.
     * So make sure links or forms point to
     * module = packagemanager
     * action = uploadPackage
     * provider = class_name
     * The provider will be called using the processPackageUpload method.
     *
     * @abstract
     * @return string
     */
    public function renderPackageList();

    /**
     * The real "download" or "upload" should be handled right here.
     * All packages have to be downloaded to /project/temp in order to be processed afterwards.
     *
     * @abstract
     * @return string|null the filename of the package downloaded or null in case of errors
     */
    public function processPackageUpload();
}