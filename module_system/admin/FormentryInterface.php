<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin;

/**
 * Interface for all form-objects.
 * Make sure you extend FormentryBase, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
interface FormentryInterface
{

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @abstract
     * @return string
     */
    public function renderField();

}
