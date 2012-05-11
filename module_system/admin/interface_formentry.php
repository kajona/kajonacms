<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Interface for all form-objects.
 * Make sure you extend class_formentry_base, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
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
