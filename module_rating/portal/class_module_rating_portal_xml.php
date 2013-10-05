<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

/**
 * Portal-class of the rating-module
 * Serves xml-requests, e.g. saves a sent rating
 *
 * @package module_rating
 * @author sidler@mulchprod.de
 * @module rating
 * @moduleId _rating_modul_id_
 */
class class_module_rating_portal_xml extends class_portal implements interface_xml_portal {


    /**
     * Saves a rating to a passed rating-file
     *
     * @return string the new rating for the passed file
     * @permissions view
     */
    protected function actionSaveRating() {

        //rating already existing?
        $objRating = class_module_rating_rate::getRating($this->getSystemid());
        if($objRating == null) {
            $objRating = new class_module_rating_rate();
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
