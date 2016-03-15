<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Mediamanager\Portal\Elements;

use Kajona\Mediamanager\Portal\MediamanagerPortal;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Rating\System\RatingRate;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the downloads-element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @targetTable element_downloads.content_id
 */
class ElementDownloadsPortal extends ElementPortal implements PortalElementInterface
{

    /**
     * Contructor
     *
     * @param $objElementData
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
     * Loads the downloads-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";

        $objDownloadsModule = SystemModule::getModuleByName("mediamanager");
        if ($objDownloadsModule != null) {

            $this->arrElementData["repo_id"] = $this->arrElementData["download_id"];
            $this->arrElementData["repo_elementsperpage"] = $this->arrElementData["download_amount"];
            $this->arrElementData["repo_template"] = $this->arrElementData["download_template"];


            $objDownloads = $objDownloadsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objDownloads->action();
        }

        return $strReturn;
    }

    public static function providesNavigationEntries()
    {
        return true;
    }


    public function getNavigationEntries()
    {
        $arrData = $this->getElementContent($this->getSystemid());

        $arrData["repo_id"] = $arrData["download_id"];
        $arrData["repo_elementsperpage"] = $arrData["download_amount"];
        $arrData["repo_template"] = $arrData["download_template"];

        $objDownloadsModule = SystemModule::getModuleByName("mediamanager");

        if ($objDownloadsModule != null) {

            /** @var $objDownloads MediamanagerPortal */
            $objDownloads = $objDownloadsModule->getPortalInstanceOfConcreteModule($arrData);
            $arrReturn = $objDownloads->getNavigationNodes();

            return $arrReturn;
        }

        return false;
    }

}
