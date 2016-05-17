<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Sourcecode\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * Loads the sourcecode specified in the element-settings and prepares the output
 *
 * @package element_sourcecode
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementSourcecodePortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData()
    {
        return $this->objTemplate->fillTemplateFile(array("content_id" => $this->arrElementData["content_id"], "code" => nl2br($this->arrElementData["text"])), "/module_sourcecode/".$this->arrElementData["char1"], "sourcecode");
    }

}
