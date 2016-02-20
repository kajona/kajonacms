<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Portal\Templatemapper;

use Kajona\System\Portal\TemplatemapperInterface;


/**
 * A templatemapper passing the value through urlencode
 *
 * @package module_system
 * @author sidler@mulchpropd.de
 * @since 4.6
 */
class UrlencodeTemplatemapper implements TemplatemapperInterface {

    /**
     * Converts the passed value to a formatted value.
     * In most scenarios, the value is written directly to the template.
     *
     * @param mixed $strValue
     *
     * @return string
     */
    public function format($strValue) {
        return urlencode($strValue);
    }

} 