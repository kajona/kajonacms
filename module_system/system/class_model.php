<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                               *
********************************************************************************************************/

/**
 * Top-level class for all model-classes.
 * Please be aware that all logic located in this class will be moved to class_root. This means that this
 * class will become useless. It will remain for API-compatibility but without any logic.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @deprectated this class will be removed from future releases, all logic will be moved to class root.
 *
 *
 */
abstract class class_model extends class_root {


    // --- RATING -------------------------------------------------------------------------------------------
    /**
     * Rating of the current file, if module rating is installed.
     *
     * @param bool $bitRound Rounds the rating or disables rounding
     * @see interface_sortable_rating
     * @return float
     *
     * @todo: with php5.4, ths could be moved to traits
     */
    public function getFloatRating($bitRound = true) {
        $floatRating = null;
        $objModule = class_module_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_module_rating_rate::getRating($this->getSystemid());
            if($objRating != null) {
                $floatRating = $objRating->getFloatRating();
                if($bitRound) {
                    $floatRating = round($floatRating, 2);
                }
            }
            else
                $floatRating = 0.0;
        }

        return $floatRating;
    }

    /**
     * Checks if the current user is allowed to rate the file
     *
     * @return bool
     *
     * @todo: with php5.4, ths could be moved to traits
     */
    public function isRateableByUser() {
        $bitReturn = false;
        $objModule = class_module_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_module_rating_rate::getRating($this->getSystemid());
            if($objRating != null)
               $bitReturn = $objRating->isRateableByCurrentUser();
            else
               $bitReturn = true;
        }

        return $bitReturn;
    }

    /**
     * Number of rating for the current file
     *
     * @see interface_sortable_rating
     * @return int
     *
     * @todo: with php5.4, ths could be moved to traits
     */
    public function getIntRatingHits() {
        $intHits = 0;
        $objModule = class_module_system_module::getModuleByName("rating");
        if($objModule != null) {
            $objRating = class_module_rating_rate::getRating($this->getSystemid());
            if($objRating != null)
               $intHits = $objRating->getIntHits();
            else
               return 0;
        }

        return $intHits;
    }

}
