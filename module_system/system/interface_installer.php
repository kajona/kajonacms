<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Interface for all module installers.
 *
 * @package module_system
 */
interface interface_installer {

    /**
     * Constructor is needed to generate array containing information
     *
     */
    public function __construct();

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
     * Method to switch between the update or the install mode.
     * Called either by the installer or the packagemanager.
     * The default implementation is handled by the base-class.
     *
     * @return string a log about the actions taken.
     */
    public function installOrUpdate();

}
