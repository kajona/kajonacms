<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

namespace Kajona\Faqs\Portal\Elements;

use class_module_rating_rate;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the faqs-element
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @targetTable element_faqs.content_id
 */
class ElementFaqsPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Contructor
     *
     * @param mixed $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct($objElementData);

        //we support ratings, so add cache-busters
        if (SystemModule::getModuleByName("rating") != null) {
            $this->setStrCacheAddon(getCookie(class_module_rating_rate::RATING_COOKIE));
        }
    }


    /**
     * Loads the faqs-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";
        //Load the data
        $objFaqsModule = SystemModule::getModuleByName("faqs");
        if ($objFaqsModule != null) {
            $objFaqs = $objFaqsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objFaqs->action();
        }
        return $strReturn;
    }

}
