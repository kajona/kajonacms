<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_downloads_toplist_admin.php 3530 2011-01-06 12:30:26Z sidler $                         *
********************************************************************************************************/

namespace Kajona\Languageredirect\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\ResponseObject;


/**
 *
 * @package element_languageredirect
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementLanguageredirectPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Loads the files, sorts them and generates the output
     *
     * @return string
     */
    public function loadData()
    {

        $objTargetLang = new LanguagesLanguage($this->arrElementData["char1"]);
        if ($this->getStrPortalLanguage() != $objTargetLang->getStrName()) {
            $strTemplateID = $this->objTemplate->readTemplate("/module_languageredirect/".$this->arrElementData["char2"], "languageredirect_wrapper");

            $strTargetHref = getLinkPortalHref($this->getPagename(), "", "", "", "", $objTargetLang->getStrName());
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_REDIRECT);
            $this->portalReload($strTargetHref);
            return $this->fillTemplate(array("redirect_href" => $strTargetHref), $strTemplateID);
        }


        return "";
    }


}
