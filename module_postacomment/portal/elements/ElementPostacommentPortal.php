<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						        *
********************************************************************************************************/

namespace Kajona\Postacomment\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Rating\System\RatingRate;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the postacomment-element
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementPostacommentPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Constructor
     *
     * @param $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct($objElementData);

        //we support ratings, so add cache-busters
        $objRatingModule = SystemModule::getModuleByName("rating");
        if ($objRatingModule != null) {
            $this->setStrCacheAddon(getCookie(RatingRate::RATING_COOKIE));
        }
    }


    /**
     * Loads the postacomment-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";
        //Load the data
        $objPostacommentModule = SystemModule::getModuleByName("postacomment");
        if ($objPostacommentModule != null) {

            //action-filter set within the element?
            if (trim($this->arrElementData["char2"]) != "") {
                if ($this->getParam("action") != $this->arrElementData["char2"]) {
                    return "";
                }
            }

            $objPostacomment = $objPostacommentModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objPostacomment->action();
        }
        return $strReturn;
    }

}
