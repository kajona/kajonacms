<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Facebooklikebox\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;


/**
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class ElementFacebooklikeboxPortal extends ElementPortal implements PortalElementInterface {


    /**
     * Renders the template
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strLanguage = $this->getStrPortalLanguage();
        //load the template
        $strTemplateID = $this->objTemplate->readTemplate("/module_facebooklikebox/".$this->arrElementData["char1"], "facebooklikebox");
        $strReturn = $this->fillTemplate(array("portallanguage" => $strLanguage), $strTemplateID);
        return "adfsdfs".$strReturn;
    }

}
