<?php
/*"******************************************************************************************************
*   (c) 2014-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * List of possible data-types usable when generating new tables / updating tables.
 *
 * @todo move to an enum based approach
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class DbDatatypes
{

    const STR_TYPE_INT = "int";
    const STR_TYPE_LONG = "long";
    const STR_TYPE_DOUBLE = "double";
    const STR_TYPE_CHAR10 = "char10";
    const STR_TYPE_CHAR20 = "char20";
    const STR_TYPE_CHAR100 = "char100";
    const STR_TYPE_CHAR254 = "char254";
    const STR_TYPE_CHAR500 = "char500";
    const STR_TYPE_TEXT = "text";
    const STR_TYPE_LONGTEXT = "longtext";
}
