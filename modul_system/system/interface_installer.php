<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Interface for all installers
 *
 * @package modul_system
 */
interface interface_installer {

    /**
     * Constructor is needed to generate array containing infos
     *
     */
    public function __construct();

    /**
     * Returns an array of modules needed to install the current module
     * This way can be used to check the existance of other modules
     *
     */
    public function getNeededModules();
    
    /**
     * Returns the version of the system-module needed as a minimum
     * Return an empty string, if no min version is needed
     *
     */
    public function getMinSystemVersion();

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install();

    /**
     * Does the hard work: updates the module to the current version
     *
     */
    public function update();

    /**
     * Returns a boolean value
     * Indicates, if the module has post-install options or not
     *
     */
    public function hasPostInstalls();

    /**
     * Does the post-install tasks as creating page-elements
     *
     */
    public function postInstall();

}
?>