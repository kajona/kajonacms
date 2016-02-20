<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                        *
********************************************************************************************************/

namespace Kajona\Rating\System;


/**
 * Does an assessed rating based on the current rating-value using the concept of the normal distribution by Gauss
 *
 * @package module_rating
 */
class RatingAlgoGaussian implements ModuleRatingAlgoInterface
{

    /**
     * A factor used to assess ratings depending on the number of already existing ratings.
     * Within this implementation every rating has a certain probability which depends on 2 criteria:
     * 1. the max value an object can be rated (the number of "stars" available for rating)
     * 2. the amount of already existing ratings by users
     * Generally ratings of 1 or 5 stars (with 5 being the max value) are less likely than a rating of 3 stars (being the average rating value).
     * Therefore the former will be changed to values being closer to the average rating value (1 becoming 1.5 and 5 will be changed to 4.5 points).
     * With more ballots being casted on an object those ratings will be assessed less.
     * This so called "assessment softener factor" depends on the number of ratings one usually expects on the website.
     * With less than 100 votes a factor of 10 is suggested.
     * With less than 1000 votes a factor of 100 is recommended and so on...
     *
     * @var int
     */
    public static $intAssessmentSoftenerFactor = 10;

    /**
     * Calculates the new rating
     *
     * @param RatingRate $objSourceRate The rating-record to update
     * @param float $floatNewRating The rating fired by the user
     *
     * @return float the new rating
     */
    public function doRating(RatingRate $objSourceRate, $floatNewRating)
    {

        //calc the rating's midpoint depending on the maximum rating value
        $floatRatingMidpoint = (RatingRate::$intMaxRatingValue + 1) / 2;

        $floatAssessedRating = 0;
        if ($floatNewRating == $floatRatingMidpoint) {
            //the rating exactly matches the average rating value
            //it doesn't need to be assessed
            $floatAssessedRating = $floatNewRating;
        }
        else {
            //determine the interval between assessed ratings
            //the number 0.5 is to the maximum change which is applied to a rating during assessment
            //a more probable rating and a higher number of ratings will make this interval less meaningful
            $floatAssessmentInterval = 0.5 / ($floatRatingMidpoint - 1);
            //calc the assessment on the current rating
            $floatAssessedRating = ($floatRatingMidpoint - $floatNewRating) * $floatAssessmentInterval + $floatNewRating;

            //add or subtract a bonus depending on the number of already existing ratings
            $intAdditionSign = 1;
            if ($floatNewRating < $floatRatingMidpoint) {
                $intAdditionSign = -1;
            }
            $floatAssessedRating = $floatAssessedRating + $intAdditionSign * ($objSourceRate->getIntHits() / RatingAlgoGaussian::$intAssessmentSoftenerFactor * 2);

            //reset the final assessed ratings if they should exceed the user's rating
            //this could only happen with a high number of ratings on an object
            if (($floatNewRating < $floatRatingMidpoint && $floatAssessedRating < $floatNewRating)
                || ($floatNewRating > $floatRatingMidpoint && $floatAssessedRating > $floatNewRating)
            ) {
                $floatAssessedRating = $floatNewRating;
            }
        }
        //calc the new rating
        $floatNewRating = (($objSourceRate->getFloatRating() * $objSourceRate->getIntHits()) + $floatAssessedRating) / ($objSourceRate->getIntHits() + 1);

        return $floatNewRating;
    }

}

