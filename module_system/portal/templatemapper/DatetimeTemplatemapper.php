<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Portal\Templatemapper;


/**
 * A templatemapper rendering a formatted date with its time
 *
 * @package module_system
 * @author sidler@mulchpropd.de
 * @since 4.5
 */
class DatetimeTemplatemapper implements interface_templatemapper {

    /**
     * Converts the passed value to a formatted value.
     * In most scenarios, the value is written directly to the template.
     *
     * @param mixed $strValue
     *
     * @return string
     */
    public function format($strValue) {
        return dateToString(new class_date($strValue));
    }

} 