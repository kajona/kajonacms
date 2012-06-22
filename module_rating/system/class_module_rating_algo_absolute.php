<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_modul_rating_algo_absolute.php 3530 2011-01-06 12:30:26Z sidler $                        *
********************************************************************************************************/

/**
 * Does an absolute, linear rating based on the current rating-value
 * @package modul_rating
 */
class class_module_rating_algo_absolute implements interface_module_rating_algo {
	
	
	/**
     * Calculates the new rating
     * 
     * @param class_module_rating_rate $objSourceRate The rating-record to update
     * @param float $floatNewRating The rating fired by the user
     * @return float the new rating
     */
    public function doRating(class_module_rating_rate $objSourceRate, $floatNewRating) {
    	//calc the new rating
        $floatNewRating = (($objSourceRate->getFloatRating() * $objSourceRate->getIntHits()) + $floatNewRating) / ($objSourceRate->getIntHits()+1);
        
        return $floatNewRating;
    }
	
	
}

