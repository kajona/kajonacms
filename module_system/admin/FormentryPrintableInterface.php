<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin;

/**
 * Extension to the simple formentry-interface,
 * adds a method to fetch a textual representation of the
 * value. May be used for "readonly" fields or generic summaries of
 * a record.
 *
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_formgenerator
 */
interface FormentryPrintableInterface extends FormentryInterface {

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @abstract
     * @return string
     */
    public function getValueAsText();

}
