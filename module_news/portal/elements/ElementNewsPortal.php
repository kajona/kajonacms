<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						               	*
********************************************************************************************************/

namespace Kajona\News\Portal\Elements;

use Kajona\News\System\NewsNews;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Rating\System\RatingRate;
use Kajona\System\System\SystemModule;

/**
 * Portal-part of the news-element
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @targetTable element_news.content_id
 */
class ElementNewsPortal extends ElementPortal implements PortalElementInterface
{
    /**
     * Redefined in order to register cache busters
     *
     * @param PagesPageelement $objElementData
     */
    public function __construct($objElementData)
    {
        parent::__construct($objElementData);

        //we support ratings, so add cache-busters
        if (SystemModule::getModuleByName("rating") !== null) {
            $this->setStrCacheAddon(getCookie(RatingRate::RATING_COOKIE));
        }
    }


    /**
     * Loads the news-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";
        //Load the data
        $objNewsModule = SystemModule::getModuleByName("news");
        if ($objNewsModule != null) {
            $objNews = $objNewsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objNews->action();
        }
        return $strReturn;
    }


    /**
     * Overwrite this method if you'd like to perform special actions if as soon as content
     * was loaded from the cache.
     * Make sure to return a proper boolean value, otherwise the cached entry may get invalid.
     *
     * @return boolean
     */
    public function onLoadFromCache()
    {
        //update the news shown, if in details mode
        if ($this->getParam("action") == "newsDetail") {
            $objNews = new NewsNews($this->getParam("systemid"));
            $objNews->increaseHits();
        }

        return true;
    }

}
