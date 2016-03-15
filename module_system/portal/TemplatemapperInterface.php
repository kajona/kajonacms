<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\System\Portal;

/**
 * Interface to convert a single value to a readable value, printable to a template.
 * Use the annotation @templateMapper in combination with @templateExport to define a mapper
 * to be used,
 *
 * @package module_system
 * @since 4.5
 */
interface TemplatemapperInterface {

    /**
     * Converts the passed value to a formatted value.
     * In most scenarios, the value is written directly to the template.
     *
     * @param mixed $strValue
     *
     * @return string
     */
    public function format($strValue);


}
