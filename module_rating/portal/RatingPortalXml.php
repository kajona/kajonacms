<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

namespace Kajona\Rating\Portal;

use Kajona\Rating\System\RatingRate;
use Kajona\System\Portal\PortalController;
use Kajona\System\Portal\XmlPortalInterface;


/**
 * Portal-class of the rating-module
 * Serves xml-requests, e.g. saves a sent rating
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 * @module rating
 * @moduleId _rating_modul_id_
 */
class RatingPortalXml extends PortalController implements XmlPortalInterface
{


    /**
     * Saves a rating to a passed rating-file
     *
     * @return string the new rating for the passed file
     * @permissions view
     */
    protected function actionSaveRating()
    {

        //rating already existing?
        $objRating = RatingRate::getRating($this->getSystemid());
        if ($objRating == null) {
            $objRating = new RatingRate();
            $objRating->setStrRatingSystemid($this->getSystemid());
            $objRating->updateObjectToDb();
        }

        $strReturn = "<rating>";
        $objRating->saveRating($this->getParam("rating"));
        $objRating->updateObjectToDb();
        $strReturn .= round($objRating->getFloatRating(), 2);

        $strReturn .= "</rating>";
        return $strReturn;
    }

}
