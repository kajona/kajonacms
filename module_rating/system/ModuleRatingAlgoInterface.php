<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/

namespace Kajona\Rating\System;


/**
 * Interface to be implemented by all rating-algorithms designed to calculate ratings
 *
 * @package module_rating
 */
interface ModuleRatingAlgoInterface
{

    /**
     * Calculates the new rating
     *
     * @param RatingRate $objSourceRate The rating-record to update
     * @param float $floatNewRating The rating fired by the user
     *
     * @return float the new rating
     */
    public function doRating(RatingRate $objSourceRate, $floatNewRating);

}
