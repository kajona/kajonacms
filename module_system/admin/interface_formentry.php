<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                               *
********************************************************************************************************/

/**
 * Interface for all form-objects.
 * Make sure you extend class_formentry_base, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
interface interface_formentry {

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @abstract
     * @return string
     */
    public function renderField();

}
