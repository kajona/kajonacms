<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

namespace Kajona\Mediamanager\Portal\Elements;

use Kajona\Mediamanager\Portal\MediamanagerPortal;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Rating\System\RatingRate;
use Kajona\System\System\SystemModule;


/**
 * Portal-part of the gallery-element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @targetTable element_gallery.content_id
 */
class ElementGalleryPortal extends ElementPortal implements PortalElementInterface
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
     * Loads the gallery-class and passes control
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";

        $objMediamanagerModule = SystemModule::getModuleByName("mediamanager");
        if ($objMediamanagerModule != null) {

            $this->arrElementData["repo_id"] = $this->arrElementData["gallery_id"];
            $this->arrElementData["repo_elementsperpage"] = $this->arrElementData["gallery_imagesperpage"];
            $this->arrElementData["repo_template"] = $this->arrElementData["gallery_template"];

            $objGallery = $objMediamanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objGallery->action();
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

        //skip random galleries
        if ($arrData["gallery_mode"] == "1") {
            return false;
        }

        $arrData["repo_id"] = $arrData["gallery_id"];
        $arrData["repo_elementsperpage"] = $arrData["gallery_imagesperpage"];
        $arrData["repo_template"] = $arrData["gallery_template"];

        $objMediamanagerModule = SystemModule::getModuleByName("mediamanager");

        if ($objMediamanagerModule != null) {

            /** @var $objDownloads MediamanagerPortal */
            $objDownloads = $objMediamanagerModule->getPortalInstanceOfConcreteModule($arrData);
            $arrReturn = $objDownloads->getNavigationNodes();

            return $arrReturn;
        }

        return false;
    }

}
