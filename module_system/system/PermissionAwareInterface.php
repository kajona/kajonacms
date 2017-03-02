<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Interface which indicates that a model is able to set the permissions according to the current state of the object
 *
 * @package module_system
 * @author christoph.kappestein@gmail.com
 * @since 6.2
 */
interface PermissionAwareInterface
{
    /**
     * Calculates the permissions according to the current state of the object
     *
     * @return void
     */
    public function calcPermissions();
}
