<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Templatemapper;

use Kajona\System\Portal\TemplatemapperInterface;
use Kajona\System\System\Link;
use Kajona\System\System\StringUtil;


/**
 * A templatemapper passing the value through urlencode
 *
 * @author sidler@mulchpropd.de
 * @since 5.0
 */
class PagelinkTemplatemapper implements TemplatemapperInterface
{

    /**
     * Converts the passed value to a formatted value.
     * In most scenarios, the value is written directly to the template.
     *
     * @param mixed $strValue
     *
     * @return string
     */
    public function format($strValue)
    {
        if(StringUtil::startsWith($strValue, "http")) {
            return Link::getLinkPortalHref("", $strValue);
        }
        
        return Link::getLinkPortalHref($strValue);
    }

}